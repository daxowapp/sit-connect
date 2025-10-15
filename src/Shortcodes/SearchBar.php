<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\ActiveCountries;
use SIT\Search\Services\Template;

class SearchBar
{
    public function __invoke()
    {
        // Get active country IDs
        $active_country_ids = \SIT\Search\Services\ActiveCountries::getActiveCountryIds();
        
        $country_args = array(
            'taxonomy' => 'sit-country',
            'hide_empty' => false,
        );
        
        // Filter by active countries if enabled
        if (!empty($active_country_ids)) {
            $country_args['include'] = $active_country_ids;
        }
        
        $countries = get_terms($country_args);
        
        if (is_wp_error($countries)) {
            $countries = [];
        }

        $specialities = get_terms(
            array(
                'taxonomy' => 'sit-speciality',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'active_in_search',
                        'compare' => '=',
                        'value' => '1'
                    )
                )
            )
        );
        
        if (is_wp_error($specialities)) {
            $specialities = [];
        }

        $degrees = get_terms(
            array(
                'taxonomy' => 'sit-degree',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'active_in_search',
                        'compare' => '=',
                        'value' => '1'
                    )
                )
            )
        );
        
        if (is_wp_error($degrees)) {
            $degrees = [];
        }
        
        // Get cities if country is selected
        $cities = [];
        if (isset($_GET['country']) && $_GET['country'] != '0') {
            $country_id = intval($_GET['country']);
            
            // Get universities from this country
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
            
            // Filter universities by Active_in_Search
            $active_universities = [];
            if (!empty($all_universities)) {
                foreach ($all_universities as $uni_id) {
                    $active_in_search = get_field('Active_in_Search', $uni_id);
                    if ($active_in_search == '1' || $active_in_search === true) {
                        $active_universities[] = $uni_id;
                    }
                }
            }
            
            if (!empty($active_universities)) {
                $cities = get_terms([
                    'taxonomy' => 'sit-city',
                    'hide_empty' => false,
                    'object_ids' => $active_universities,
                    'orderby' => 'name',
                    'order' => 'ASC'
                ]);
                
                if (is_wp_error($cities)) {
                    $cities = [];
                }
            }
        }

        ob_start();
        Template::render(
            'shortcodes/search-bar',
            [
                'countries' => $countries,
                'specialities' => $specialities,
                'degrees' => $degrees,
                'cities' => $cities
            ]);
        return ob_get_clean();
    }
}