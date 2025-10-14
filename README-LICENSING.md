# SIT Connect - Licensing & Protection Guide

## Overview
This document explains how to protect your SIT Connect plugin and implement a licensing system for commercial sales.

## Features Implemented

### 1. Color Customization System ✅
- Admin settings page with color picker
- Real-time preview of color changes
- Customizable primary, secondary, and accent colors
- CSS variables injection for frontend styling
- Reset to default colors option

### 2. Plugin Rebranding ✅
- Plugin renamed from "Study In Türkiye Search" to "SIT Connect"
- Text domain updated to `sit-connect`
- Version bumped to 2.0.0
- Professional description for commercial use

### 3. License Management System ✅
- License key activation/deactivation
- Email verification
- Domain-based license validation
- Admin notices for inactive licenses
- License status checking (every 7 days)

## How to Use the Plugin

### For Plugin Users (Buyers)

1. **Install the Plugin**
   - Upload `sit-search` folder to `/wp-content/plugins/`
   - Activate through WordPress admin

2. **Activate License**
   - Go to **SIT Connect** menu in WordPress admin
   - Click on **License** tab
   - Enter your license key and email address
   - Click **Activate License**

3. **Customize Colors**
   - Go to **SIT Connect** menu
   - Click on **Color Customization** tab
   - Choose your brand colors using color pickers
   - Preview changes in real-time
   - Click **Save Colors**

### For Plugin Seller (You)

## Licensing Implementation Options

You have several options to implement licensing protection:

### Option 1: Simple License Server (Recommended for Start)

Create a simple PHP-based license server with the following endpoints:

#### A. License Server Setup

1. **Create a separate website/domain** for license management (e.g., `license.yoursite.com`)

2. **Database Schema:**
```sql
CREATE TABLE licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) NOT NULL,
    domain VARCHAR(255),
    status ENUM('active', 'inactive', 'expired') DEFAULT 'inactive',
    activations_count INT DEFAULT 0,
    max_activations INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activated_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    INDEX(license_key),
    INDEX(email)
);
```

3. **API Endpoints:**

**Activate License** (`POST /api/activate`)
```php
<?php
// activate.php
header('Content-Type: application/json');

$license_key = $_POST['license_key'] ?? '';
$email = $_POST['email'] ?? '';
$domain = $_POST['domain'] ?? '';

// Validate license key and email
$license = $db->query("SELECT * FROM licenses WHERE license_key = ? AND email = ?", [$license_key, $email]);

if ($license && $license['status'] === 'active') {
    if ($license['activations_count'] < $license['max_activations']) {
        // Update domain and increment activations
        $db->query("UPDATE licenses SET domain = ?, activations_count = activations_count + 1, activated_at = NOW() WHERE id = ?", [$domain, $license['id']]);
        
        echo json_encode(['success' => true, 'message' => 'License activated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Maximum activations reached']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid license key or email']);
}
```

**Verify License** (`POST /api/verify`)
```php
<?php
// verify.php
header('Content-Type: application/json');

$license_key = $_POST['license_key'] ?? '';
$domain = $_POST['domain'] ?? '';

$license = $db->query("SELECT * FROM licenses WHERE license_key = ? AND domain = ?", [$license_key, $domain]);

if ($license && $license['status'] === 'active') {
    // Check expiration if applicable
    if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
        echo json_encode(['valid' => false, 'message' => 'License expired']);
    } else {
        echo json_encode(['valid' => true, 'message' => 'License is valid']);
    }
} else {
    echo json_encode(['valid' => false, 'message' => 'Invalid license']);
}
```

**Deactivate License** (`POST /api/deactivate`)
```php
<?php
// deactivate.php
header('Content-Type: application/json');

$license_key = $_POST['license_key'] ?? '';
$domain = $_POST['domain'] ?? '';

$result = $db->query("UPDATE licenses SET domain = NULL, activations_count = activations_count - 1 WHERE license_key = ? AND domain = ?", [$license_key, $domain]);

echo json_encode(['success' => true, 'message' => 'License deactivated']);
```

4. **Update the plugin files:**

Edit `/src/Actions/RegisterSettingsPage.php` and `/src/Services/LicenseChecker.php`:
- Replace `https://your-license-server.com/api` with your actual license server URL

### Option 2: Use Existing License Management Services

#### A. Freemius (Recommended - Most Popular)
- Website: https://freemius.com/
- Features: License management, payments, analytics, automatic updates
- Pricing: Free tier available, paid plans for more features
- Integration: SDK available for WordPress plugins

#### B. Easy Digital Downloads (EDD) with Software Licensing
- Website: https://easydigitaldownloads.com/
- Features: Complete e-commerce + license management
- Pricing: One-time purchase
- Integration: API available

#### C. WooCommerce with License Manager
- Plugin: https://www.licensemanager.at/
- Features: Integrates with WooCommerce
- Pricing: One-time purchase
- Integration: REST API

### Option 3: Advanced Custom Solution

For maximum control, implement:

1. **Encrypted License Keys**
   - Use RSA or similar encryption
   - Include domain, expiration, and features in encrypted payload

2. **Hardware Fingerprinting**
   - Bind license to server characteristics
   - Prevent easy license sharing

3. **Obfuscation**
   - Use PHP obfuscators like ionCube or Zend Guard
   - Protect your code from reverse engineering

4. **Remote Kill Switch**
   - Ability to remotely deactivate licenses
   - Useful for chargebacks or violations

## Selling Your Plugin

### Recommended Marketplaces

1. **CodeCanyon (Envato Market)**
   - URL: https://codecanyon.net/
   - Largest WordPress plugin marketplace
   - Built-in licensing system
   - 50-70% revenue share

2. **Your Own Website**
   - Use Easy Digital Downloads or WooCommerce
   - 100% revenue
   - Full control

3. **Freemius Store**
   - Built-in marketplace
   - Handles licensing automatically

### Pricing Strategy

Consider these pricing models:

1. **One-Time Purchase**
   - Single site: $49-$79
   - 5 sites: $99-$149
   - Unlimited: $199-$299

2. **Subscription**
   - Monthly: $9-$19/month
   - Yearly: $79-$149/year
   - Includes updates and support

3. **Freemium**
   - Free basic version
   - Premium features: $49-$99

## Security Best Practices

### 1. Code Obfuscation
```bash
# Use ionCube or similar
ioncube_encoder sit-search/
```

### 2. License Validation Frequency
- Check on plugin activation
- Check every 7 days (already implemented)
- Check on critical operations

### 3. Graceful Degradation
Instead of completely blocking functionality:
- Show admin notices
- Limit features
- Add watermarks
- Reduce functionality after grace period

### 4. Update Mechanism
Implement automatic updates for licensed users:
```php
// Add to App.php constructor
if (LicenseChecker::getInstance()->isLicenseActive()) {
    add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_updates']);
}
```

## Testing Your License System

### Test Scenarios

1. **Valid License**
   - Activate with correct key and email
   - Verify all features work
   - Check color customization

2. **Invalid License**
   - Try activating with wrong key
   - Verify error message appears
   - Check that notice shows in admin

3. **Expired License**
   - Set expiration date in past
   - Verify license becomes inactive
   - Check grace period behavior

4. **Maximum Activations**
   - Activate on multiple domains
   - Verify limit enforcement

5. **License Verification**
   - Wait 7 days or manually trigger
   - Verify remote check works
   - Test with server down (should maintain current status)

## Support & Documentation

### For Your Customers

Create documentation covering:
1. Installation steps
2. License activation process
3. Color customization guide
4. Troubleshooting common issues
5. Contact support information

### Support Channels

Consider offering:
- Email support
- Documentation site
- Video tutorials
- Community forum

## Legal Considerations

1. **License Agreement**
   - Create clear terms of use
   - Define allowed usage
   - Specify refund policy

2. **Privacy Policy**
   - Explain what data you collect (domain, email)
   - How you use it
   - GDPR compliance if selling in EU

3. **Refund Policy**
   - 30-day money-back guarantee (recommended)
   - Clear conditions

## Next Steps

1. **Set up license server** (choose option above)
2. **Update plugin files** with your license server URL
3. **Test thoroughly** with all scenarios
4. **Create documentation** for customers
5. **Set up sales platform** (CodeCanyon, own site, etc.)
6. **Market your plugin**
   - Create demo site
   - Make promotional video
   - Write blog posts
   - Social media marketing

## Files Modified/Created

### New Files:
- `/src/Actions/RegisterSettingsPage.php` - Admin settings page
- `/src/Actions/InjectCustomColors.php` - Custom color injection
- `/src/Services/LicenseChecker.php` - License validation
- `/sit-connect.php` - New main plugin file (replaces sit-search.php)
- `/README-LICENSING.md` - This file

### Modified Files:
- `/src/App.php` - Added new actions and license checker

## Important Notes

⚠️ **Before Going Live:**
1. Remove or secure the old `sit-search.php` file
2. Update license server URL in code
3. Test license activation thoroughly
4. Set up automatic updates mechanism
5. Create customer documentation
6. Prepare support system

## Contact & Support

For questions about this implementation:
- Review the code comments
- Test in staging environment first
- Consider hiring a security expert for code review

---

**Good luck with your plugin sales! 🚀**
