# Making All Text Translatable - Guide

## 🎯 Why This Matters

For WPML to work properly, **ALL hardcoded text** must use WordPress translation functions. This allows:
- ✅ Text to be translated via WPML String Translation
- ✅ Different text for each language
- ✅ Professional multilingual experience

---

## 📝 WordPress Translation Functions

### 1. `__()` - Return Translated String
```php
// ❌ BAD - Hardcoded
$text = 'Search programs';

// ✅ GOOD - Translatable
$text = __('Search programs', 'sit-connect');
```

### 2. `_e()` - Echo Translated String
```php
// ❌ BAD
echo 'No results found';

// ✅ GOOD
_e('No results found', 'sit-connect');
```

### 3. `esc_html__()` - Return Escaped & Translated
```php
// ❌ BAD
echo 'University not found';

// ✅ GOOD
echo esc_html__('University not found', 'sit-connect');
```

### 4. `esc_html_e()` - Echo Escaped & Translated
```php
// ❌ BAD
echo '<h1>Search Results</h1>';

// ✅ GOOD
echo '<h1>' . esc_html__('Search Results', 'sit-connect') . '</h1>';
```

### 5. `esc_attr__()` - For HTML Attributes
```php
// ❌ BAD
<input placeholder="Search...">

// ✅ GOOD
<input placeholder="<?php echo esc_attr__('Search...', 'sit-connect'); ?>">
```

### 6. `_n()` - Plural Forms
```php
// ❌ BAD
echo $count . ' programs';

// ✅ GOOD
echo sprintf(
    _n('%d program', '%d programs', $count, 'sit-connect'),
    $count
);
```

### 7. `_x()` - With Context
```php
// ❌ BAD
$text = 'Post';

// ✅ GOOD - Context helps translators
$text = _x('Post', 'verb - to post something', 'sit-connect');
$text = _x('Post', 'noun - a blog post', 'sit-connect');
```

---

## 🔍 Common Hardcoded Text to Fix

### In PHP Files

#### Messages
```php
// ❌ BAD
echo 'No results found';
echo 'Loading...';
echo 'Error occurred';

// ✅ GOOD
echo esc_html__('No results found', 'sit-connect');
echo esc_html__('Loading...', 'sit-connect');
echo esc_html__('Error occurred', 'sit-connect');
```

#### Labels
```php
// ❌ BAD
$label = 'Search';
$label = 'Filter';
$label = 'Sort by';

// ✅ GOOD
$label = __('Search', 'sit-connect');
$label = __('Filter', 'sit-connect');
$label = __('Sort by', 'sit-connect');
```

#### Placeholders
```php
// ❌ BAD
'Empty'
'N/A'
'None'

// ✅ GOOD
__('No description available', 'sit-connect')
__('Not available', 'sit-connect')
__('None', 'sit-connect')
```

#### Button Text
```php
// ❌ BAD
<button>Apply Now</button>
<button>Learn More</button>
<button>Submit</button>

// ✅ GOOD
<button><?php esc_html_e('Apply Now', 'sit-connect'); ?></button>
<button><?php esc_html_e('Learn More', 'sit-connect'); ?></button>
<button><?php esc_html_e('Submit', 'sit-connect'); ?></button>
```

### In JavaScript

```javascript
// ❌ BAD
alert('Error occurred');

// ✅ GOOD - Use wp_localize_script
wp_localize_script('my-script', 'sitConnect', [
    'errorMessage' => __('Error occurred', 'sit-connect'),
    'loadingText' => __('Loading...', 'sit-connect'),
]);

// Then in JS:
alert(sitConnect.errorMessage);
```

### In Templates

```php
<!-- ❌ BAD -->
<h2>Featured Universities</h2>
<p>No programs available</p>

<!-- ✅ GOOD -->
<h2><?php esc_html_e('Featured Universities', 'sit-connect'); ?></h2>
<p><?php esc_html_e('No programs available', 'sit-connect'); ?></p>
```

---

## 🛠️ How to Fix Existing Code

### Step 1: Find Hardcoded Text

Search for patterns like:
```bash
# Strings in quotes
echo 'some text'
echo "some text"

# Direct output
<h1>Some Text</h1>

# Array values
'placeholder' => 'Search here'
```

### Step 2: Replace with Translation Functions

```php
// Before
echo 'University not active in search';

// After
echo esc_html__('University not active in search', 'sit-connect');
```

### Step 3: Register Strings in WPML

The `RegisterWPML` action already registers common strings, but you can add more:

```php
// In RegisterWPML.php
WPML::register_string('custom_message', 'Your custom message', 'sit-connect');
```

---

## 📋 Checklist for Each File

### PHP Files
- [ ] All `echo` statements use translation functions
- [ ] All error messages are translatable
- [ ] All labels are translatable
- [ ] All button text is translatable
- [ ] All placeholders are translatable
- [ ] All array values with text are translatable

### Template Files
- [ ] All headings use `esc_html_e()` or `esc_html__()`
- [ ] All paragraphs use translation functions
- [ ] All form labels use translation functions
- [ ] All button text uses translation functions
- [ ] All placeholder text uses `esc_attr__()`

### JavaScript Files
- [ ] All strings are passed via `wp_localize_script`
- [ ] No hardcoded English text in JS
- [ ] All alert/confirm messages are translatable

---

## 🎯 Examples from Your Plugin

### Example 1: UniversityPrograms.php

```php
// ❌ BEFORE
if (empty($uni_id)) {
    echo 'in';
}

// ✅ AFTER
if (empty($uni_id)) {
    echo esc_html__('Invalid university ID', 'sit-connect');
}
```

### Example 2: Empty Descriptions

```php
// ❌ BEFORE
'description' => !empty($desc) ? $desc : 'Empty',

// ✅ AFTER
'description' => !empty($desc) ? $desc : __('No description available', 'sit-connect'),
```

### Example 3: Placeholder Images

```php
// ❌ BEFORE (URL is fine, but text could be translatable)
'https://placehold.co/714x340?text=University'

// ✅ BETTER (if you want translatable placeholder text)
'https://placehold.co/714x340?text=' . urlencode(__('University', 'sit-connect'))
```

### Example 4: Search Form

```php
// ❌ BEFORE
<input type="text" placeholder="Search programs...">

// ✅ AFTER
<input type="text" placeholder="<?php echo esc_attr__('Search programs...', 'sit-connect'); ?>">
```

### Example 5: Filter Labels

```php
// ❌ BEFORE
<label>Country:</label>

// ✅ AFTER
<label><?php esc_html_e('Country:', 'sit-connect'); ?></label>
```

---

## 🔧 Automated Tools

### 1. Find Hardcoded Strings

```bash
# Search for echo with quotes
grep -r "echo ['\"]" src/

# Search for hardcoded strings in arrays
grep -r "=> ['\"][^$]" src/
```

### 2. WordPress i18n Tools

```bash
# Install WP-CLI i18n command
wp package install wp-cli/i18n-command

# Generate POT file (translation template)
wp i18n make-pot . languages/sit-connect.pot

# This will show all translatable strings
```

### 3. Poedit

Use Poedit to:
- View all translatable strings
- Create translations
- Export .mo files for WordPress

---

## 📚 Text Domain

**Always use:** `'sit-connect'`

```php
// ✅ CORRECT
__('Text', 'sit-connect')

// ❌ WRONG
__('Text', 'sit-search')  // Old domain
__('Text', 'my-plugin')   // Wrong domain
__('Text')                // Missing domain
```

---

## 🌍 Translation Workflow

### 1. Developer (You)
```php
// Add translation functions
echo esc_html__('Search Results', 'sit-connect');
```

### 2. WPML String Translation
```
WPML → String Translation
Find: "Search Results"
Translate to Arabic: "نتائج البحث"
Translate to Turkish: "Arama Sonuçları"
```

### 3. User Sees
```
English site: "Search Results"
Arabic site: "نتائج البحث"
Turkish site: "Arama Sonuçları"
```

---

## ✅ Best Practices

### DO:
- ✅ Use translation functions for ALL user-facing text
- ✅ Use `esc_html__()` for output
- ✅ Use `esc_attr__()` for attributes
- ✅ Use descriptive context with `_x()`
- ✅ Keep text domain consistent: `'sit-connect'`
- ✅ Use `sprintf()` for dynamic text

### DON'T:
- ❌ Echo hardcoded strings
- ❌ Mix text domains
- ❌ Forget to escape output
- ❌ Concatenate translated strings
- ❌ Translate variable names or code

---

## 🎓 Advanced Examples

### Dynamic Text with Variables

```php
// ❌ BAD
echo "Found " . $count . " programs";

// ✅ GOOD
echo sprintf(
    __('Found %d programs', 'sit-connect'),
    $count
);
```

### Plural Forms

```php
// ❌ BAD
echo $count . ($count == 1 ? ' program' : ' programs');

// ✅ GOOD
echo sprintf(
    _n('%d program', '%d programs', $count, 'sit-connect'),
    $count
);
```

### With HTML

```php
// ❌ BAD
echo '<strong>Important:</strong> Read this';

// ✅ GOOD
echo sprintf(
    __('<strong>Important:</strong> %s', 'sit-connect'),
    __('Read this', 'sit-connect')
);
```

### Context-Specific

```php
// For "Post" as verb
$verb = _x('Post', 'verb', 'sit-connect');

// For "Post" as noun
$noun = _x('Post', 'noun', 'sit-connect');
```

---

## 📝 Quick Reference

| Function | Use Case | Example |
|----------|----------|---------|
| `__()` | Return string | `$text = __('Hello', 'sit-connect');` |
| `_e()` | Echo string | `_e('Hello', 'sit-connect');` |
| `esc_html__()` | Return escaped | `echo esc_html__('Hello', 'sit-connect');` |
| `esc_html_e()` | Echo escaped | `esc_html_e('Hello', 'sit-connect');` |
| `esc_attr__()` | For attributes | `<input value="<?php echo esc_attr__('Hello', 'sit-connect'); ?>">` |
| `_n()` | Plurals | `_n('1 item', '%d items', $count, 'sit-connect')` |
| `_x()` | With context | `_x('Post', 'verb', 'sit-connect')` |

---

## 🎉 Summary

**Every piece of user-facing text should be translatable!**

✅ Use WordPress translation functions
✅ Always include text domain: `'sit-connect'`
✅ Escape output for security
✅ Test in multiple languages
✅ Register strings in WPML

**This makes your plugin truly multilingual!** 🌍
