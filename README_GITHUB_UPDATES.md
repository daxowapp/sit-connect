# GitHub Auto-Updates for SIT Connect

## ✅ Implementation Complete

Your plugin now supports automatic updates from GitHub! Here's what was implemented:

---

## 🎯 What You Get

### For You (Developer)
- Push code to GitHub
- Create a release/tag
- Users automatically see updates in WordPress

### For Users
- See "Update available" notification in WordPress admin
- One-click update installation
- No manual downloads needed
- Seamless update experience

---

## 📁 Files Modified/Created

### Modified Files
1. **`sit-connect.php`** (lines 93-123)
   - Added GitHub update checker initialization
   - Supports both public and private repositories
   - Reads GitHub token from wp-config.php

2. **`composer.json`** (line 17)
   - Added `yahnis-elsts/plugin-update-checker` dependency

### New Files Created
1. **`GITHUB_AUTO_UPDATE_SETUP.md`** - Detailed setup guide
2. **`RELEASE_WORKFLOW.md`** - Complete release process documentation
3. **`QUICK_START_GITHUB_UPDATES.md`** - 5-minute quick start
4. **`.gitignore`** - Prevents committing sensitive files
5. **`README_GITHUB_UPDATES.md`** - This file

---

## 🚀 How It Works

### The Flow

```
┌─────────────────┐
│  You (Windsurf) │
│  Make changes   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Update version │
│  in plugin file │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Commit & Push  │
│  to GitHub      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Create Git Tag │
│  (e.g., v2.0.1) │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Create GitHub   │
│    Release      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ GitHub creates  │
│   release ZIP   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  WordPress      │
│  checks GitHub  │
│  (every 12hrs)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ User sees       │
│ "Update Now"    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ One-click       │
│    Update!      │
└─────────────────┘
```

---

## 🔧 Technical Details

### Update Checker Configuration

**Location:** `sit-connect.php` lines 97-122

```php
function sit_connect_init_github_updater() {
    $puc_path = SIT_CONNECT_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
    
    if (file_exists($puc_path)) {
        require $puc_path;
        
        $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
            'https://github.com/your-username/sit-connect',
            SIT_CONNECT_FILE,
            'sit-connect'
        );
        
        $myUpdateChecker->setBranch('main');
        
        if (defined('SIT_CONNECT_GITHUB_TOKEN')) {
            $myUpdateChecker->setAuthentication(SIT_CONNECT_GITHUB_TOKEN);
        }
    }
}
```

### Version Detection

**Plugin Header Version:**
```php
* Version: 2.0.0
```

**GitHub Tag:**
```
v2.0.1
```

**Result:** Update detected! (2.0.1 > 2.0.0)

### Update Check Frequency

- **Default:** Every 12 hours
- **Manual:** Clear transients to force check
- **Automatic:** WordPress cron job

---

## 📦 Installation Steps

### 1. Install Dependencies

```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search
composer install
```

This installs:
- `yahnis-elsts/plugin-update-checker` (for GitHub updates)
- Other existing dependencies

### 2. Create GitHub Repository

**Option A: Via GitHub Web**
1. Go to https://github.com/new
2. Name: `sit-connect`
3. Public or Private
4. Create repository

**Option B: Via GitHub CLI**
```bash
gh repo create sit-connect --public
```

### 3. Configure Repository URL

Edit `sit-connect.php` line 107:
```php
'https://github.com/YOUR-USERNAME/sit-connect', // ← Change this
```

### 4. For Private Repos Only

Add to `wp-config.php`:
```php
// GitHub token for SIT Connect updates
define('SIT_CONNECT_GITHUB_TOKEN', 'ghp_xxxxxxxxxxxxx');
```

**Get token:** GitHub → Settings → Developer settings → Personal access tokens

### 5. Push to GitHub

```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/sit-connect.git
git push -u origin main
```

### 6. Create First Release

```bash
# Create tag
git tag v2.0.0
git push origin v2.0.0

# Create release on GitHub
# Go to: Releases → Create new release → Select v2.0.0 → Publish
```

---

## 🎓 Usage Examples

### Example 1: Bug Fix Release

```bash
# 1. Fix bug in code
# 2. Update version: 2.0.0 → 2.0.1
# 3. Commit
git add .
git commit -m "Fix: Resolved PHP warning in taxonomy access"
git push origin main

# 4. Tag
git tag v2.0.1
git push origin v2.0.1

# 5. Create release on GitHub with notes:
# "Fixed PHP warnings when accessing undefined taxonomy terms"
```

### Example 2: New Feature Release

```bash
# 1. Add new feature
# 2. Update version: 2.0.1 → 2.1.0
# 3. Commit
git add .
git commit -m "Feature: Add AI-powered search"
git push origin main

# 4. Tag
git tag v2.1.0
git push origin v2.1.0

# 5. Create release with detailed notes
```

### Example 3: Major Update

```bash
# 1. Major refactor
# 2. Update version: 2.1.0 → 3.0.0
# 3. Commit
git add .
git commit -m "Major: Complete UI redesign"
git push origin main

# 4. Tag
git tag v3.0.0
git push origin v3.0.0

# 5. Create release with breaking changes documentation
```

---

## 🔍 Verification

### Check if Update System is Active

Add this temporarily to your plugin:

```php
add_action('admin_notices', function() {
    $puc_path = SIT_CONNECT_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
    $exists = file_exists($puc_path);
    $token_defined = defined('SIT_CONNECT_GITHUB_TOKEN');
    
    echo '<div class="notice notice-info">';
    echo '<p><strong>GitHub Update System Status:</strong></p>';
    echo '<p>Library installed: ' . ($exists ? '✅ Yes' : '❌ No') . '</p>';
    echo '<p>GitHub token defined: ' . ($token_defined ? '✅ Yes' : '❌ No (only needed for private repos)') . '</p>';
    echo '</div>';
});
```

### Force Update Check

```php
// Add to functions.php temporarily
delete_site_transient('update_plugins');
```

Or via WP-CLI:
```bash
wp transient delete --all
```

---

## 📊 Version Comparison Logic

| Installed | GitHub | Result |
|-----------|--------|--------|
| 2.0.0 | v2.0.0 | No update |
| 2.0.0 | v2.0.1 | Update available |
| 2.0.0 | v2.1.0 | Update available |
| 2.0.0 | v3.0.0 | Update available |
| 2.0.1 | v2.0.0 | No update (newer installed) |

---

## 🛡️ Security Best Practices

### ✅ DO
- Store GitHub token in `wp-config.php`
- Add `.gitignore` to exclude sensitive files
- Use semantic versioning
- Test updates on staging first
- Review code before releases
- Use HTTPS for all API calls

### ❌ DON'T
- Hardcode GitHub token in plugin files
- Commit `wp-config.php` to repository
- Skip version testing
- Release without testing
- Ignore security updates

---

## 🐛 Troubleshooting

### Problem: Update not showing

**Solutions:**
1. Clear WordPress transients
2. Check version numbers match
3. Verify GitHub release exists
4. Check repository URL is correct

### Problem: Private repo not working

**Solutions:**
1. Verify token has `repo` scope
2. Check token is in wp-config.php
3. Test token with curl:
   ```bash
   curl -H "Authorization: token YOUR_TOKEN" \
     https://api.github.com/repos/your-username/sit-connect
   ```

### Problem: Library not found

**Solutions:**
1. Run `composer install`
2. Check `vendor/` directory exists
3. Verify composer.json has the dependency

---

## 📚 Additional Resources

### Documentation Files
- **`GITHUB_AUTO_UPDATE_SETUP.md`** - Complete setup guide
- **`RELEASE_WORKFLOW.md`** - Detailed release process
- **`QUICK_START_GITHUB_UPDATES.md`** - Quick 5-minute setup
- **`DEPLOYMENT_CHECKLIST.md`** - Pre-deployment checklist

### External Links
- [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker)
- [GitHub Releases](https://docs.github.com/en/repositories/releasing-projects-on-github)
- [WordPress Plugin Updates](https://developer.wordpress.org/plugins/plugin-basics/updating-your-plugin/)
- [Semantic Versioning](https://semver.org/)

---

## ✨ Benefits

### For Developers
- ✅ No need to manually distribute updates
- ✅ Version control integrated with updates
- ✅ Easy rollback (just create new release)
- ✅ Automatic changelog from GitHub releases
- ✅ Works with CI/CD pipelines

### For Users
- ✅ Automatic update notifications
- ✅ One-click updates
- ✅ No manual downloads
- ✅ Always up-to-date
- ✅ Seamless experience

---

## 🎉 You're All Set!

Your plugin now has professional-grade automatic updates from GitHub!

**Next Steps:**
1. Run `composer install`
2. Create GitHub repository
3. Update repository URL in code
4. Push code to GitHub
5. Create first release
6. Test on staging site
7. Enjoy automatic updates! 🚀

---

## 📞 Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review the documentation files
3. Check Plugin Update Checker GitHub issues
4. Verify GitHub API status

**Happy coding!** 💻✨
