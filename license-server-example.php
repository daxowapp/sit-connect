<?php
/**
 * SIT Connect - Sample License Server Implementation
 * 
 * This is a simple example of a license server.
 * Deploy this on a separate domain (e.g., license.yoursite.com)
 * 
 * IMPORTANT: This is a basic example. For production use:
 * - Add proper authentication
 * - Implement rate limiting
 * - Add logging
 * - Use prepared statements (shown below)
 * - Add CSRF protection
 * - Use environment variables for DB credentials
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'license_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($request_method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Route the request
if (strpos($request_uri, '/api/activate') !== false && $request_method === 'POST') {
    handleActivate($pdo);
} elseif (strpos($request_uri, '/api/verify') !== false && $request_method === 'POST') {
    handleVerify($pdo);
} elseif (strpos($request_uri, '/api/deactivate') !== false && $request_method === 'POST') {
    handleDeactivate($pdo);
} elseif (strpos($request_uri, '/api/generate') !== false && $request_method === 'POST') {
    // Admin endpoint to generate license keys
    handleGenerate($pdo);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}

/**
 * Handle license activation
 */
function handleActivate($pdo)
{
    $license_key = $_POST['license_key'] ?? '';
    $email = $_POST['email'] ?? '';
    $domain = $_POST['domain'] ?? '';

    // Validate input
    if (empty($license_key) || empty($email) || empty($domain)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        return;
    }

    // Sanitize domain
    $domain = parse_url($domain, PHP_URL_HOST) ?: $domain;
    $domain = strtolower(trim($domain));

    try {
        // Check if license exists and is valid
        $stmt = $pdo->prepare("
            SELECT * FROM licenses 
            WHERE license_key = ? AND email = ?
        ");
        $stmt->execute([$license_key, $email]);
        $license = $stmt->fetch();

        if (!$license) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid license key or email address'
            ]);
            return;
        }

        // Check if license is expired
        if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
            echo json_encode([
                'success' => false,
                'message' => 'License has expired'
            ]);
            return;
        }

        // Check if already activated on this domain
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM license_activations 
            WHERE license_id = ? AND domain = ? AND status = 'active'
        ");
        $stmt->execute([$license['id'], $domain]);
        $existing = $stmt->fetch();

        if ($existing['count'] > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'License already activated on this domain'
            ]);
            return;
        }

        // Check activation limit
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM license_activations 
            WHERE license_id = ? AND status = 'active'
        ");
        $stmt->execute([$license['id']]);
        $activations = $stmt->fetch();

        if ($activations['count'] >= $license['max_activations']) {
            echo json_encode([
                'success' => false,
                'message' => 'Maximum number of activations reached. Please deactivate on another site first.'
            ]);
            return;
        }

        // Activate license
        $stmt = $pdo->prepare("
            INSERT INTO license_activations (license_id, domain, status, activated_at)
            VALUES (?, ?, 'active', NOW())
        ");
        $stmt->execute([$license['id'], $domain]);

        // Update license status
        $stmt = $pdo->prepare("
            UPDATE licenses 
            SET status = 'active', last_checked = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$license['id']]);

        // Log the activation
        logActivity($pdo, $license['id'], 'activate', $domain, 'License activated successfully');

        echo json_encode([
            'success' => true,
            'message' => 'License activated successfully'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server error occurred'
        ]);
        error_log('License activation error: ' . $e->getMessage());
    }
}

/**
 * Handle license verification
 */
function handleVerify($pdo)
{
    $license_key = $_POST['license_key'] ?? '';
    $domain = $_POST['domain'] ?? '';

    if (empty($license_key) || empty($domain)) {
        http_response_code(400);
        echo json_encode(['valid' => false, 'message' => 'Missing required fields']);
        return;
    }

    $domain = parse_url($domain, PHP_URL_HOST) ?: $domain;
    $domain = strtolower(trim($domain));

    try {
        $stmt = $pdo->prepare("
            SELECT l.*, la.domain, la.status as activation_status
            FROM licenses l
            LEFT JOIN license_activations la ON l.id = la.license_id AND la.domain = ?
            WHERE l.license_key = ?
        ");
        $stmt->execute([$domain, $license_key]);
        $license = $stmt->fetch();

        if (!$license || $license['activation_status'] !== 'active') {
            echo json_encode([
                'valid' => false,
                'message' => 'License not active on this domain'
            ]);
            return;
        }

        // Check expiration
        if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
            // Deactivate expired license
            $stmt = $pdo->prepare("
                UPDATE license_activations 
                SET status = 'expired' 
                WHERE license_id = ? AND domain = ?
            ");
            $stmt->execute([$license['id'], $domain]);

            echo json_encode([
                'valid' => false,
                'message' => 'License has expired'
            ]);
            return;
        }

        // Update last checked timestamp
        $stmt = $pdo->prepare("
            UPDATE licenses 
            SET last_checked = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$license['id']]);

        echo json_encode([
            'valid' => true,
            'message' => 'License is valid',
            'expires_at' => $license['expires_at']
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['valid' => false, 'message' => 'Server error']);
        error_log('License verification error: ' . $e->getMessage());
    }
}

/**
 * Handle license deactivation
 */
function handleDeactivate($pdo)
{
    $license_key = $_POST['license_key'] ?? '';
    $domain = $_POST['domain'] ?? '';

    if (empty($license_key) || empty($domain)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    $domain = parse_url($domain, PHP_URL_HOST) ?: $domain;
    $domain = strtolower(trim($domain));

    try {
        $stmt = $pdo->prepare("
            SELECT l.id FROM licenses l
            WHERE l.license_key = ?
        ");
        $stmt->execute([$license_key]);
        $license = $stmt->fetch();

        if (!$license) {
            echo json_encode(['success' => false, 'message' => 'Invalid license key']);
            return;
        }

        // Deactivate
        $stmt = $pdo->prepare("
            UPDATE license_activations 
            SET status = 'deactivated', deactivated_at = NOW()
            WHERE license_id = ? AND domain = ?
        ");
        $stmt->execute([$license['id'], $domain]);

        // Log the deactivation
        logActivity($pdo, $license['id'], 'deactivate', $domain, 'License deactivated');

        echo json_encode([
            'success' => true,
            'message' => 'License deactivated successfully'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error']);
        error_log('License deactivation error: ' . $e->getMessage());
    }
}

/**
 * Generate new license key (Admin only)
 * Add proper authentication before using in production!
 */
function handleGenerate($pdo)
{
    // TODO: Add admin authentication here!
    $admin_key = $_POST['admin_key'] ?? '';
    
    if ($admin_key !== 'YOUR_SECRET_ADMIN_KEY') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $email = $_POST['email'] ?? '';
    $max_activations = (int)($_POST['max_activations'] ?? 1);
    $expires_in_days = (int)($_POST['expires_in_days'] ?? 365);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid email required']);
        return;
    }

    try {
        // Generate unique license key
        $license_key = generateLicenseKey();
        
        // Calculate expiration
        $expires_at = null;
        if ($expires_in_days > 0) {
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_in_days} days"));
        }

        // Insert license
        $stmt = $pdo->prepare("
            INSERT INTO licenses (license_key, email, max_activations, expires_at, status, created_at)
            VALUES (?, ?, ?, ?, 'inactive', NOW())
        ");
        $stmt->execute([$license_key, $email, $max_activations, $expires_at]);

        echo json_encode([
            'success' => true,
            'license_key' => $license_key,
            'email' => $email,
            'max_activations' => $max_activations,
            'expires_at' => $expires_at
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to generate license']);
        error_log('License generation error: ' . $e->getMessage());
    }
}

/**
 * Generate a unique license key
 */
function generateLicenseKey()
{
    $segments = [];
    for ($i = 0; $i < 4; $i++) {
        $segments[] = strtoupper(bin2hex(random_bytes(4)));
    }
    return implode('-', $segments);
}

/**
 * Log activity
 */
function logActivity($pdo, $license_id, $action, $domain, $message)
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO license_logs (license_id, action, domain, message, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $license_id,
            $action,
            $domain,
            $message,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log('Failed to log activity: ' . $e->getMessage());
    }
}

/**
 * Database Schema
 * Run this SQL to create the necessary tables:
 * 
 * CREATE TABLE licenses (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     license_key VARCHAR(255) UNIQUE NOT NULL,
 *     email VARCHAR(255) NOT NULL,
 *     max_activations INT DEFAULT 1,
 *     status ENUM('active', 'inactive', 'expired', 'suspended') DEFAULT 'inactive',
 *     expires_at DATETIME NULL,
 *     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 *     last_checked DATETIME NULL,
 *     INDEX(license_key),
 *     INDEX(email),
 *     INDEX(status)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 * 
 * CREATE TABLE license_activations (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     license_id INT NOT NULL,
 *     domain VARCHAR(255) NOT NULL,
 *     status ENUM('active', 'deactivated', 'expired') DEFAULT 'active',
 *     activated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 *     deactivated_at DATETIME NULL,
 *     FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
 *     INDEX(license_id),
 *     INDEX(domain),
 *     INDEX(status)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 * 
 * CREATE TABLE license_logs (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     license_id INT NOT NULL,
 *     action VARCHAR(50) NOT NULL,
 *     domain VARCHAR(255),
 *     message TEXT,
 *     ip_address VARCHAR(45),
 *     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 *     FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
 *     INDEX(license_id),
 *     INDEX(created_at)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */
