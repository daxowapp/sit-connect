<?php

namespace SIT\Search\Services;

class ActiveCountries
{
    /**
     * Check if country filtering is enabled
     */
    public static function isEnabled(): bool
    {
        if (!function_exists('get_field')) {
            return false;
        }
        
        return (bool) get_field('enable_country_filter', 'option');
    }
    
    /**
     * Get active country term IDs
     */
    public static function getActiveCountryIds(): array
    {
        if (!function_exists('get_field')) {
            return [];
        }
        
        if (!self::isEnabled()) {
            return [];
        }
        
        $country_ids = get_field('active_countries', 'option');
        
        if (empty($country_ids) || !is_array($country_ids)) {
            return [];
        }
        
        return array_map('intval', $country_ids);
    }
    
    /**
     * Get active country names
     */
    public static function getActiveCountryNames(): array
    {
        $country_ids = self::getActiveCountryIds();
        
        if (empty($country_ids)) {
            return [];
        }
        
        $names = [];
        foreach ($country_ids as $term_id) {
            $term = get_term($term_id, 'sit-country');
            if ($term && !is_wp_error($term)) {
                $names[] = $term->name;
            }
        }
        
        return $names;
    }
    
    /**
     * Get tax query for active countries
     * Returns array to be used in WP_Query or get_posts
     */
    public static function getTaxQuery(): array
    {
        $country_ids = self::getActiveCountryIds();
        
        if (empty($country_ids)) {
            return [];
        }
        
        return [
            'taxonomy' => 'sit-country',
            'field'    => 'term_id',
            'terms'    => $country_ids,
            'operator' => 'IN',
        ];
    }
    
    /**
     * Apply active country filter to WP_Query args
     */
    public static function applyToQueryArgs(array $args): array
    {
        if (!self::isEnabled()) {
            return $args;
        }
        
        $tax_query = self::getTaxQuery();
        
        if (empty($tax_query)) {
            return $args;
        }
        
        // Initialize tax_query if it doesn't exist
        if (!isset($args['tax_query'])) {
            $args['tax_query'] = ['relation' => 'AND'];
        }
        
        // Add active country filter
        $args['tax_query'][] = $tax_query;
        
        return $args;
    }
    
    /**
     * Check if a specific country is active
     */
    public static function isCountryActive($country_name_or_id): bool
    {
        if (!self::isEnabled()) {
            return true; // If filtering is disabled, all countries are "active"
        }
        
        $active_ids = self::getActiveCountryIds();
        
        if (empty($active_ids)) {
            return true; // If no countries selected, show all
        }
        
        // If it's a term ID
        if (is_numeric($country_name_or_id)) {
            return in_array((int)$country_name_or_id, $active_ids);
        }
        
        // If it's a country name
        $active_names = self::getActiveCountryNames();
        return in_array($country_name_or_id, $active_names);
    }
}
