# Setup Commands for GitHub Auto-Updates

## Quick Setup (Copy & Paste)

Run these commands in order:

### 1. Navigate to Plugin Directory
```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Make Setup Script Executable
```bash
chmod +x setup-github.sh
```

### 4. Run Setup Script
```bash
./setup-github.sh
```

**OR do it manually:**

### Manual Setup Commands

```bash
# Navigate to plugin directory
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search

# Install dependencies
composer install

# Initialize git (if not already)
git init
git branch -M main

# Add remote
git remote add origin https://github.com/daxowapp/sit-connect.git

# Stage all files
git add .

# Commit
git commit -m "Initial commit: SIT Connect v2.0.0 with GitHub auto-updates"

# Push to GitHub
git push -u origin main

# Create and push tag
git tag -a v2.0.0 -m "Version 2.0.0 - Initial release"
git push origin v2.0.0
```

---

## After Running Commands

### Create GitHub Release

1. Go to: https://github.com/daxowapp/sit-connect/releases
2. Click **"Create a new release"**
3. **Choose a tag:** Select `v2.0.0`
4. **Release title:** `v2.0.0`
5. **Description:**
   ```markdown
   ## SIT Connect v2.0.0
   
   ### 🎉 Initial Release
   
   #### Features
   - University and program management
   - Zoho CRM integration
   - GitHub auto-updates
   - Customizable colors
   - Advanced search capabilities
   - Active countries management
   
   #### Installation
   1. Download the plugin ZIP
   2. Upload to WordPress
   3. Activate plugin
   4. Configure Zoho API credentials
   5. Sync data from Zoho
   
   #### Requirements
   - WordPress 5.2+
   - PHP 7.2+
   - ACF Pro (recommended)
   ```
6. Click **"Publish release"**

---

## For Private Repository

If your repository is private, add this to `wp-config.php`:

```php
// GitHub Personal Access Token for SIT Connect updates
define('SIT_CONNECT_GITHUB_TOKEN', 'ghp_your_token_here');
```

**Get Token:**
1. Go to: https://github.com/settings/tokens
2. Click "Generate new token (classic)"
3. Name: "SIT Connect Updates"
4. Expiration: No expiration (or your choice)
5. Scopes: Check `repo` (Full control of private repositories)
6. Click "Generate token"
7. Copy the token (starts with `ghp_`)

---

## Verify Setup

### Check if library is installed:
```bash
ls -la vendor/plugin-update-checker/
```

### Check git remote:
```bash
git remote -v
```

### Check tags:
```bash
git tag -l
```

### Check GitHub connection:
```bash
git ls-remote origin
```

---

## Test Update System

### Option 1: Force WordPress to Check
```bash
wp transient delete --all
```

### Option 2: Add to functions.php temporarily
```php
add_action('admin_init', function() {
    delete_site_transient('update_plugins');
});
```

### Option 3: Wait 12 Hours
WordPress will automatically check for updates every 12 hours.

---

## Troubleshooting

### If composer install fails:
```bash
# Install composer first
brew install composer

# Then try again
composer install
```

### If git push fails:
```bash
# Authenticate with GitHub
gh auth login

# Or use SSH instead of HTTPS
git remote set-url origin git@github.com:daxowapp/sit-connect.git
```

### If repository doesn't exist:
Create it first at: https://github.com/new
- Name: `sit-connect`
- Public or Private
- Don't initialize with README

---

## Success Indicators

✅ Composer installed dependencies
✅ Git repository initialized
✅ Remote added: https://github.com/daxowapp/sit-connect.git
✅ Code pushed to GitHub
✅ Tag v2.0.0 created and pushed
✅ GitHub release created
✅ WordPress shows "Update available" (after 12 hours or cache clear)

---

## Next Release (Example)

When you want to release v2.0.1:

```bash
# 1. Update version in sit-connect.php (line 7 and 70)
# 2. Commit changes
git add .
git commit -m "Release v2.0.1: Bug fixes and improvements"
git push origin main

# 3. Create tag
git tag -a v2.0.1 -m "Version 2.0.1"
git push origin v2.0.1

# 4. Create GitHub release (web interface)
```

Users will automatically see the update! 🎉

---

## Support

- **Documentation:** See `RELEASE_WORKFLOW.md`
- **Quick Start:** See `QUICK_START_GITHUB_UPDATES.md`
- **Full Setup:** See `GITHUB_AUTO_UPDATE_SETUP.md`
