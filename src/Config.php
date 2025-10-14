<?php

namespace SIT\Search;

/**
 * Configuration helper for SIT Search Plugin
 * Handles environment-specific URLs and paths
 */
class Config
{
    /**
     * Get the main site URL (for cross-site links)
     */
    public static function getMainSiteUrl(): string
    {
        // Check if defined in wp-config.php
        if (defined('SIT_MAIN_SITE_URL')) {
            return SIT_MAIN_SITE_URL;
        }
        
        // Default to production
        return 'https://studyinturkiye.com';
    }

    /**
     * Get asset URL from uploads directory
     * 
     * @param string $path Path relative to uploads directory
     * @return string Full URL to asset
     */
    public static function getAssetUrl(string $path): string
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/' . ltrim($path, '/');
    }

    /**
     * Get plugin asset URL
     * 
     * @param string $path Path relative to plugin assets directory
     * @return string Full URL to plugin asset
     */
    public static function getPluginAssetUrl(string $path): string
    {
        return plugins_url('sit-search/assets/' . ltrim($path, '/'));
    }

    /**
     * Get apply URL with optional query parameters
     * 
     * @param array $params Query parameters
     * @return string Full apply URL
     */
    public static function getApplyUrl(array $params = []): string
    {
        $base_url = home_url('/apply/');
        
        if (!empty($params)) {
            return add_query_arg($params, $base_url);
        }
        
        return $base_url;
    }

    /**
     * Get university URL with optional query parameters
     * 
     * @param array $params Query parameters
     * @return string Full university URL
     */
    public static function getUniversityUrl(array $params = []): string
    {
        $base_url = home_url('/university/');
        
        if (!empty($params)) {
            return add_query_arg($params, $base_url);
        }
        
        return $base_url;
    }

    /**
     * Get home page URL
     * 
     * @return string Home URL
     */
    public static function getHomeUrl(): string
    {
        return home_url('/');
    }

    /**
     * Get results page URL
     * 
     * @param array $params Query parameters
     * @return string Full results URL
     */
    public static function getResultsUrl(array $params = []): string
    {
        $base_url = home_url('/results/');
        
        if (!empty($params)) {
            return add_query_arg($params, $base_url);
        }
        
        return $base_url;
    }

    /**
     * Check if we're in development environment
     */
    public static function isDevelopment(): bool
    {
        if (defined('SIT_SEARCH_ENV')) {
            return SIT_SEARCH_ENV === 'local' || SIT_SEARCH_ENV === 'development';
        }
        
        // Auto-detect localhost
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false;
    }

    /**
     * Get support email
     */
    public static function getSupportEmail(): string
    {
        if (defined('SIT_SUPPORT_EMAIL')) {
            return SIT_SUPPORT_EMAIL;
        }
        
        return 'support@studyinturkiye.com';
    }

    /**
     * Get support phone
     */
    public static function getSupportPhone(): string
    {
        if (defined('SIT_SUPPORT_PHONE')) {
            return SIT_SUPPORT_PHONE;
        }
        
        return '+90 545 306 1000';
    }

    /**
     * Get fallback/placeholder images
     */
    public static function getPlaceholderImage(string $type = 'university'): string
    {
        switch ($type) {
            case 'university':
                return 'https://placehold.co/714x340?text=University';
            case 'program':
                return 'https://placehold.co/400x300?text=Program';
            default:
                return 'https://placehold.co/400x300?text=Image';
        }
    }
}
