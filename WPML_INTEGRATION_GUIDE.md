# WPML Integration Guide for SIT Connect

## ✅ WPML Support Implemented

SIT Connect now fully supports WPML (WordPress Multilingual Plugin) for creating multilingual university and program websites!

---

## 🎯 What's Included

### 1. **WPML Service** (`src/Services/WPML.php`)
Complete WPML integration service with:
- Language detection
- Post/term translation
- String translation
- Language switcher
- Translation management

### 2. **Auto-Registration** (`src/Actions/RegisterWPML.php`)
Automatically registers:
- Post types (universities, programs, campuses)
- Taxonomies (countries, cities, degrees, languages, faculties, specialities)
- Common strings for translation

### 3. **Shortcode Support**
All shortcodes now support WPML:
- Automatic translation of IDs
- Language-specific content
- Translated permalinks

---

## 📋 Setup Instructions

### Step 1: Install WPML

1. Purchase and download WPML from https://wpml.org
2. Install these WPML plugins:
   - **WPML Multilingual CMS** (required)
   - **WPML String Translation** (recommended)
   - **WPML Translation Management** (recommended)

### Step 2: Configure WPML

1. Go to **WPML → Languages**
2. Select your languages (e.g., English, Arabic, Turkish, Spanish)
3. Set default language
4. Configure language switcher options

### Step 3: Activate SIT Connect

The plugin will automatically:
- ✅ Detect WPML
- ✅ Register post types for translation
- ✅ Register taxonomies for translation
- ✅ Register strings for translation

---

## 🌍 Translating Content

### Translating Universities

1. **Go to:** Posts → Universities
2. **Find university** you want to translate
3. **Click the "+" icon** in the language column
4. **Translate:**
   - University name
   - Description
   - Custom fields (if needed)
5. **Save translation**

### Translating Programs

1. **Go to:** Posts → Programs
2. **Find program** you want to translate
3. **Click the "+" icon** in the language column
4. **Translate:**
   - Program name
   - Description
   - Custom fields
5. **Save translation**

### Translating Taxonomies

1. **Go to:** WPML → Taxonomy Translation
2. **Select taxonomy** (Countries, Cities, Degrees, etc.)
3. **Translate terms**
4. **Save**

### Translating Strings

1. **Go to:** WPML → String Translation
2. **Search for:** "sit-connect"
3. **Translate strings:**
   - Search placeholder
   - Button labels
   - Filter labels
   - Messages
4. **Save translations**

---

## 🔧 How It Works

### Automatic Translation Detection

```php
// When user visits Arabic version
// URL: example.com/ar/universities/

// Plugin automatically:
1. Detects current language (ar)
2. Loads Arabic translations
3. Shows Arabic content
4. Filters queries by language
```

### Post Translation

```php
// Original university ID: 123 (English)
// Arabic translation ID: 456

// When viewing in Arabic:
$uni_id = 123;
$translated_id = WPML::get_translated_id($uni_id, 'sit-university');
// Returns: 456 (Arabic version)
```

### String Translation

```php
// In template:
echo WPML::translate_string('search_placeholder', 'Search programs...');

// English: "Search programs..."
// Arabic: "ابحث عن البرامج..."
// Turkish: "Programları ara..."
```

---

## 📝 Translation Workflow

### Option 1: Manual Translation

1. Create content in default language
2. Click "+" to add translation
3. Manually translate each field
4. Save

**Best for:** Small sites, custom content

### Option 2: Automatic Translation

1. Configure WPML Translation Management
2. Send content to translation service
3. Receive translations
4. Review and publish

**Best for:** Large sites, many languages

### Option 3: Duplicate & Translate

1. Create content in default language
2. Use WPML duplicate feature
3. Edit duplicated content
4. Translate as needed

**Best for:** Similar content across languages

---

## 🎨 Language Switcher

### Built-in Switcher

Use the WPML service to add a language switcher:

```php
// In your theme template:
echo \SIT\Search\Services\WPML::get_language_switcher();
```

### WPML Widget

Or use WPML's built-in widget:
1. Go to **Appearance → Widgets**
2. Add **WPML Language Switcher** widget
3. Configure display options

### Custom Switcher

Create your own:

```php
$languages = \SIT\Search\Services\WPML::get_active_languages();

foreach ($languages as $lang) {
    echo '<a href="' . $lang['url'] . '">';
    echo $lang['native_name'];
    echo '</a>';
}
```

---

## 🔍 Search & Filters

### Multilingual Search

Search automatically works in current language:

```
English site: Searches English content
Arabic site: Searches Arabic content
Turkish site: Searches Turkish content
```

### Multilingual Filters

Filters show translated terms:

```
Countries filter:
- English: Turkey, Spain, Germany
- Arabic: تركيا، إسبانيا، ألمانيا
- Turkish: Türkiye, İspanya, Almanya
```

---

## 📊 Syncing from Zoho with WPML

### Recommended Workflow:

1. **Sync to default language first**
   ```
   SIT Connect → Sync
   Syncs universities/programs in English
   ```

2. **Translate synced content**
   ```
   WPML → Translation Management
   Translate universities/programs to other languages
   ```

3. **Future syncs update default language**
   ```
   Translations remain intact
   Only default language gets updated from Zoho
   ```

### Important Notes:

- ⚠️ Zoho sync creates/updates **default language only**
- ✅ Translations are **not overwritten** by sync
- ✅ You control when to update translations
- ✅ Custom translations are preserved

---

## 🌐 URL Structure

### Directory Structure (Recommended)

```
example.com/en/universities/harvard/
example.com/ar/universities/harvard/
example.com/tr/universities/harvard/
```

### Domain Structure

```
example.com/universities/harvard/      (English)
example.ar/universities/harvard/       (Arabic)
example.tr/universities/harvard/       (Turkish)
```

### Subdomain Structure

```
en.example.com/universities/harvard/
ar.example.com/universities/harvard/
tr.example.com/universities/harvard/
```

Configure in: **WPML → Languages → Language URL format**

---

## 🎯 Best Practices

### 1. **Set Default Language First**
Choose your primary language before adding content.

### 2. **Translate Core Content**
Prioritize translating:
- Main universities
- Popular programs
- Important pages

### 3. **Use String Translation**
Translate all interface strings for better UX.

### 4. **Test Each Language**
View site in each language to ensure:
- Content displays correctly
- Filters work
- Search functions
- Links are correct

### 5. **SEO for Each Language**
Configure WPML SEO settings:
- Translated meta titles
- Translated meta descriptions
- Hreflang tags

---

## 🔧 Troubleshooting

### Issue: Content Not Translating

**Solution:**
1. Check WPML is active
2. Verify post type is registered for translation
3. Go to WPML → Settings → Post Types
4. Enable translation for sit-university, sit-program

### Issue: Filters Showing Wrong Language

**Solution:**
1. Go to WPML → Taxonomy Translation
2. Translate taxonomy terms
3. Clear cache

### Issue: Search Returns All Languages

**Solution:**
1. WPML automatically filters by language
2. Check WPML → Settings → Queries
3. Ensure "Adjust IDs for multilingual functionality" is enabled

### Issue: Language Switcher Not Working

**Solution:**
1. Check WPML is properly configured
2. Verify translations exist
3. Clear WordPress cache
4. Clear browser cache

---

## 📚 WPML Functions Available

### Check if WPML is Active

```php
if (\SIT\Search\Services\WPML::is_active()) {
    // WPML is active
}
```

### Get Current Language

```php
$lang = \SIT\Search\Services\WPML::get_current_language();
// Returns: 'en', 'ar', 'tr', etc.
```

### Get Translated Post ID

```php
$translated_id = \SIT\Search\Services\WPML::get_translated_id(
    $post_id, 
    'sit-university', 
    'ar'
);
```

### Get Translated Term ID

```php
$translated_term = \SIT\Search\Services\WPML::get_translated_term_id(
    $term_id, 
    'sit-country', 
    'ar'
);
```

### Translate String

```php
$translated = \SIT\Search\Services\WPML::translate_string(
    'search_button',
    'Search',
    'sit-connect',
    'ar'
);
```

### Get All Languages

```php
$languages = \SIT\Search\Services\WPML::get_active_languages();
// Returns array of language data
```

---

## 🎓 Example: Multilingual University Page

```php
// Get university ID from URL
$uni_id = $_GET['uni-id'];

// Get translated version for current language
$translated_uni_id = \SIT\Search\Services\WPML::get_translated_id(
    $uni_id, 
    'sit-university'
);

// Get university data in current language
$university_name = get_the_title($translated_uni_id);
$description = get_field('Description', $translated_uni_id);

// Get translated country
$country_terms = get_the_terms($translated_uni_id, 'sit-country');
$country_name = $country_terms[0]->name; // Already translated by WPML

// Display
echo '<h1>' . $university_name . '</h1>';
echo '<p>' . $description . '</p>';
echo '<p>' . __('Country', 'sit-connect') . ': ' . $country_name . '</p>';
```

---

## 🌟 Features

### ✅ Fully Translated Interface
- Search forms
- Filters
- Buttons
- Messages
- Labels

### ✅ Multilingual Content
- Universities
- Programs
- Campuses
- Taxonomies

### ✅ Language-Specific URLs
- SEO-friendly
- Proper hreflang tags
- Language-specific sitemaps

### ✅ Automatic Query Filtering
- Shows only current language content
- No mixed-language results
- Clean user experience

### ✅ Translation Management
- Easy workflow
- Professional translation support
- Translation memory

---

## 📞 Support

### WPML Documentation
https://wpml.org/documentation/

### SIT Connect + WPML
- Check `src/Services/WPML.php` for available methods
- Check `src/Actions/RegisterWPML.php` for registered items
- All shortcodes support WPML automatically

---

## ✨ Summary

**SIT Connect is now fully multilingual!**

✅ Automatic WPML detection
✅ Post types registered for translation
✅ Taxonomies registered for translation
✅ Strings registered for translation
✅ Shortcodes support translations
✅ Language switcher included
✅ SEO-friendly URLs
✅ Professional translation workflow

**Create your multilingual university website today!** 🌍🎓
