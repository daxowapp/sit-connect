# SIT Connect - Release Workflow Guide

## 🚀 Complete Release Process

This guide covers the entire workflow from development in Windsurf to automatic updates for WordPress users.

---

## 📋 Prerequisites

### 1. Install Plugin Update Checker Library

```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search
composer install
```

This will install the `yahnis-elsts/plugin-update-checker` library.

### 2. Create GitHub Repository

1. Go to https://github.com/new
2. Repository name: `sit-connect` (or your preferred name)
3. Choose **Public** or **Private**
4. Don't initialize with README (you'll push existing code)
5. Click "Create repository"

### 3. Configure Git (First Time Only)

```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search
git init
git remote add origin https://github.com/your-username/sit-connect.git
```

### 4. Update Plugin Code

Edit `sit-connect.php` line 107:
```php
'https://github.com/your-username/sit-connect', // Replace with YOUR repo URL
```

### 5. For Private Repos: Add GitHub Token

**Generate Token:**
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token
3. Select scopes: `repo` (full control of private repositories)
4. Copy the token

**Add to wp-config.php:**
```php
// Add this near the top of wp-config.php (NEVER commit this file!)
define('SIT_CONNECT_GITHUB_TOKEN', 'ghp_your_actual_token_here');
```

---

## 🔄 Development Workflow

### Step 1: Develop Locally in Windsurf

```bash
# Work on your features/fixes in Windsurf
# Test thoroughly on localhost
```

### Step 2: Update Version Number

**Edit `sit-connect.php` line 7:**
```php
* Version: 2.0.1  // Increment version (2.0.0 → 2.0.1)
```

**Also update the constant on line 70:**
```php
define('SIT_CONNECT_VERSION', '2.0.1');
```

**Version Numbering:**
- **Major** (2.x.x): Breaking changes, major features
- **Minor** (x.1.x): New features, backward compatible
- **Patch** (x.x.1): Bug fixes, minor improvements

### Step 3: Commit Changes

```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search

# Stage all changes
git add .

# Commit with descriptive message
git commit -m "Release v2.0.1: Add GitHub auto-updates and Zoho API UI"

# Push to GitHub
git push origin main
```

### Step 4: Create Git Tag

```bash
# Create annotated tag
git tag -a v2.0.1 -m "Version 2.0.1"

# Push tag to GitHub
git push origin v2.0.1
```

**Tag Naming Convention:**
- Always prefix with `v` (e.g., `v2.0.1`)
- Match the version in plugin header exactly

### Step 5: Create GitHub Release

#### Option A: Via GitHub Web Interface

1. Go to your repository on GitHub
2. Click **"Releases"** (right sidebar)
3. Click **"Create a new release"**
4. **Choose a tag:** Select `v2.0.1` from dropdown
5. **Release title:** `v2.0.1` or `Version 2.0.1`
6. **Description:** Add release notes (see template below)
7. Click **"Publish release"**

#### Option B: Via GitHub CLI (gh)

```bash
# Install GitHub CLI first: brew install gh
gh auth login

# Create release with notes
gh release create v2.0.1 \
  --title "v2.0.1" \
  --notes "## What's New
- Added GitHub auto-update system
- Implemented Zoho API configuration UI
- Fixed PHP warnings in taxonomy access
- Improved sync performance"
```

---

## 📦 What Happens After Release

### Automatic Process

1. **GitHub creates release ZIP:**
   - URL: `https://github.com/your-username/sit-connect/archive/refs/tags/v2.0.1.zip`
   - Contains entire plugin folder

2. **WordPress checks for updates** (every 12 hours):
   - Plugin Update Checker queries GitHub API
   - Compares installed version (2.0.0) with latest tag (v2.0.1)
   - Detects update is available

3. **User sees notification:**
   - WordPress admin → Plugins page
   - "Update available" message appears
   - Shows version number and release notes

4. **User clicks "Update Now":**
   - WordPress downloads ZIP from GitHub
   - Extracts files
   - Replaces old plugin files
   - Activates updated plugin
   - Done! ✅

---

## 📝 Release Notes Template

```markdown
## What's New in v2.0.1

### 🎉 New Features
- GitHub auto-update system for seamless plugin updates
- Zoho API configuration UI in admin settings
- Active Countries management page

### 🐛 Bug Fixes
- Fixed PHP warnings when accessing undefined taxonomy terms
- Corrected dynamic URL generation for programs and universities
- Resolved image display issues for San Pablo CEU

### ⚡ Improvements
- Enhanced Zoho sync performance with single instance reuse
- Added country filtering to reduce API credit usage
- Improved error handling in Zoho image downloads

### 🔧 Technical Changes
- Replaced all `->guid` with `get_permalink()` for dynamic URLs
- Added null coalescing operators for safe array access
- Implemented output flushing for long-running sync operations

### 📚 Documentation
- Added comprehensive deployment checklist
- Created GitHub auto-update setup guide
- Documented release workflow

### ⚠️ Breaking Changes
None - This is a backward-compatible update

### 📋 Upgrade Notes
1. Update as normal through WordPress admin
2. No database migrations required
3. Clear cache after update (if using caching plugin)

### 🔗 Links
- [Full Changelog](https://github.com/your-username/sit-connect/compare/v2.0.0...v2.0.1)
- [Documentation](https://github.com/your-username/sit-connect/wiki)
```

---

## 🧪 Testing Updates

### Test on Staging Site First

1. **Create staging site** (copy of production)
2. **Install current version** (e.g., 2.0.0)
3. **Create test release** on GitHub
4. **Check for updates** in WordPress admin
5. **Install update** and verify everything works
6. **If successful**, deploy to production

### Force Update Check

```php
// Add to functions.php temporarily
delete_site_transient('update_plugins');
```

Or use WP-CLI:
```bash
wp transient delete --all
wp plugin update sit-connect --dry-run
```

---

## 🔍 Troubleshooting

### Update Not Showing

**Check Version Numbers:**
```bash
# In sit-connect.php
grep "Version:" sit-connect.php
# Should show: * Version: 2.0.1

# On GitHub
git tag -l
# Should show: v2.0.1
```

**Clear WordPress Cache:**
```php
delete_site_transient('update_plugins');
```

**Check GitHub API:**
```bash
curl https://api.github.com/repos/your-username/sit-connect/releases/latest
```

### Private Repo Issues

**Verify Token:**
```php
// Add temporarily to test
var_dump(defined('SIT_CONNECT_GITHUB_TOKEN'));
var_dump(SIT_CONNECT_GITHUB_TOKEN); // Should show token
```

**Test GitHub API with Token:**
```bash
curl -H "Authorization: token YOUR_TOKEN" \
  https://api.github.com/repos/your-username/sit-connect/releases/latest
```

### Plugin Update Checker Not Loading

**Check Library Path:**
```php
$puc_path = SIT_CONNECT_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
var_dump(file_exists($puc_path)); // Should be true
```

**Reinstall Library:**
```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search
rm -rf vendor/
composer install
```

---

## 📊 Version History Example

| Version | Date | Description |
|---------|------|-------------|
| 2.0.1 | 2025-01-14 | GitHub auto-updates, Zoho API UI |
| 2.0.0 | 2025-01-10 | Major refactor, new features |
| 1.9.5 | 2024-12-20 | Bug fixes |
| 1.9.0 | 2024-12-01 | Initial release |

---

## 🎯 Quick Reference Commands

```bash
# Update version, commit, tag, and push
git add .
git commit -m "Release v2.0.1"
git push origin main
git tag v2.0.1
git push origin v2.0.1

# Create release (GitHub CLI)
gh release create v2.0.1 --title "v2.0.1" --notes "Release notes here"

# Force WordPress to check for updates
wp transient delete --all

# Check current version
wp plugin list | grep sit-connect
```

---

## 🔐 Security Checklist

- [ ] Never commit GitHub token to repository
- [ ] Add `.gitignore` to exclude sensitive files
- [ ] Use environment variables for API keys
- [ ] Test updates on staging before production
- [ ] Keep dependencies up to date
- [ ] Review code before each release
- [ ] Use semantic versioning
- [ ] Document breaking changes

---

## 📞 Support

**Plugin Update Checker Issues:**
- GitHub: https://github.com/YahnisElsts/plugin-update-checker/issues
- Docs: https://github.com/YahnisElsts/plugin-update-checker

**GitHub API Issues:**
- Docs: https://docs.github.com/en/rest
- Status: https://www.githubstatus.com/

**WordPress Plugin Updates:**
- Docs: https://developer.wordpress.org/plugins/plugin-basics/updating-your-plugin/

---

## ✅ Pre-Release Checklist

Before creating each release:

- [ ] All tests passing
- [ ] Version number updated in plugin header
- [ ] Version constant updated
- [ ] CHANGELOG.md updated
- [ ] README.md updated (if needed)
- [ ] Tested on staging site
- [ ] No PHP errors/warnings
- [ ] No JavaScript console errors
- [ ] Database migrations tested (if any)
- [ ] Backup created
- [ ] Release notes prepared
- [ ] GitHub repository URL correct in code

---

## 🎉 Success!

Your plugin now has automatic updates from GitHub! Users will receive updates seamlessly without manual downloads.

**Next Steps:**
1. Install Plugin Update Checker: `composer install`
2. Create GitHub repository
3. Update repository URL in code
4. Push code to GitHub
5. Create first release
6. Test on staging site
7. Enjoy automatic updates! 🚀
