<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Functions;
use SIT\Search\Services\Template;

class UniversityCountries
{
    public function __invoke()
    {
        // Get active country IDs
        $active_country_ids = \SIT\Search\Services\ActiveCountries::getActiveCountryIds();
        
        $country_args = array(
            'taxonomy' => 'sit-country',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => 'active_on_university',
                    'compare' => '=',
                    'value' => '1'
                )
            )
        );
        
        // Filter by active countries if enabled
        if (!empty($active_country_ids)) {
            $country_args['include'] = $active_country_ids;
        }
        
        $countries = get_terms($country_args);

        // Check for WP_Error
        if (is_wp_error($countries) || empty($countries)) {
            $countries = [];
        }

        $countries = array_map(function ($country) {
            return [
                'id' => $country->term_id,
                'name' => $country->name,
                'slug' => $country->slug,
                'flag' => Functions::getCountryFlag($country->name),
            ];
        }, $countries);

        ob_start();
        Template::render(
            'shortcodes/university-countries',
            [
                'countries' => $countries
            ]
        );
        return ob_get_clean();
    }
}