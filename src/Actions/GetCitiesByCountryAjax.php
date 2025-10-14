<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Hook;

class GetCitiesByCountryAjax extends Hook
{
    public static array $hooks = [
        'wp_ajax_get_cities_by_country',
        'wp_ajax_nopriv_get_cities_by_country'
    ];

    public static int $priority = 10;

    public function __invoke()
    {
        // Get country ID from request
        $country_id = isset($_POST['country_id']) ? intval($_POST['country_id']) : 0;
        
        if (empty($country_id) || $country_id == 0) {
            wp_send_json_success(['cities' => [], 'debug' => 'No country ID provided']);
            return;
        }
        
        // Get all universities from this country
        $all_universities = get_posts([
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'sit-country',
                    'field' => 'term_id',
                    'terms' => $country_id
                ]
            ],
            'fields' => 'ids'
        ]);
        
        if (empty($all_universities)) {
            wp_send_json_success(['cities' => [], 'debug' => 'No universities found for country ' . $country_id]);
            return;
        }
        
        // Filter universities by Active_in_Search
        $active_universities = [];
        foreach ($all_universities as $uni_id) {
            $active_in_search = get_field('Active_in_Search', $uni_id);
            if ($active_in_search == '1' || $active_in_search === true) {
                $active_universities[] = $uni_id;
            }
        }
        
        if (empty($active_universities)) {
            wp_send_json_success([
                'cities' => [], 
                'debug' => 'Found ' . count($all_universities) . ' universities but none are active'
            ]);
            return;
        }
        
        // Get ALL cities first to check if they exist
        $all_cities = get_terms([
            'taxonomy' => 'sit-city',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        // Get cities for these active universities using object_ids
        $cities = get_terms([
            'taxonomy' => 'sit-city',
            'hide_empty' => false,
            'object_ids' => $active_universities,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        // Alternative method: Get cities directly from university terms
        $city_ids = [];
        foreach ($active_universities as $uni_id) {
            $uni_cities = wp_get_post_terms($uni_id, 'sit-city', ['fields' => 'ids']);
            if (!is_wp_error($uni_cities) && !empty($uni_cities)) {
                $city_ids = array_merge($city_ids, $uni_cities);
            }
        }
        $city_ids = array_unique($city_ids);
        
        // If object_ids method failed, use the direct method
        if ((is_wp_error($cities) || empty($cities)) && !empty($city_ids)) {
            $cities = get_terms([
                'taxonomy' => 'sit-city',
                'hide_empty' => false,
                'include' => $city_ids,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);
        }
        
        if (is_wp_error($cities) || empty($cities)) {
            wp_send_json_success([
                'cities' => [], 
                'debug' => [
                    'active_universities_count' => count($active_universities),
                    'active_universities' => $active_universities,
                    'all_cities_count' => is_array($all_cities) ? count($all_cities) : 0,
                    'city_ids_found' => $city_ids,
                    'error' => is_wp_error($cities) ? $cities->get_error_message() : 'No cities found'
                ]
            ]);
            return;
        }
        
        // Format cities for response
        $formatted_cities = array_map(function($city) {
            return [
                'id' => $city->term_id,
                'name' => $city->name
            ];
        }, $cities);
        
        wp_send_json_success([
            'cities' => $formatted_cities,
            'debug' => [
                'method' => 'success',
                'active_universities_count' => count($active_universities),
                'cities_count' => count($formatted_cities)
            ]
        ]);
    }
}
