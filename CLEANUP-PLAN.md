# Plugin Cleanup Plan

## 🗑️ Files to DELETE (Development/Test Files)

### Test & Debug Files
- ❌ `test-colors.php` - Test file for color injection
- ❌ `sit-search.php` - OLD main plugin file (replaced by sit-connect.php)
- ❌ `create-payment-intent.php` - Stripe test file
- ❌ `license-server-example.php` - Example file, not needed

### Documentation to DELETE (Keep only essential)
- ❌ `COLOR-DEBUG.md` - Debug guide (not needed in production)
- ❌ `COLOR-FIX-SUMMARY.md` - Development notes
- ❌ `FINAL-COLOR-FIX.md` - Development notes
- ❌ `QUICK-TEST.md` - Testing guide (not needed in production)
- ❌ `TEST-LICENSE-KEYS.md` - Test keys (security risk!)
- ❌ `IMPLEMENTATION-SUMMARY.md` - Development notes

### Backup Files (if any exist)
- ❌ `*.bak` files
- ❌ `*.bak2` files
- ❌ `*.bak3` files

### IDE Files
- ❌ `.idea/` folder - PhpStorm/IntelliJ config

---

## ✅ Files to KEEP

### Essential Plugin Files
- ✅ `sit-connect.php` - **MAIN PLUGIN FILE**
- ✅ `README.txt` - WordPress.org readme
- ✅ `composer.json` - Dependencies
- ✅ `composer.lock` - Locked dependencies

### Essential Documentation
- ✅ `DEPLOYMENT-CHECKLIST.md` - For deployment
- ✅ `FREEMIUS-SETUP.md` - Freemius guide
- ✅ `LICENSE-ENFORCEMENT.md` - How licensing works
- ✅ `README-LICENSING.md` - Licensing options
- ✅ `READY-FOR-SALE.md` - Sales preparation
- ✅ `SETUP-GUIDE.md` - Setup instructions

### Essential Folders
- ✅ `src/` - Source code
- ✅ `assets/` - CSS, JS, images
- ✅ `templates/` - Template files
- ✅ `vendor/` - Composer dependencies
- ✅ `freemius/` - Freemius SDK

---

## 📦 Cleanup Commands

Run these commands to clean up:

```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search

# Delete test files
rm -f test-colors.php
rm -f sit-search.php
rm -f create-payment-intent.php
rm -f license-server-example.php

# Delete development documentation
rm -f COLOR-DEBUG.md
rm -f COLOR-FIX-SUMMARY.md
rm -f FINAL-COLOR-FIX.md
rm -f QUICK-TEST.md
rm -f TEST-LICENSE-KEYS.md
rm -f IMPLEMENTATION-SUMMARY.md

# Delete backup files
find . -name "*.bak*" -delete

# Delete IDE folder
rm -rf .idea

# Delete this cleanup plan after use
rm -f CLEANUP-PLAN.md

echo "✅ Cleanup complete!"
```

---

## 📊 Before & After

### Before Cleanup
```
sit-search/
├── sit-connect.php          ✅ Keep
├── sit-search.php           ❌ Delete (old)
├── test-colors.php          ❌ Delete
├── create-payment-intent.php ❌ Delete
├── license-server-example.php ❌ Delete
├── COLOR-DEBUG.md           ❌ Delete
├── COLOR-FIX-SUMMARY.md     ❌ Delete
├── FINAL-COLOR-FIX.md       ❌ Delete
├── QUICK-TEST.md            ❌ Delete
├── TEST-LICENSE-KEYS.md     ❌ Delete (security!)
├── IMPLEMENTATION-SUMMARY.md ❌ Delete
├── DEPLOYMENT-CHECKLIST.md  ✅ Keep
├── FREEMIUS-SETUP.md        ✅ Keep
├── LICENSE-ENFORCEMENT.md   ✅ Keep
├── README-LICENSING.md      ✅ Keep
├── READY-FOR-SALE.md        ✅ Keep
├── SETUP-GUIDE.md           ✅ Keep
├── README.txt               ✅ Keep
├── .idea/                   ❌ Delete
├── src/                     ✅ Keep
├── assets/                  ✅ Keep
├── templates/               ✅ Keep
├── vendor/                  ✅ Keep
└── freemius/                ✅ Keep
```

### After Cleanup
```
sit-search/
├── sit-connect.php          ← MAIN FILE
├── README.txt               ← WordPress readme
├── composer.json
├── composer.lock
├── DEPLOYMENT-CHECKLIST.md  ← Deployment guide
├── FREEMIUS-SETUP.md        ← Freemius guide
├── LICENSE-ENFORCEMENT.md   ← Licensing guide
├── README-LICENSING.md      ← License options
├── READY-FOR-SALE.md        ← Sales guide
├── SETUP-GUIDE.md           ← Setup guide
├── src/                     ← Source code
├── assets/                  ← CSS/JS/Images
├── templates/               ← Template files
├── vendor/                  ← Dependencies
└── freemius/                ← Freemius SDK
```

---

## ⚠️ Important Notes

### Before Deleting
1. **Backup everything** (just in case)
2. **Test the plugin** after cleanup
3. **Make sure sit-connect.php is the active main file**

### Security
- ❗ **DELETE TEST-LICENSE-KEYS.md** - Contains test keys (security risk)
- ❗ **DELETE sit-search.php** - Old file may cause conflicts

### For Production
When creating ZIP for sale:
- Keep only essential files
- Remove all `.md` documentation files (or keep only README.txt)
- Remove `.idea`, `.git`, etc.
- Remove backup files

---

## 🚀 Quick Cleanup (Safe)

If you want to be safe, just delete the obvious test files:

```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search

# Delete only test files (safe)
rm -f test-colors.php
rm -f sit-search.php
rm -f create-payment-intent.php
rm -f TEST-LICENSE-KEYS.md

# Delete backup files
find . -name "*.bak*" -delete

echo "✅ Safe cleanup complete!"
```

---

## 📝 Summary

**Files to Delete**: 15+ files
**Space Saved**: ~100KB
**Security Improved**: ✅ (removed test keys)
**Conflicts Removed**: ✅ (removed old main file)

**Ready to clean up?** Run the commands above!
