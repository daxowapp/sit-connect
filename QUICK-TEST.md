# Quick Test Guide - SIT Connect

## ✅ Changes Made

1. **Unified Menu Structure**
   - Renamed "Study in Turkiye" to "SIT Connect"
   - Made Settings a submenu item (not separate top-level menu)
   - All items now under one "SIT Connect" menu

2. **Menu Structure Now:**
   ```
   SIT Connect (main menu)
   ├── Dashboard (default page)
   ├── Mapping
   ├── Zoho Sync
   └── Settings (Color Customization + License)
   ```

## 🧪 Testing Steps

### Step 1: Check Menu Structure
1. Go to WordPress Admin
2. Look at the left sidebar
3. You should see **ONE** "SIT Connect" menu (not two)
4. Click on it to expand
5. You should see: Dashboard, Mapping, Zoho Sync, Settings

### Step 2: Test License Activation
1. Click **SIT Connect** → **Settings**
2. Click the **License** tab
3. Use test license: `DEMO-LICENSE-KEY-12345`
4. Enter any email
5. Click **Activate License**
6. Should see: ✅ "License activated successfully! (Development Mode)"

### Step 3: Test Color Customization
1. Stay on Settings page
2. Click **Color Customization** tab
3. Change the Primary Color (click color picker)
4. Choose a bright color (e.g., blue #0000FF)
5. Watch the preview buttons change color
6. Click **Save Colors**
7. Should see: ✅ "Colors saved successfully!"

### Step 4: Verify Colors on Frontend
1. Open your website in a new tab
2. Right-click → View Page Source
3. Search for: `sit-connect-custom-colors`
4. You should see a `<style>` block with your custom colors
5. Example:
   ```css
   <style id="sit-connect-custom-colors">
       :root {
           --apply-primary: #0000FF;
           --apply-primary-dark: #8B1116;
           ...
       }
   </style>
   ```

### Step 5: See Colors Applied
1. Navigate to pages that use the plugin
2. Look for buttons, links, cards
3. They should now use your custom colors
4. Try changing colors again and refresh to see updates

## 🐛 Troubleshooting

### Problem: Still See Two Menus
**Solution:** Clear WordPress cache and refresh
- If using a caching plugin, clear it
- Hard refresh browser (Ctrl+Shift+R or Cmd+Shift+R)
- Deactivate and reactivate the plugin

### Problem: Colors Not Changing
**Solution:** 
1. Make sure license is activated first
2. Check browser console for errors (F12)
3. Clear all caches (WordPress + browser)
4. View page source to verify `<style id="sit-connect-custom-colors">` exists

### Problem: Can't Find Settings
**Solution:**
- Click on "SIT Connect" in the menu
- Look for "Settings" submenu item
- It should be at the bottom of the submenu list

## 🎨 Color Variables Reference

The plugin uses these CSS variables that you can customize:

| Variable | Default | Usage |
|----------|---------|-------|
| `--apply-primary` | #AA151B | Main buttons, primary actions |
| `--apply-primary-dark` | #8B1116 | Hover states, gradients |
| `--apply-secondary` | #F1BF00 | Secondary buttons, accents |
| `--apply-accent` | #C29900 | Highlights, special elements |

All these are customizable from the Settings page!

## ✨ Expected Result

After completing all steps:
- ✅ Single "SIT Connect" menu in admin
- ✅ License activated
- ✅ Colors customizable
- ✅ Colors applied to frontend
- ✅ Changes persist after page refresh

## 📝 Notes

- Test license keys are for development only
- Remove them before selling (see TEST-LICENSE-KEYS.md)
- Colors apply site-wide automatically
- No coding required for users!

---

**If everything works:** You're ready to package and sell! 🚀
**If issues persist:** Check the browser console (F12) for JavaScript errors
