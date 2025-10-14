<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;

class TopUniversities
{
    public function __invoke()
    {
        // Query for featured universities that are active in search
        $args = array(
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1, // Get ALL featured universities
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'Featured_Univesity',
                        'value' => '1',
                        'compare' => '='
                    ),
                    array(
                        'key' => 'Featured_Univesity',
                        'value' => 1,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'Featured_Univesity',
                        'value' => true,
                        'compare' => '='
                    )
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'Active_in_Search',
                        'value' => '1',
                        'compare' => '='
                    ),
                    array(
                        'key' => 'Active_in_Search',
                        'value' => 1,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'Active_in_Search',
                        'value' => true,
                        'compare' => '='
                    )
                )
            ),
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        // Apply active countries filter
        $args = \SIT\Search\Services\ActiveCountries::applyToQueryArgs($args);

        $query = new \WP_Query($args);
        
        $universities = $query->get_posts();
        $universities = array_map(function ($university) {
            $terms = get_the_terms($university->ID, 'sit-country');
            $country_name = '';
            if (!is_wp_error($terms) && !empty($terms)) {
                $country_name = $terms[0]->name;
            }
            
            return [
                'uni_id' => $university->ID,
                'title' => $university->post_title,
                'country' => $country_name,
                'description' => get_post_meta($university->ID, 'Description', true),
                'ranking' => get_post_meta($university->ID, 'QS_Rank', true),
                'accommodation' => is_array(get_post_meta($university->ID, 'Accommodation', true)) ?
                    implode(', ', get_post_meta($university->ID, 'Accommodation', true)) :
                    get_post_meta($university->ID, 'Accommodation', true),
                'students' => get_post_meta($university->ID, 'Number_Of_Students', true),
                'year' => get_post_meta($university->ID, 'Year_Founded', true) ?
                    date('Y', strtotime(get_post_meta($university->ID, 'Year_Founded', true))) :
                    null,
                'image_url'=> !empty(get_post_meta($university->ID, 'uni_image', true))  ?
                    esc_url(get_post_meta($university->ID, 'uni_image', true))
                    :'https://placehold.co/714x340?text=University',
                'guid'=> get_permalink($university->ID),
            ];
        }, $universities);

        ob_start();
        Template::render('shortcodes/top-universities', ['universities' => $universities]);
        return ob_get_clean();
    }
}