# 🚨 Troubleshooting Live Deployment Error

**Error:** "There has been a critical error on this website"

---

## ✅ Issue Identified: File Encoding Problem

### Root Cause
The main plugin file `sit-search.php` had **encoding issues** that cause PHP to fail on some servers, especially when uploading via FTP or certain deployment methods.

**Fixed:** Converted file from `unknown-8bit` to proper `UTF-8` encoding.

---

## 🔧 Immediate Fix Applied

### Files Fixed
1. ✅ `sit-search.php` - Converted to UTF-8 encoding
2. ✅ All PHP files verified for syntax errors (0 errors found)

---

## 📋 Deployment Checklist for Live Server

### Before Uploading

1. **Verify File Encoding**
   ```bash
   # All PHP files should be UTF-8
   file -b --mime-encoding sit-search.php
   # Should output: utf-8
   ```

2. **Check PHP Syntax**
   ```bash
   php -l sit-search.php
   # Should output: No syntax errors detected
   ```

3. **Verify File Permissions**
   - Directories: 755
   - PHP files: 644

---

## 🚀 Deployment Methods

### Method 1: Safe Upload (Recommended)

1. **Deactivate old plugin** on live site (if exists)
2. **Delete old plugin folder** via FTP/cPanel
3. **Upload new plugin folder** using:
   - **Binary mode** in FTP (not ASCII)
   - Or use SFTP/SSH
   - Or use cPanel File Manager (upload as ZIP)
4. **Activate plugin** in WordPress admin

### Method 2: Using WP-CLI (Safest)

```bash
# SSH into server
wp plugin deactivate sit-search
wp plugin delete sit-search
# Upload new plugin folder
wp plugin activate sit-search
```

### Method 3: Using Git (Best for Version Control)

```bash
# On server
cd wp-content/plugins/
git pull origin main
wp plugin activate sit-search
```

---

## 🔍 Common Issues & Solutions

### Issue 1: "Critical Error" After Upload

**Possible Causes:**
- File encoding issues
- File permissions incorrect
- PHP version incompatibility
- Missing dependencies

**Solutions:**

1. **Check WordPress Debug Log**
   ```php
   // Add to wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```
   Then check: `/wp-content/debug.log`

2. **Check Server Error Log**
   - cPanel: Error Log viewer
   - SSH: `tail -f /var/log/apache2/error.log`

3. **Verify PHP Version**
   ```bash
   php -v
   # Should be PHP 7.2 or higher
   ```

4. **Check File Permissions**
   ```bash
   # Set correct permissions
   find . -type d -exec chmod 755 {} \;
   find . -type f -exec chmod 644 {} \;
   ```

---

### Issue 2: White Screen of Death

**Solutions:**

1. **Disable Plugin via Database**
   ```sql
   UPDATE wp_options 
   SET option_value = '' 
   WHERE option_name = 'active_plugins';
   ```

2. **Rename Plugin Folder**
   - Via FTP, rename `sit-search` to `sit-search-disabled`
   - This will deactivate it
   - Check error logs
   - Fix issue
   - Rename back and reactivate

---

### Issue 3: Missing Dependencies

**Check if Composer dependencies are included:**

```bash
# Verify vendor folder exists
ls -la vendor/
# Should show: autoload.php, composer/, monolog/, stripe/, etc.
```

**If missing:**
```bash
composer install --no-dev --optimize-autoloader
```

---

### Issue 4: ACF Plugin Not Active

The plugin requires **Advanced Custom Fields (ACF)** to be installed and active.

**Solution:**
1. Install ACF plugin
2. Import ACF field groups (if not already done)
3. Activate sit-search plugin

---

## 🛠️ Server Requirements

### Minimum Requirements
- **PHP:** 7.2 or higher (7.4+ recommended)
- **WordPress:** 5.2 or higher
- **MySQL:** 5.6 or higher
- **Memory Limit:** 128MB minimum (256MB recommended)
- **Max Execution Time:** 60 seconds minimum

### Required PHP Extensions
- `curl`
- `json`
- `mbstring`
- `mysqli`
- `openssl`
- `zip`

**Check PHP Extensions:**
```bash
php -m | grep -E 'curl|json|mbstring|mysqli|openssl|zip'
```

---

## 📊 Debugging Steps

### Step 1: Enable WordPress Debug Mode

Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

### Step 2: Check Error Logs

**WordPress Debug Log:**
```bash
tail -100 wp-content/debug.log
```

**PHP Error Log:**
```bash
tail -100 /var/log/php-errors.log
```

**Apache/Nginx Error Log:**
```bash
tail -100 /var/log/apache2/error.log
# or
tail -100 /var/log/nginx/error.log
```

### Step 3: Test Plugin Activation

```bash
# Via WP-CLI
wp plugin activate sit-search --debug
```

### Step 4: Check Database Tables

```sql
-- Check if plugin tables exist
SHOW TABLES LIKE 'wp_sit_%';

-- Check if ACF fields are saved
SELECT * FROM wp_options WHERE option_name LIKE '%acf%';
```

---

## 🔐 Security Checklist

Before deploying to live:

- [ ] Remove all debug code (✅ Done)
- [ ] Remove test files (✅ Done)
- [ ] Verify API keys are not exposed
- [ ] Check file permissions (644 for files, 755 for directories)
- [ ] Ensure wp-config.php is not in plugin folder
- [ ] Verify .htaccess rules don't conflict

---

## 📞 Emergency Recovery

### If Site is Down

1. **Via FTP/cPanel:**
   - Rename plugin folder: `sit-search` → `sit-search-disabled`
   - Site should come back online
   - Check error logs
   - Fix issue
   - Rename back

2. **Via Database:**
   ```sql
   -- Deactivate all plugins
   UPDATE wp_options 
   SET option_value = 'a:0:{}' 
   WHERE option_name = 'active_plugins';
   ```

3. **Via wp-config.php:**
   ```php
   // Add this line to disable all plugins
   define('WP_PLUGIN_DIR', '/path/to/nowhere');
   ```

---

## ✅ Post-Deployment Verification

After successful deployment:

1. **Test Core Features:**
   - [ ] Top Universities carousel displays
   - [ ] University grid filtering works
   - [ ] Search functionality works
   - [ ] Apply Now form submits
   - [ ] Campus faculties display

2. **Check Performance:**
   - [ ] Page load time < 3 seconds
   - [ ] No JavaScript console errors
   - [ ] No PHP errors in logs

3. **Verify Data:**
   - [ ] Universities display correctly
   - [ ] Programs display correctly
   - [ ] Country filtering works
   - [ ] Images load properly

---

## 📝 Support Contacts

**If issues persist:**

1. Check WordPress debug log: `/wp-content/debug.log`
2. Check server error log
3. Verify all requirements are met
4. Test on staging environment first
5. Contact hosting support if server-related

---

## 🎯 Quick Fix Commands

```bash
# Fix file permissions
find /path/to/sit-search -type d -exec chmod 755 {} \;
find /path/to/sit-search -type f -exec chmod 644 {} \;

# Check PHP syntax
find /path/to/sit-search -name "*.php" -exec php -l {} \;

# Verify encoding
find /path/to/sit-search -name "*.php" -exec file -b --mime-encoding {} \;

# Clear all caches
wp cache flush
wp rewrite flush

# Regenerate autoload
cd /path/to/sit-search
composer dump-autoload --optimize
```

---

**Last Updated:** October 13, 2025  
**Status:** Encoding issue fixed, ready for deployment
