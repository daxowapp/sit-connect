# Color Customization Debug Guide

## 🔍 How to Check if Colors Are Working

### Step 1: Verify Colors Are Saved in Database

Add this code temporarily to check saved colors:

1. Go to **SIT Connect** → **Settings** → **Color Customization**
2. Open browser console (F12)
3. Run this in console:
```javascript
// Check what colors are saved
fetch('/wp-admin/admin-ajax.php?action=check_colors')
```

Or add this PHP snippet temporarily to your theme's `functions.php`:
```php
add_action('wp_footer', function() {
    if (current_user_can('manage_options')) {
        echo '<!-- SIT Connect Colors Debug -->';
        echo '<!-- Primary: ' . get_option('sit_connect_primary_color', 'NOT SET') . ' -->';
        echo '<!-- Primary Dark: ' . get_option('sit_connect_primary_dark_color', 'NOT SET') . ' -->';
        echo '<!-- Secondary: ' . get_option('sit_connect_secondary_color', 'NOT SET') . ' -->';
        echo '<!-- Accent: ' . get_option('sit_connect_accent_color', 'NOT SET') . ' -->';
    }
});
```

### Step 2: Check if Style Tag is Injected

1. Visit your website frontend
2. Right-click → **View Page Source**
3. Press Ctrl+F (or Cmd+F) and search for: `sit-connect-custom-colors`
4. You should see something like:
```html
<style id="sit-connect-custom-colors">
    :root {
        --apply-primary: #YOUR_COLOR !important;
        --apply-primary-dark: #YOUR_COLOR !important;
        ...
    }
</style>
```

**If you DON'T see this:** The injection isn't working.

### Step 3: Check Browser Console

1. Open your website
2. Press F12 to open Developer Tools
3. Go to **Console** tab
4. Look for any JavaScript errors
5. Go to **Elements** tab
6. Find the `<head>` section
7. Look for `<style id="sit-connect-custom-colors">`

### Step 4: Test CSS Variables

1. Open Developer Tools (F12)
2. Go to **Console** tab
3. Run this command:
```javascript
getComputedStyle(document.documentElement).getPropertyValue('--apply-primary')
```
4. It should return your custom color (e.g., `#0000FF`)

**If it returns the default color:** Variables aren't being applied.

## 🐛 Common Issues & Solutions

### Issue 1: Colors Save But Don't Show
**Cause:** Caching
**Solution:**
```bash
# Clear WordPress cache
- Go to your caching plugin settings
- Click "Clear Cache" or "Purge Cache"

# Clear browser cache
- Chrome/Edge: Ctrl+Shift+Delete
- Firefox: Ctrl+Shift+Delete
- Safari: Cmd+Option+E

# Hard refresh
- Windows: Ctrl+Shift+R
- Mac: Cmd+Shift+R
```

### Issue 2: Style Tag Not in Source
**Cause:** Hook not firing or plugin not active
**Solution:**
1. Check if plugin is active: Go to Plugins page
2. Deactivate and reactivate SIT Connect
3. Check if you're using the NEW plugin file (`sit-connect.php`)
4. Verify in database:
```sql
SELECT * FROM wp_options WHERE option_name LIKE 'sit_connect_%';
```

### Issue 3: Colors Show in Source But Not Applied
**Cause:** CSS specificity or other styles overriding
**Solution:**
Add this temporary CSS to test:
```css
/* Add to your theme's style.css or Customizer */
body {
    background: var(--apply-primary) !important;
}
```
If background changes, variables work. If not, check browser compatibility.

### Issue 4: Old Colors Still Showing
**Cause:** Browser or CDN cache
**Solution:**
1. Clear browser cache completely
2. Try incognito/private window
3. If using Cloudflare or CDN, purge cache
4. Check if CSS file is cached with timestamp

## 🔧 Manual Testing

### Test 1: Direct Database Check
Run this SQL query in phpMyAdmin:
```sql
SELECT option_name, option_value 
FROM wp_options 
WHERE option_name IN (
    'sit_connect_primary_color',
    'sit_connect_primary_dark_color',
    'sit_connect_secondary_color',
    'sit_connect_accent_color'
);
```

Expected result:
```
sit_connect_primary_color       | #YOUR_COLOR
sit_connect_primary_dark_color  | #YOUR_COLOR
sit_connect_secondary_color     | #YOUR_COLOR
sit_connect_accent_color        | #YOUR_COLOR
```

### Test 2: Check Hook Registration
Add this to `wp-config.php` temporarily:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Then add this to your theme's `functions.php`:
```php
add_action('wp_head', function() {
    error_log('wp_head hook fired');
}, 1000);
```

Check `/wp-content/debug.log` to see if hook fires.

### Test 3: Force Color Output
Temporarily add this to your theme's `header.php` (before `</head>`):
```php
<?php
$test_color = get_option('sit_connect_primary_color', 'NOT_FOUND');
echo "<!-- Test Color: $test_color -->";
?>
<style>
    :root {
        --test-primary: <?php echo $test_color; ?> !important;
    }
    body { border-top: 5px solid var(--test-primary) !important; }
</style>
```

If you see a colored border at top of page, colors are saved correctly.

## ✅ Verification Checklist

- [ ] Colors saved in admin (check success message)
- [ ] Colors in database (SQL query shows them)
- [ ] Style tag in page source (view source shows it)
- [ ] CSS variables accessible (console test works)
- [ ] No JavaScript errors (console is clean)
- [ ] Cache cleared (browser + WordPress)
- [ ] Using correct plugin file (sit-connect.php)
- [ ] Plugin is active (check plugins page)

## 🚀 Quick Fix Commands

### Clear All WordPress Caches
```php
// Add to functions.php temporarily, visit site, then remove
wp_cache_flush();
delete_transient('sit_connect_colors_cache');
```

### Force Regenerate Colors
```php
// Run this in WordPress admin → Tools → Site Health → Info → Copy site info
update_option('sit_connect_primary_color', '#FF0000');
update_option('sit_connect_primary_dark_color', '#CC0000');
update_option('sit_connect_secondary_color', '#00FF00');
update_option('sit_connect_accent_color', '#0000FF');
```

### Reset to Defaults
```php
delete_option('sit_connect_primary_color');
delete_option('sit_connect_primary_dark_color');
delete_option('sit_connect_secondary_color');
delete_option('sit_connect_accent_color');
```

## 📞 Still Not Working?

If colors still don't show after all checks:

1. **Check theme compatibility**
   - Some themes override CSS variables
   - Try with a default WordPress theme (Twenty Twenty-Four)

2. **Check for conflicts**
   - Deactivate other plugins temporarily
   - Test with only SIT Connect active

3. **Verify PHP version**
   - Requires PHP 7.2+
   - Check: `<?php echo PHP_VERSION; ?>`

4. **Check file permissions**
   - Plugin files should be readable
   - Check: `ls -la /path/to/plugin`

---

**Next Step:** Follow the checklist above and report which step fails.
