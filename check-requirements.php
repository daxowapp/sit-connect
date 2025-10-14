<?php
/**
 * SIT Search Requirements Checker
 * Access via: https://yoursite.com/wp-content/plugins/sit-search/check-requirements.php
 * 
 * IMPORTANT: Delete this file after checking!
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<title>SIT Search - Requirements Check</title>";
echo "<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #AA151B; margin-top: 0; }
    .pass { color: #28a745; font-weight: bold; }
    .fail { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #AA151B; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .summary { padding: 20px; border-radius: 5px; margin: 20px 0; }
    .summary.success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .summary.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }
    code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    .delete-warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
</style></head><body><div class='container'>";

echo "<h1>🔍 SIT Search Plugin - Requirements Check</h1>";
echo "<p>Generated: " . date('Y-m-d H:i:s') . "</p>";

echo "<table>";
echo "<tr><th>Check</th><th>Status</th><th>Details</th></tr>";

$all_pass = true;

// PHP Version
$php_version = phpversion();
$php_ok = version_compare($php_version, '7.2', '>=');
$all_pass = $all_pass && $php_ok;
echo "<tr>";
echo "<td><strong>PHP Version</strong></td>";
echo "<td class='" . ($php_ok ? 'pass' : 'fail') . "'>" . ($php_ok ? '✓ PASS' : '✗ FAIL') . "</td>";
echo "<td>Current: <code>$php_version</code> | Required: <code>7.2+</code></td>";
echo "</tr>";

// Vendor Autoloader
$vendor_path = __DIR__ . '/vendor/autoload.php';
$vendor_exists = file_exists($vendor_path);
$all_pass = $all_pass && $vendor_exists;
echo "<tr>";
echo "<td><strong>Composer Dependencies</strong></td>";
echo "<td class='" . ($vendor_exists ? 'pass' : 'fail') . "'>" . ($vendor_exists ? '✓ PASS' : '✗ FAIL') . "</td>";
echo "<td>" . ($vendor_exists ? '<code>vendor/autoload.php</code> found' : '<code>vendor/autoload.php</code> <strong>MISSING</strong>') . "</td>";
echo "</tr>";

// File Permissions
$is_readable = $vendor_exists && is_readable($vendor_path);
$all_pass = $all_pass && $is_readable;
echo "<tr>";
echo "<td><strong>File Permissions</strong></td>";
echo "<td class='" . ($is_readable ? 'pass' : 'fail') . "'>" . ($is_readable ? '✓ PASS' : '✗ FAIL') . "</td>";
echo "<td>" . ($is_readable ? 'Files are readable' : 'Permission denied - check file permissions') . "</td>";
echo "</tr>";

// Try to load autoloader
$autoloader_works = false;
if ($vendor_exists && $is_readable) {
    try {
        require_once $vendor_path;
        $autoloader_works = true;
    } catch (Exception $e) {
        $autoloader_works = false;
    }
}
echo "<tr>";
echo "<td><strong>Autoloader Loading</strong></td>";
echo "<td class='" . ($autoloader_works ? 'pass' : 'fail') . "'>" . ($autoloader_works ? '✓ PASS' : '✗ FAIL') . "</td>";
echo "<td>" . ($autoloader_works ? 'Autoloader loads successfully' : 'Autoloader failed to load') . "</td>";
echo "</tr>";

// Check if App class exists
$app_class_exists = $autoloader_works && class_exists('\\SIT\\Search\\App');
echo "<tr>";
echo "<td><strong>Plugin Main Class</strong></td>";
echo "<td class='" . ($app_class_exists ? 'pass' : 'fail') . "'>" . ($app_class_exists ? '✓ PASS' : '✗ FAIL') . "</td>";
echo "<td>" . ($app_class_exists ? '<code>SIT\\Search\\App</code> class found' : '<code>SIT\\Search\\App</code> class NOT FOUND') . "</td>";
echo "</tr>";

// Memory Limit
$memory_limit = ini_get('memory_limit');
$memory_value = preg_replace('/[^0-9]/', '', $memory_limit);
$memory_ok = $memory_value >= 128;
echo "<tr>";
echo "<td><strong>PHP Memory Limit</strong></td>";
echo "<td class='" . ($memory_ok ? 'pass' : 'warning') . "'>" . ($memory_ok ? '✓ PASS' : '⚠ WARNING') . "</td>";
echo "<td>Current: <code>$memory_limit</code> | Recommended: <code>256M+</code></td>";
echo "</tr>";

// Max Execution Time
$max_time = ini_get('max_execution_time');
$time_ok = (int)$max_time >= 30 || (int)$max_time === 0;
echo "<tr>";
echo "<td><strong>Max Execution Time</strong></td>";
echo "<td class='" . ($time_ok ? 'pass' : 'warning') . "'>" . ($time_ok ? '✓ PASS' : '⚠ WARNING') . "</td>";
echo "<td>Current: <code>{$max_time}s</code> | Recommended: <code>60s+</code></td>";
echo "</tr>";

// Required Extensions
$required_extensions = ['curl', 'json', 'mbstring', 'mysqli', 'openssl'];
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $all_pass = $all_pass && $loaded;
    echo "<tr>";
    echo "<td><strong>PHP Extension: $ext</strong></td>";
    echo "<td class='" . ($loaded ? 'pass' : 'fail') . "'>" . ($loaded ? '✓ PASS' : '✗ FAIL') . "</td>";
    echo "<td>" . ($loaded ? 'Loaded' : '<strong>NOT LOADED</strong>') . "</td>";
    echo "</tr>";
}

// WordPress Detection
$wp_load = false;
$wp_path = '';
$possible_paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    '../../../../../wp-load.php'
];
foreach ($possible_paths as $path) {
    if (file_exists(__DIR__ . '/' . $path)) {
        $wp_load = true;
        $wp_path = $path;
        break;
    }
}
echo "<tr>";
echo "<td><strong>WordPress Installation</strong></td>";
echo "<td class='" . ($wp_load ? 'pass' : 'warning') . "'>" . ($wp_load ? '✓ PASS' : '⚠ WARNING') . "</td>";
echo "<td>" . ($wp_load ? '<code>wp-load.php</code> found' : 'Cannot detect WordPress') . "</td>";
echo "</tr>";

// Check if ACF is available
$acf_active = false;
if ($wp_load) {
    try {
        require_once(__DIR__ . '/' . $wp_path);
        $acf_active = function_exists('get_field');
    } catch (Exception $e) {
        // Ignore
    }
    echo "<tr>";
    echo "<td><strong>ACF Plugin</strong></td>";
    echo "<td class='" . ($acf_active ? 'pass' : 'fail') . "'>" . ($acf_active ? '✓ PASS' : '✗ FAIL') . "</td>";
    echo "<td>" . ($acf_active ? 'Advanced Custom Fields is active' : 'ACF is <strong>NOT active or installed</strong>') . "</td>";
    echo "</tr>";
}

echo "</table>";

// Summary
if ($all_pass && $acf_active) {
    echo "<div class='summary success'>";
    echo "<h2>✅ All Requirements Met!</h2>";
    echo "<p>All critical requirements are satisfied. The plugin should work correctly.</p>";
    echo "<p><strong>If the plugin still fails to activate:</strong></p>";
    echo "<ol>";
    echo "<li>Enable debug mode in <code>wp-config.php</code></li>";
    echo "<li>Check <code>/wp-content/debug.log</code> for specific error messages</li>";
    echo "<li>Check your server's error log in cPanel</li>";
    echo "<li>Ensure you uploaded files in <strong>BINARY mode</strong> (not ASCII)</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='summary error'>";
    echo "<h2>❌ Requirements Not Met</h2>";
    echo "<p>The plugin will NOT work until these issues are fixed:</p>";
    echo "<ul>";
    if (!$php_ok) echo "<li><strong>Upgrade PHP</strong> to version 7.2 or higher (current: $php_version)</li>";
    if (!$vendor_exists) echo "<li><strong>Upload the vendor folder</strong> - Run <code>composer install</code> locally and upload the entire <code>vendor</code> directory</li>";
    if (!$is_readable) echo "<li><strong>Fix file permissions</strong> - Run: <code>chmod 644 vendor/autoload.php</code></li>";
    if (!$app_class_exists && $vendor_exists) echo "<li><strong>Reinstall Composer dependencies</strong> - Delete vendor folder and run <code>composer install --no-dev</code></li>";
    if (!$acf_active) echo "<li><strong>Install and activate</strong> the Advanced Custom Fields (ACF) plugin</li>";
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            echo "<li><strong>Enable PHP extension:</strong> $ext (contact your hosting provider)</li>";
        }
    }
    echo "</ul>";
    echo "</div>";
}

// Next Steps
echo "<div class='info-box'>";
echo "<h3>📋 Next Steps</h3>";
echo "<ol>";
echo "<li><strong>Fix any failed checks</strong> shown above</li>";
echo "<li><strong>Enable WordPress debug mode</strong> by adding to <code>wp-config.php</code>:";
echo "<pre style='background:#f4f4f4; padding:10px; border-radius:3px; overflow-x:auto;'>define('WP_DEBUG', true);\ndefine('WP_DEBUG_LOG', true);\ndefine('WP_DEBUG_DISPLAY', false);</pre></li>";
echo "<li><strong>Try to activate the plugin</strong> in WordPress admin</li>";
echo "<li><strong>Check the error log</strong> at <code>/wp-content/debug.log</code></li>";
echo "<li><strong>Share the error message</strong> for further assistance</li>";
echo "</ol>";
echo "</div>";

// Delete warning
echo "<div class='delete-warning'>";
echo "<h3>⚠️ IMPORTANT: Security Warning</h3>";
echo "<p><strong>DELETE THIS FILE</strong> after checking! This diagnostic file should not remain on your live server.</p>";
echo "<p>File location: <code>" . __FILE__ . "</code></p>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align:center; color:#666;'><small>SIT Search Plugin Diagnostic Tool | Generated: " . date('Y-m-d H:i:s') . "</small></p>";

echo "</div></body></html>";
?>
