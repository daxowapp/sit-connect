# SIT Connect - Quick Setup Guide

## ✅ What's Been Done

Your plugin is now ready for commercial sale with the following features:

### 1. **Color Customization System**
- Users can customize 4 main colors:
  - Primary Color (main brand color)
  - Primary Dark Color (for gradients/hover states)
  - Secondary Color (accent color)
  - Accent Color (highlights)
- Real-time preview in admin
- Colors automatically apply to all frontend elements
- Reset to default option

### 2. **Plugin Rebranding**
- ✅ Renamed to **SIT Connect**
- ✅ Version 2.0.0
- ✅ Professional description
- ✅ Text domain: `sit-connect`

### 3. **License Management**
- License key activation/deactivation
- Email verification
- Domain binding
- Automatic verification every 7 days
- Admin notices for inactive licenses

## 🚀 Next Steps to Go Live

### Step 1: Activate the New Plugin File

The new main plugin file is `sit-connect.php`. You need to:

1. **Deactivate the old plugin** (if active):
   - Go to WordPress Admin → Plugins
   - Deactivate "Study In Türkiye Search"

2. **Delete or rename the old file**:
   ```bash
   cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search
   mv sit-search.php sit-search.php.old
   ```

3. **Activate the new plugin**:
   - Go to WordPress Admin → Plugins
   - Find "SIT Connect"
   - Click Activate

### Step 2: Set Up Your License Server

You have 3 options (detailed in README-LICENSING.md):

#### Option A: Quick Start (Recommended)
Use **Freemius** - it handles everything:
- Sign up at https://freemius.com/
- Create your plugin listing
- Get API credentials
- Update the plugin code with your Freemius credentials

#### Option B: Build Your Own
1. Create a simple PHP server with MySQL
2. Implement the 3 API endpoints (activate, verify, deactivate)
3. Update these files with your server URL:
   - `/src/Actions/RegisterSettingsPage.php` (line 331)
   - `/src/Services/LicenseChecker.php` (line 13)

#### Option C: Use WooCommerce + License Manager
- Install WooCommerce on your site
- Install License Manager plugin
- Configure and get API URL
- Update plugin files

### Step 3: Update License Server URLs

Edit these files and replace `https://your-license-server.com/api`:

**File 1:** `/src/Actions/RegisterSettingsPage.php`
```php
// Line ~331
$api_url = 'https://YOUR-ACTUAL-DOMAIN.com/api/activate';
```

**File 2:** `/src/Services/LicenseChecker.php`
```php
// Line ~13
private $license_server_url = 'https://YOUR-ACTUAL-DOMAIN.com/api';
```

### Step 4: Test Everything

1. **Test Color Customization:**
   - Go to **SIT Connect** menu in admin
   - Change colors and verify they apply
   - Check frontend to see changes

2. **Test License System:**
   - Try activating with a test license key
   - Verify error handling for invalid keys
   - Test deactivation

3. **Test on Fresh Install:**
   - Install on a clean WordPress site
   - Verify all features work
   - Check for any errors

### Step 5: Prepare for Sale

1. **Create Documentation:**
   - Installation guide
   - How to activate license
   - How to customize colors
   - Troubleshooting section

2. **Create Demo Site:**
   - Show all features
   - Different color schemes
   - Video walkthrough

3. **Package the Plugin:**
   ```bash
   cd /Users/darwish/Desktop/websites/spain/wp-content/plugins
   zip -r sit-connect.zip sit-search/ -x "*.git*" "*.idea*" "*node_modules*"
   ```

4. **Choose Sales Platform:**
   - **CodeCanyon**: Largest marketplace, built-in licensing
   - **Your Website**: 100% revenue, full control
   - **Freemius Store**: Integrated solution

### Step 6: Set Pricing

Suggested pricing tiers:
- **Single Site**: $49-$79
- **5 Sites**: $99-$149  
- **Unlimited Sites**: $199-$299

Or subscription model:
- **Monthly**: $9-$19/month
- **Yearly**: $79-$149/year

## 📁 File Structure

```
sit-search/
├── sit-connect.php          ← NEW main plugin file (use this)
├── sit-search.php           ← OLD file (delete or rename)
├── src/
│   ├── Actions/
│   │   ├── RegisterSettingsPage.php    ← NEW (settings page)
│   │   ├── InjectCustomColors.php      ← NEW (color injection)
│   │   └── ... (existing files)
│   ├── Services/
│   │   ├── LicenseChecker.php          ← NEW (license validation)
│   │   └── ... (existing files)
│   └── App.php                          ← MODIFIED (added new actions)
├── assets/
│   └── css/
│       └── sit-search.css               ← Uses CSS variables (already compatible)
├── README-LICENSING.md                  ← Detailed licensing guide
└── SETUP-GUIDE.md                       ← This file
```

## 🎨 How Users Will Customize Colors

1. Install and activate plugin
2. Go to **SIT Connect** in WordPress admin menu
3. Click **Color Customization** tab
4. Use color pickers to choose brand colors
5. See live preview
6. Click **Save Colors**
7. Colors automatically apply to entire plugin

## 🔐 How Users Will Activate License

1. Purchase license from your store
2. Receive license key via email
3. Go to **SIT Connect** → **License** tab
4. Enter license key and email
5. Click **Activate License**
6. Plugin validates with your server
7. License active ✅

## ⚠️ Important Security Notes

1. **Never commit license server credentials** to public repos
2. **Use HTTPS** for license server
3. **Validate all inputs** on license server
4. **Rate limit** API endpoints to prevent abuse
5. **Log activation attempts** for security monitoring

## 🐛 Troubleshooting

### Colors Not Applying
- Clear browser cache
- Check if custom CSS is being injected (view page source)
- Verify colors are saved in database

### License Activation Fails
- Check license server URL is correct
- Verify server is accessible
- Check server logs for errors
- Test API endpoints directly with Postman

### Plugin Not Showing in Admin
- Verify `sit-connect.php` exists
- Check for PHP errors in debug.log
- Ensure all dependencies are installed

## 📞 Support Resources

- **Detailed Licensing Guide**: See `README-LICENSING.md`
- **Code Comments**: All new files are well-documented
- **WordPress Codex**: https://codex.wordpress.org/
- **Freemius Docs**: https://freemius.com/help/

## 🎯 Marketing Tips

1. **Create Video Demo**: Show color customization in action
2. **Before/After Screenshots**: Different color schemes
3. **Feature List**: Highlight customization + licensing
4. **Customer Testimonials**: Get early user feedback
5. **SEO**: Optimize listing for "university plugin", "CRM integration"

## 📊 Metrics to Track

- Number of sales
- Activation rate (purchases vs activations)
- Support tickets
- Refund requests
- Feature requests

## 🔄 Updates & Maintenance

Plan for:
- Bug fixes
- WordPress compatibility updates
- New features based on feedback
- Security patches

Set up automatic updates for licensed users (see README-LICENSING.md)

---

## ✨ You're Ready!

Your plugin now has:
- ✅ Professional branding (SIT Connect)
- ✅ User-friendly color customization
- ✅ Robust licensing system
- ✅ Commercial-ready code

**Next Action**: Choose your license server solution and update the URLs in the code.

Good luck with your sales! 🚀
