<?php

namespace SIT\Search\Services;

/**
 * WPML Integration Service
 * Handles multilingual support for SIT Connect
 */
class WPML
{
    private static $instance = null;
    
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Check if WPML is active
     */
    public static function is_active()
    {
        return defined('ICL_SITEPRESS_VERSION') && function_exists('icl_object_id');
    }
    
    /**
     * Get current language code
     */
    public static function get_current_language()
    {
        if (!self::is_active()) {
            return 'en';
        }
        
        return apply_filters('wpml_current_language', null);
    }
    
    /**
     * Get default language code
     */
    public static function get_default_language()
    {
        if (!self::is_active()) {
            return 'en';
        }
        
        return apply_filters('wpml_default_language', null);
    }
    
    /**
     * Get all active languages
     */
    public static function get_active_languages()
    {
        if (!self::is_active()) {
            return ['en' => ['code' => 'en', 'native_name' => 'English']];
        }
        
        return apply_filters('wpml_active_languages', null);
    }
    
    /**
     * Get translated post ID
     */
    public static function get_translated_id($post_id, $post_type, $language_code = null)
    {
        if (!self::is_active()) {
            return $post_id;
        }
        
        if ($language_code === null) {
            $language_code = self::get_current_language();
        }
        
        return apply_filters('wpml_object_id', $post_id, $post_type, true, $language_code);
    }
    
    /**
     * Get translated term ID
     */
    public static function get_translated_term_id($term_id, $taxonomy, $language_code = null)
    {
        if (!self::is_active()) {
            return $term_id;
        }
        
        if ($language_code === null) {
            $language_code = self::get_current_language();
        }
        
        return apply_filters('wpml_object_id', $term_id, $taxonomy, true, $language_code);
    }
    
    /**
     * Get post language
     */
    public static function get_post_language($post_id)
    {
        if (!self::is_active()) {
            return 'en';
        }
        
        return apply_filters('wpml_post_language_details', null, $post_id);
    }
    
    /**
     * Register post type for translation
     */
    public static function register_post_type($post_type)
    {
        if (!self::is_active()) {
            return;
        }
        
        do_action('wpml_register_single_type', $post_type);
    }
    
    /**
     * Register taxonomy for translation
     */
    public static function register_taxonomy($taxonomy)
    {
        if (!self::is_active()) {
            return;
        }
        
        do_action('wpml_register_single_taxonomy', $taxonomy);
    }
    
    /**
     * Register string for translation
     */
    public static function register_string($name, $value, $context = 'sit-connect')
    {
        if (!self::is_active() || !function_exists('icl_register_string')) {
            return;
        }
        
        icl_register_string($context, $name, $value);
    }
    
    /**
     * Translate string
     */
    public static function translate_string($name, $value, $context = 'sit-connect', $language_code = null)
    {
        if (!self::is_active() || !function_exists('icl_t')) {
            return $value;
        }
        
        if ($language_code) {
            return icl_t($context, $name, $value, false, false, $language_code);
        }
        
        return icl_t($context, $name, $value);
    }
    
    /**
     * Get language switcher
     */
    public static function get_language_switcher($args = [])
    {
        if (!self::is_active() || !function_exists('icl_get_languages')) {
            return '';
        }
        
        $languages = icl_get_languages('skip_missing=0');
        
        if (empty($languages)) {
            return '';
        }
        
        $output = '<div class="sit-language-switcher">';
        
        foreach ($languages as $lang) {
            $active_class = $lang['active'] ? 'active' : '';
            $output .= sprintf(
                '<a href="%s" class="sit-lang-link %s" data-lang="%s">%s</a>',
                esc_url($lang['url']),
                esc_attr($active_class),
                esc_attr($lang['code']),
                esc_html($lang['native_name'])
            );
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Modify WP_Query for current language
     */
    public static function filter_query_by_language($query)
    {
        if (!self::is_active() || !$query->is_main_query()) {
            return;
        }
        
        // WPML automatically filters queries by current language
        // This is just a hook point for custom filtering if needed
        do_action('sit_wpml_filter_query', $query);
    }
    
    /**
     * Get translated permalink
     */
    public static function get_translated_permalink($post_id, $language_code = null)
    {
        if (!self::is_active()) {
            return get_permalink($post_id);
        }
        
        $translated_id = self::get_translated_id($post_id, get_post_type($post_id), $language_code);
        return get_permalink($translated_id);
    }
    
    /**
     * Duplicate post to other languages
     */
    public static function duplicate_post($post_id, $target_languages = [])
    {
        if (!self::is_active()) {
            return [];
        }
        
        $duplicated = [];
        $post_type = get_post_type($post_id);
        
        if (empty($target_languages)) {
            $target_languages = array_keys(self::get_active_languages());
        }
        
        foreach ($target_languages as $lang_code) {
            if ($lang_code === self::get_post_language($post_id)) {
                continue;
            }
            
            $translated_id = self::get_translated_id($post_id, $post_type, $lang_code);
            
            if ($translated_id && $translated_id !== $post_id) {
                $duplicated[$lang_code] = $translated_id;
            }
        }
        
        return $duplicated;
    }
    
    /**
     * Set post language
     */
    public static function set_post_language($post_id, $language_code)
    {
        if (!self::is_active() || !function_exists('wpml_set_element_language_details')) {
            return false;
        }
        
        $post_type = get_post_type($post_id);
        
        $set_language_args = [
            'element_id'    => $post_id,
            'element_type'  => 'post_' . $post_type,
            'trid'          => false,
            'language_code' => $language_code,
        ];
        
        do_action('wpml_set_element_language_details', $set_language_args);
        
        return true;
    }
    
    /**
     * Connect translations
     */
    public static function connect_translations($original_id, $translated_id, $language_code)
    {
        if (!self::is_active()) {
            return false;
        }
        
        $post_type = get_post_type($original_id);
        $trid = apply_filters('wpml_element_trid', null, $original_id, 'post_' . $post_type);
        
        if (!$trid) {
            return false;
        }
        
        $set_language_args = [
            'element_id'    => $translated_id,
            'element_type'  => 'post_' . $post_type,
            'trid'          => $trid,
            'language_code' => $language_code,
        ];
        
        do_action('wpml_set_element_language_details', $set_language_args);
        
        return true;
    }
    
    /**
     * Get language flag URL
     */
    public static function get_flag_url($language_code)
    {
        if (!self::is_active()) {
            return '';
        }
        
        $languages = self::get_active_languages();
        
        if (isset($languages[$language_code]['country_flag_url'])) {
            return $languages[$language_code]['country_flag_url'];
        }
        
        return '';
    }
    
    /**
     * Translate custom field
     */
    public static function translate_custom_field($field_name, $post_id, $language_code = null)
    {
        if (!self::is_active()) {
            return get_post_meta($post_id, $field_name, true);
        }
        
        $translated_id = self::get_translated_id($post_id, get_post_type($post_id), $language_code);
        return get_post_meta($translated_id, $field_name, true);
    }
}
