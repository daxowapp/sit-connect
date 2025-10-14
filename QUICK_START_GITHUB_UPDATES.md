# Quick Start: GitHub Auto-Updates

## 5-Minute Setup

### 1. Install Library
```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search
composer install
```

### 2. Create GitHub Repo
```bash
# On GitHub.com, create new repository: your-username/sit-connect
# Then:
git init
git remote add origin https://github.com/your-username/sit-connect.git
git add .
git commit -m "Initial commit"
git push -u origin main
```

### 3. Update Code
Edit `sit-connect.php` line 107:
```php
'https://github.com/your-username/sit-connect', // YOUR actual repo URL
```

### 4. Create First Release
```bash
# Update version in sit-connect.php to 2.0.1
git add .
git commit -m "Release v2.0.1"
git push origin main
git tag v2.0.1
git push origin v2.0.1

# On GitHub: Releases → Create new release → Select v2.0.1 tag → Publish
```

### 5. Test
- Go to WordPress admin → Plugins
- Should see "Update available" (may take up to 12 hours or clear cache)
- Click "Update Now"
- Done! ✅

## For Private Repos

Add to `wp-config.php`:
```php
define('SIT_CONNECT_GITHUB_TOKEN', 'ghp_your_token_here');
```

Get token from: GitHub → Settings → Developer settings → Personal access tokens

## That's It!

Users will now automatically receive updates when you create new GitHub releases.

**Full documentation:** See `RELEASE_WORKFLOW.md`
