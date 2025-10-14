# Test License Keys for SIT Connect

## 🔑 Available Test Licenses

Use these license keys to test the plugin without setting up a license server:

### License Key 1 (Works with any email)
```
DEMO-LICENSE-KEY-12345
```
- **Email:** Any valid email address
- **Use Case:** Quick testing with any email

### License Key 2 (Specific email)
```
DEV-TEST-2024-SITCONNECT
```
- **Email:** test@example.com
- **Use Case:** Testing with specific email validation

## 📝 How to Activate

1. Go to WordPress Admin
2. Navigate to **SIT Connect** menu
3. Click on **License** tab
4. Enter one of the license keys above
5. Enter the corresponding email (or any email for key 1)
6. Click **Activate License**
7. You should see: "License activated successfully! (Development Mode)"

## ✅ After Activation

Once activated, you can:
- Access the **Color Customization** tab
- Change all 4 colors (Primary, Primary Dark, Secondary, Accent)
- See live preview of changes
- Save colors and see them applied on frontend

## 🎨 Testing Color Customization

1. Make sure license is activated
2. Go to **SIT Connect** → **Color Customization**
3. Click on any color picker
4. Choose a new color
5. Watch the preview update in real-time
6. Click **Save Colors**
7. Visit your frontend pages to see the changes

## ⚠️ Important: Before Selling

**REMOVE THESE TEST KEYS** from the code before selling your plugin!

Edit this file:
```
/src/Actions/RegisterSettingsPage.php
```

Find the `activate_license()` method (around line 440) and remove or comment out:
```php
// Development/Test License Keys (bypass server check)
$test_licenses = [
    'DEV-TEST-2024-SITCONNECT' => 'test@example.com',
    'DEMO-LICENSE-KEY-12345' => '*', // Works with any email
];
```

## 🔧 Troubleshooting

### License Won't Activate
- Make sure you're using the exact license key (copy-paste)
- Check that email format is valid
- Clear browser cache and try again

### Colors Not Showing
- Verify license is activated first
- Clear WordPress cache (if using caching plugin)
- Clear browser cache
- Check browser console for errors

### Can't Find SIT Connect Menu
- Make sure the new plugin file is activated
- Deactivate old "Study In Türkiye Search" if still active
- Check that `sit-connect.php` exists in plugin folder

## 🚀 Next Steps

After testing:
1. Set up your real license server (see README-LICENSING.md)
2. Remove test license keys from code
3. Update license server URLs in:
   - `/src/Actions/RegisterSettingsPage.php`
   - `/src/Services/LicenseChecker.php`
4. Test with real license server
5. Package and sell!

---

**Current Status:** Development Mode - Test licenses active
**Production Ready:** No - Remove test keys first
