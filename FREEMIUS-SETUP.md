# Freemius SDK Setup Guide

## Current Status

✅ **Plugin is working** - Freemius is optional
✅ **All features functional** - Color customization, dashboard, etc.
⚠️ **Licensing disabled** - Will be enabled when you add Freemius

## When to Add Freemius

Add Freemius SDK when you're ready to:
- Sell the plugin commercially
- Enable license management
- Provide automatic updates
- Track analytics
- Accept payments

## How to Install Freemius SDK

### Step 1: Download SDK

**Option A: Direct Download**
1. Go to https://github.com/Freemius/wordpress-sdk
2. Click "Code" → "Download ZIP"
3. Extract the ZIP file

**Option B: Git Clone**
```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search
git clone https://github.com/Freemius/wordpress-sdk.git freemius
```

### Step 2: Place in Plugin

1. Create folder: `/Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search/freemius/`
2. Copy all files from the SDK into this folder
3. Verify `freemius/start.php` exists

**Expected structure:**
```
sit-search/
├── sit-connect.php
├── freemius/              ← Create this folder
│   ├── start.php         ← Must exist
│   ├── includes/
│   ├── templates/
│   └── ...
├── src/
├── assets/
└── ...
```

### Step 3: Refresh WordPress

1. Go to your WordPress admin
2. The Freemius notice will disappear
3. Freemius will be active automatically

### Step 4: Configure on Freemius Dashboard

1. Sign up at https://dashboard.freemius.com/
2. Verify your plugin details:
   - **ID**: 21157 (already in code)
   - **Slug**: sit-connect
   - **Public Key**: pk_889390a925a9663528fbb1bdcbb74

3. Set up pricing plans
4. Configure checkout
5. Test activation

## Quick Install Script

Run this to download and install Freemius automatically:

```bash
cd /Users/darwish/Desktop/websites/spain/wp-content/plugins/sit-search

# Download Freemius SDK
curl -L https://github.com/Freemius/wordpress-sdk/archive/refs/heads/master.zip -o freemius.zip

# Extract
unzip freemius.zip

# Move to correct location
mv wordpress-sdk-master freemius

# Clean up
rm freemius.zip

echo "✅ Freemius SDK installed!"
```

## Verification

After installation, verify:

1. **File exists**: `/freemius/start.php`
2. **No errors**: Check WordPress admin for errors
3. **Notice gone**: Freemius notice should disappear
4. **Menu appears**: Look for Freemius menu items (if enabled)

## Development vs Production

### Development (Current)
- ✅ Plugin works fully
- ✅ All features available
- ⚠️ No licensing
- ⚠️ No automatic updates
- ⚠️ No analytics

### Production (With Freemius)
- ✅ Plugin works fully
- ✅ All features available
- ✅ License management
- ✅ Automatic updates
- ✅ Analytics & insights
- ✅ Payment processing
- ✅ Customer management

## Testing Freemius

Once installed, test:

1. **Activation**: Plugin activates without errors
2. **Dashboard**: Freemius dashboard accessible
3. **Licensing**: Can activate test license
4. **Updates**: Update mechanism works
5. **Checkout**: Payment flow works

## Troubleshooting

### Error: "Failed to open stream"
- **Cause**: Freemius SDK not installed
- **Solution**: Follow Step 1-2 above

### Error: "Call to undefined function fs_dynamic_init"
- **Cause**: Freemius SDK incomplete
- **Solution**: Re-download complete SDK

### Freemius menu not showing
- **Normal**: Menu is disabled in config (line 47-49)
- **To enable**: Change `'account' => false` to `true`

### License tab not working
- **Check**: Freemius dashboard configured
- **Check**: Public key matches
- **Check**: Plugin ID matches

## For Customers

When you sell the plugin, customers will:
1. Purchase from your store
2. Receive license key via email
3. Install plugin
4. Activate license in Settings
5. Get automatic updates

## Support

- **Freemius Docs**: https://freemius.com/help/documentation/
- **SDK GitHub**: https://github.com/Freemius/wordpress-sdk
- **Dashboard**: https://dashboard.freemius.com/

## Summary

**Right Now:**
- Plugin works perfectly without Freemius
- You can develop and test freely
- No licensing restrictions

**Before Selling:**
- Install Freemius SDK (5 minutes)
- Configure pricing on dashboard
- Test license activation
- You're ready to sell!

---

**The plugin is ready to use NOW. Add Freemius when you're ready to sell!** ✅
