# 🔍 How to Debug the Live Server Error

## Step 1: Enable WordPress Debug Mode

### Add to wp-config.php (on live server)

**IMPORTANT:** Add these lines **BEFORE** the line that says `/* That's all, stop editing! */`

```php
// Enable WordPress Debug Mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);

// Additional debugging
define('SCRIPT_DEBUG', true);
define('SAVEQUERIES', true);
```

---

## Step 2: Check the Error Log

After enabling debug mode, try to activate the plugin again. Then check:

### Location 1: WordPress Debug Log
```
/wp-content/debug.log
```

**How to access:**
- Via FTP: Download the file
- Via cPanel: File Manager → Navigate to wp-content → View debug.log
- Via SSH: `tail -100 /path/to/wp-content/debug.log`

### Location 2: PHP Error Log
```
# Common locations:
/var/log/php-errors.log
/var/log/php/error.log
/usr/local/lsws/logs/error.log  (LiteSpeed)
```

**How to access via cPanel:**
- cPanel → Errors → Error Log
- Look for the most recent errors

---

## Step 3: What to Look For

The error log will show something like:

### Example Error Messages

#### Missing Composer Dependencies
```
PHP Fatal error: Uncaught Error: Class 'SIT\Search\App' not found
```
**Solution:** Upload the vendor folder

#### File Permission Issues
```
PHP Warning: require_once(/path/to/vendor/autoload.php): failed to open stream: Permission denied
```
**Solution:** Fix file permissions (chmod 644)

#### PHP Version Issue
```
PHP Parse error: syntax error, unexpected '?', expecting variable
```
**Solution:** Upgrade PHP to 7.2+

#### Missing ACF Plugin
```
PHP Fatal error: Uncaught Error: Call to undefined function get_field()
```
**Solution:** Install Advanced Custom Fields plugin

#### Memory Limit
```
PHP Fatal error: Allowed memory size of 134217728 bytes exhausted
```
**Solution:** Increase PHP memory limit

---

## Step 4: Quick Diagnostic Script

Create this file on your live server to check requirements:

### Create: wp-content/plugins/sit-search/check-requirements.php

```php
<?php
/**
 * SIT Search Requirements Checker
 * Access via: https://yoursite.com/wp-content/plugins/sit-search/check-requirements.php
 */

echo "<h1>SIT Search Plugin - Requirements Check</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #AA151B; color: white; }
</style>";

echo "<table>";
echo "<tr><th>Check</th><th>Status</th><th>Details</th></tr>";

// PHP Version
$php_version = phpversion();
$php_ok = version_compare($php_version, '7.2', '>=');
echo "<tr>";
echo "<td>PHP Version</td>";
echo "<td class='" . ($php_ok ? 'pass' : 'fail') . "'>" . ($php_ok ? '✓ PASS' : '✗ FAIL') . "</td>";
echo "<td>Current: $php_version | Required: 7.2+</td>";
echo "</tr>";

// Vendor Autoloader
$vendor_path = __DIR__ . '/vendor/autoload.php';
$vendor_exists = file_exists($vendor_path);
echo "<tr>";
echo "<td>Composer Dependencies</td>";
echo "<td class='" . ($vendor_exists ? 'pass' : 'fail') . "'>" . ($vendor_exists ? '✓ PASS' : '✗ FAIL') . "</td>";
echo "<td>" . ($vendor_exists ? 'vendor/autoload.php found' : 'vendor/autoload.php MISSING') . "</td>";
echo "</tr>";

// File Permissions
$is_readable = is_readable($vendor_path);
echo "<tr>";
echo "<td>File Permissions</td>";
echo "<td class='" . ($is_readable ? 'pass' : 'fail') . "'>" . ($is_readable ? '✓ PASS' : '✗ FAIL') . "</td>";
echo "<td>" . ($is_readable ? 'Files are readable' : 'Permission denied') . "</td>";
echo "</tr>";

// Memory Limit
$memory_limit = ini_get('memory_limit');
$memory_ok = (int)$memory_limit >= 128;
echo "<tr>";
echo "<td>PHP Memory Limit</td>";
echo "<td class='" . ($memory_ok ? 'pass' : 'warning') . "'>" . ($memory_ok ? '✓ PASS' : '⚠ WARNING') . "</td>";
echo "<td>Current: $memory_limit | Recommended: 256M+</td>";
echo "</tr>";

// Max Execution Time
$max_time = ini_get('max_execution_time');
$time_ok = (int)$max_time >= 60;
echo "<tr>";
echo "<td>Max Execution Time</td>";
echo "<td class='" . ($time_ok ? 'pass' : 'warning') . "'>" . ($time_ok ? '✓ PASS' : '⚠ WARNING') . "</td>";
echo "<td>Current: {$max_time}s | Recommended: 60s+</td>";
echo "</tr>";

// Required Extensions
$extensions = ['curl', 'json', 'mbstring', 'mysqli', 'openssl'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<tr>";
    echo "<td>PHP Extension: $ext</td>";
    echo "<td class='" . ($loaded ? 'pass' : 'fail') . "'>" . ($loaded ? '✓ PASS' : '✗ FAIL') . "</td>";
    echo "<td>" . ($loaded ? 'Loaded' : 'NOT LOADED') . "</td>";
    echo "</tr>";
}

// WordPress Detection
$wp_load = false;
$possible_paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    '../../../../../wp-load.php'
];
foreach ($possible_paths as $path) {
    if (file_exists(__DIR__ . '/' . $path)) {
        $wp_load = true;
        break;
    }
}
echo "<tr>";
echo "<td>WordPress Installation</td>";
echo "<td class='" . ($wp_load ? 'pass' : 'warning') . "'>" . ($wp_load ? '✓ PASS' : '⚠ WARNING') . "</td>";
echo "<td>" . ($wp_load ? 'wp-load.php found' : 'Cannot detect WordPress') . "</td>";
echo "</tr>";

// Check if ACF is available (requires WordPress)
if ($wp_load) {
    require_once(__DIR__ . '/' . $path);
    $acf_active = function_exists('get_field');
    echo "<tr>";
    echo "<td>ACF Plugin</td>";
    echo "<td class='" . ($acf_active ? 'pass' : 'fail') . "'>" . ($acf_active ? '✓ PASS' : '✗ FAIL') . "</td>";
    echo "<td>" . ($acf_active ? 'Advanced Custom Fields is active' : 'ACF is NOT active or installed') . "</td>";
    echo "</tr>";
}

echo "</table>";

// Summary
$all_critical_pass = $php_ok && $vendor_exists && $is_readable;
echo "<h2>Summary</h2>";
if ($all_critical_pass) {
    echo "<p class='pass'>✓ All critical requirements are met. Plugin should work.</p>";
    echo "<p><strong>If plugin still fails:</strong></p>";
    echo "<ol>";
    echo "<li>Check <code>/wp-content/debug.log</code> for specific error messages</li>";
    echo "<li>Verify ACF plugin is installed and active</li>";
    echo "<li>Check server error logs in cPanel</li>";
    echo "</ol>";
} else {
    echo "<p class='fail'>✗ Critical requirements are NOT met. Plugin will fail.</p>";
    echo "<p><strong>Fix these issues:</strong></p>";
    echo "<ul>";
    if (!$php_ok) echo "<li>Upgrade PHP to version 7.2 or higher</li>";
    if (!$vendor_exists) echo "<li>Upload the <code>vendor</code> folder (run <code>composer install</code>)</li>";
    if (!$is_readable) echo "<li>Fix file permissions: <code>chmod 644 vendor/autoload.php</code></li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><small>Generated: " . date('Y-m-d H:i:s') . "</small></p>";
echo "<p><small><strong>IMPORTANT:</strong> Delete this file after checking!</small></p>";
?>
```

---

## Step 5: Access the Diagnostic Script

1. **Upload** the `check-requirements.php` file to your live server
2. **Access** via browser: `https://yoursite.com/wp-content/plugins/sit-search/check-requirements.php`
3. **Review** the results - it will show exactly what's wrong
4. **Delete** the file after checking (security)

---

## Step 6: Common Issues & Solutions

### Issue 1: Vendor Folder Missing

**Error in log:**
```
Fatal error: Class 'SIT\Search\App' not found
```

**Solution:**
```bash
# On your local machine
cd /path/to/sit-search
composer install --no-dev --optimize-autoloader

# Then upload the entire vendor folder to live server
```

**Or download vendor folder from:**
- Packagist.org
- Or ensure it's included in your ZIP upload

---

### Issue 2: File Uploaded in ASCII Mode

**Symptom:** Random parse errors, corrupted files

**Solution:**
1. Delete plugin folder from server
2. Set FTP client to **BINARY mode**
3. Re-upload entire plugin folder

**In FileZilla:**
- Transfer → Transfer Type → Binary

---

### Issue 3: PHP Version Too Old

**Error in log:**
```
Parse error: syntax error, unexpected '?'
```

**Solution:**
- Contact hosting provider to upgrade PHP
- Or change PHP version in cPanel → Select PHP Version

---

### Issue 4: Memory Limit Too Low

**Error in log:**
```
Allowed memory size exhausted
```

**Solution:**

Add to `wp-config.php`:
```php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

Or create `.user.ini` in plugin folder:
```ini
memory_limit = 256M
```

---

### Issue 5: ACF Plugin Not Active

**Error in log:**
```
Call to undefined function get_field()
```

**Solution:**
1. Install Advanced Custom Fields plugin
2. Activate it
3. Then activate SIT Search plugin

---

## Step 7: Test Plugin Activation via WP-CLI

If you have SSH access:

```bash
# SSH into server
ssh user@yourserver.com

# Navigate to WordPress root
cd /path/to/wordpress

# Try to activate plugin with debug output
wp plugin activate sit-search --debug

# This will show detailed error messages
```

---

## Step 8: Manual Error Check

If you can't access error logs, create this test file:

### Create: wp-content/plugins/sit-search/test-load.php

```php
<?php
// Test if plugin can load
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing SIT Search Plugin Load</h1>";

// Test 1: Check vendor autoloader
echo "<h2>Test 1: Vendor Autoloader</h2>";
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    echo "✓ vendor/autoload.php exists<br>";
    try {
        require_once $autoloader;
        echo "✓ Autoloader loaded successfully<br>";
    } catch (Exception $e) {
        echo "✗ Error loading autoloader: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ vendor/autoload.php NOT FOUND<br>";
    echo "Path checked: $autoloader<br>";
}

// Test 2: Check if App class exists
echo "<h2>Test 2: App Class</h2>";
if (class_exists('\\SIT\\Search\\App')) {
    echo "✓ SIT\\Search\\App class found<br>";
} else {
    echo "✗ SIT\\Search\\App class NOT FOUND<br>";
    echo "This means Composer dependencies are not loaded correctly<br>";
}

// Test 3: Check WordPress
echo "<h2>Test 3: WordPress</h2>";
if (defined('ABSPATH')) {
    echo "✓ WordPress is loaded<br>";
    echo "WordPress path: " . ABSPATH . "<br>";
} else {
    echo "⚠ WordPress not loaded in this test<br>";
}

echo "<hr>";
echo "<p><strong>If all tests pass but plugin still fails, check wp-content/debug.log</strong></p>";
echo "<p><small>Delete this file after testing!</small></p>";
?>
```

**Access:** `https://yoursite.com/wp-content/plugins/sit-search/test-load.php`

---

## Step 9: Check Server Error Logs

### Via cPanel
1. Login to cPanel
2. Go to **Errors** section
3. Click **Error Log**
4. Look for recent PHP errors

### Via SSH
```bash
# Apache
tail -100 /var/log/apache2/error.log

# Nginx
tail -100 /var/log/nginx/error.log

# PHP-FPM
tail -100 /var/log/php-fpm/error.log
```

---

## Step 10: Contact Information Needed

If you still can't find the issue, provide:

1. **PHP Version** (from check-requirements.php)
2. **Error message** from debug.log or error log
3. **Server type** (Apache, Nginx, LiteSpeed)
4. **Hosting provider** (shared, VPS, dedicated)
5. **WordPress version**
6. **Screenshot** of the error page

---

## Quick Checklist

- [ ] Enabled WP_DEBUG in wp-config.php
- [ ] Checked /wp-content/debug.log
- [ ] Ran check-requirements.php
- [ ] Verified vendor folder uploaded
- [ ] Checked file permissions (644/755)
- [ ] Verified PHP version 7.2+
- [ ] Confirmed ACF plugin is active
- [ ] Uploaded in BINARY mode (not ASCII)
- [ ] Checked server error logs
- [ ] Tested with test-load.php

---

## 🆘 Emergency: Restore Site

If site is completely broken:

### Via FTP
1. Rename plugin folder: `sit-search` → `sit-search-disabled`
2. Site should come back online

### Via Database
```sql
UPDATE wp_options 
SET option_value = '' 
WHERE option_name = 'active_plugins';
```

---

**The error logs will tell us EXACTLY what's wrong. Follow these steps and share the error message!**
