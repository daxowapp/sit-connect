<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\WPML;

/**
 * Register WPML Support
 * Registers post types and taxonomies for translation
 */
class RegisterWPML
{
    public static $hooks = ['init'];
    public static $priority = 20;
    public static $arguments = 0;
    
    public function __invoke()
    {
        // Only run if WPML is active
        if (!WPML::is_active()) {
            return;
        }
        
        // Register post types for translation
        $this->register_post_types();
        
        // Register taxonomies for translation
        $this->register_taxonomies();
        
        // Register strings for translation
        $this->register_strings();
        
        // Add language switcher support
        add_action('wp_footer', [$this, 'add_language_switcher_styles']);
    }
    
    /**
     * Register post types with WPML
     */
    private function register_post_types()
    {
        $post_types = [
            'sit-university',
            'sit-program',
            'sit-campus',
        ];
        
        foreach ($post_types as $post_type) {
            WPML::register_post_type($post_type);
        }
    }
    
    /**
     * Register taxonomies with WPML
     */
    private function register_taxonomies()
    {
        $taxonomies = [
            'sit-country',
            'sit-city',
            'sit-degree',
            'sit-language',
            'sit-faculty',
            'sit-speciality',
        ];
        
        foreach ($taxonomies as $taxonomy) {
            WPML::register_taxonomy($taxonomy);
        }
    }
    
    /**
     * Register common strings for translation
     */
    private function register_strings()
    {
        $strings = [
            // Search form
            'search_placeholder' => 'Search programs...',
            'search_button' => 'Search',
            'filter_by_country' => 'Filter by Country',
            'filter_by_city' => 'Filter by City',
            'filter_by_degree' => 'Filter by Degree',
            'filter_by_language' => 'Filter by Language',
            
            // Results
            'results_found' => 'Results Found',
            'no_results' => 'No results found',
            'load_more' => 'Load More',
            'showing_results' => 'Showing',
            'of_results' => 'of',
            
            // Program details
            'tuition_fee' => 'Tuition Fee',
            'duration' => 'Duration',
            'language' => 'Language',
            'degree' => 'Degree',
            'faculty' => 'Faculty',
            'apply_now' => 'Apply Now',
            'learn_more' => 'Learn More',
            
            // University details
            'qs_ranking' => 'QS Ranking',
            'number_of_students' => 'Number of Students',
            'year_founded' => 'Year Founded',
            'location' => 'Location',
            'programs_offered' => 'Programs Offered',
            
            // Filters
            'all_countries' => 'All Countries',
            'all_cities' => 'All Cities',
            'all_degrees' => 'All Degrees',
            'all_languages' => 'All Languages',
            'clear_filters' => 'Clear Filters',
            'apply_filters' => 'Apply Filters',
            
            // Sorting
            'sort_by' => 'Sort By',
            'sort_relevance' => 'Relevance',
            'sort_name_asc' => 'Name (A-Z)',
            'sort_name_desc' => 'Name (Z-A)',
            'sort_price_asc' => 'Price (Low to High)',
            'sort_price_desc' => 'Price (High to Low)',
            
            // Messages
            'loading' => 'Loading...',
            'error_occurred' => 'An error occurred',
            'try_again' => 'Try Again',
        ];
        
        foreach ($strings as $name => $value) {
            WPML::register_string($name, $value, 'sit-connect');
        }
    }
    
    /**
     * Add language switcher styles
     */
    public function add_language_switcher_styles()
    {
        ?>
        <style>
            .sit-language-switcher {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            
            .sit-lang-link {
                padding: 5px 10px;
                text-decoration: none;
                border: 1px solid #ddd;
                border-radius: 4px;
                transition: all 0.3s ease;
            }
            
            .sit-lang-link:hover {
                background: #f5f5f5;
            }
            
            .sit-lang-link.active {
                background: #2271b1;
                color: white;
                border-color: #2271b1;
            }
        </style>
        <?php
    }
}
