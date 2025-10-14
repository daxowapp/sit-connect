<?php

namespace SIT\Search\Services;

/**
 * Zoho Field Validator
 * Validates and handles different Zoho CRM configurations
 * Ensures plugin works even if client has different fields
 */
class ZohoFieldValidator
{
    private static $instance = null;
    private $zoho;
    private $available_fields = [];
    private $missing_fields = [];
    private $field_cache_key = 'sit_zoho_available_fields';
    
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct()
    {
        $this->zoho = new Zoho();
    }
    
    /**
     * Check if a field exists in Zoho
     */
    public function field_exists($module, $field_name)
    {
        $fields = $this->get_available_fields($module);
        return isset($fields[$field_name]);
    }
    
    /**
     * Get all available fields for a module from Zoho
     */
    public function get_available_fields($module)
    {
        // Check cache first (24 hour cache)
        $cache_key = $this->field_cache_key . '_' . $module;
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Fetch from Zoho
        $fields = $this->fetch_fields_from_zoho($module);
        
        // Cache for 24 hours
        set_transient($cache_key, $fields, DAY_IN_SECONDS);
        
        return $fields;
    }
    
    /**
     * Fetch field metadata from Zoho CRM
     */
    private function fetch_fields_from_zoho($module)
    {
        try {
            $response = $this->zoho->request("settings/fields?module={$module}");
            
            if (!isset($response['fields'])) {
                return [];
            }
            
            $fields = [];
            foreach ($response['fields'] as $field) {
                $fields[$field['api_name']] = [
                    'label' => $field['field_label'] ?? $field['api_name'],
                    'type' => $field['data_type'] ?? 'text',
                    'required' => $field['required'] ?? false,
                ];
            }
            
            return $fields;
            
        } catch (\Exception $e) {
            error_log('Zoho Field Validator Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Safely get field value with fallback
     */
    public function get_field_value($data, $field_name, $default = '')
    {
        // Check if field exists in data
        if (!isset($data[$field_name])) {
            $this->log_missing_field($field_name);
            return $default;
        }
        
        $value = $data[$field_name];
        
        // Handle different data types
        if (is_array($value)) {
            // Lookup field (e.g., University, Country)
            return $value['name'] ?? $value['id'] ?? $default;
        }
        
        return $value;
    }
    
    /**
     * Validate required fields before sync
     */
    public function validate_required_fields($module, $data)
    {
        $required_fields = $this->get_required_fields($module);
        $missing = [];
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        return [
            'valid' => empty($missing),
            'missing_fields' => $missing
        ];
    }
    
    /**
     * Get list of required fields for a module
     */
    private function get_required_fields($module)
    {
        $fields = $this->get_available_fields($module);
        $required = [];
        
        foreach ($fields as $name => $info) {
            if ($info['required']) {
                $required[] = $name;
            }
        }
        
        return $required;
    }
    
    /**
     * Log missing field for admin notification
     */
    private function log_missing_field($field_name)
    {
        $missing = get_option('sit_zoho_missing_fields', []);
        
        if (!in_array($field_name, $missing)) {
            $missing[] = $field_name;
            update_option('sit_zoho_missing_fields', $missing);
        }
    }
    
    /**
     * Get all missing fields
     */
    public function get_missing_fields()
    {
        return get_option('sit_zoho_missing_fields', []);
    }
    
    /**
     * Clear missing fields log
     */
    public function clear_missing_fields()
    {
        delete_option('sit_zoho_missing_fields');
    }
    
    /**
     * Check Zoho configuration compatibility
     */
    public function check_compatibility()
    {
        $modules = ['Accounts', 'Products', 'Contacts', 'Leads'];
        $report = [];
        
        foreach ($modules as $module) {
            $fields = $this->get_available_fields($module);
            $report[$module] = [
                'total_fields' => count($fields),
                'fields' => array_keys($fields)
            ];
        }
        
        return $report;
    }
    
    /**
     * Get field mapping suggestions
     */
    public function suggest_field_mapping($module)
    {
        $available = $this->get_available_fields($module);
        $expected = $this->get_expected_fields($module);
        
        $mapping = [];
        $missing = [];
        
        foreach ($expected as $field) {
            if (isset($available[$field])) {
                $mapping[$field] = $field; // Direct match
            } else {
                // Try to find similar field
                $similar = $this->find_similar_field($field, array_keys($available));
                if ($similar) {
                    $mapping[$field] = $similar;
                } else {
                    $missing[] = $field;
                }
            }
        }
        
        return [
            'mapping' => $mapping,
            'missing' => $missing,
            'available' => array_keys($available)
        ];
    }
    
    /**
     * Get expected fields for each module
     */
    private function get_expected_fields($module)
    {
        $expected = [
            'Accounts' => [
                'Account_Name',
                'Description',
                'QS_Rank',
                'Number_Of_Students',
                'Year_Founded',
                'Sector',
                'Active_in_Search',
                // Note: uni_image and uni_logo are custom fields
                // They may not appear in field metadata but work in data sync
                // 'uni_image',
                // 'uni_logo'
            ],
            'Products' => [
                'Product_Name',
                'Description',
                'Official_Tuition',
                'Discounted_Tuition',
                'Study_Years',
                'University',
                'Country',
                'City',
                'Degrees',
                'Program_Languages',
                'Faculty',
                'Speciality'
            ]
        ];
        
        return $expected[$module] ?? [];
    }
    
    /**
     * Find similar field name
     */
    private function find_similar_field($needle, $haystack)
    {
        $needle = strtolower($needle);
        
        foreach ($haystack as $field) {
            $field_lower = strtolower($field);
            
            // Check if contains
            if (strpos($field_lower, $needle) !== false || strpos($needle, $field_lower) !== false) {
                return $field;
            }
            
            // Check similarity
            similar_text($needle, $field_lower, $percent);
            if ($percent > 70) {
                return $field;
            }
        }
        
        return null;
    }
    
    /**
     * Clear field cache
     */
    public function clear_cache()
    {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_{$this->field_cache_key}%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_{$this->field_cache_key}%'");
    }
}
