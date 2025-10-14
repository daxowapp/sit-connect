<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;
use SIT\Search\Services\ActiveCountries;

class TrendingStudyAreas
{
    public function __invoke($atts = [])
    {
        $atts = shortcode_atts(
            [
                'number' => 5,
            ],
            $atts
        );

        // Get active country IDs
        $active_country_ids = ActiveCountries::getActiveCountryIds();
        
        // Get programs from active countries only
        $program_args = [
            'post_type' => 'sit-program',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];
        
        // Filter by active countries if enabled
        if (!empty($active_country_ids)) {
            $program_args['tax_query'] = [
                [
                    'taxonomy' => 'sit-country',
                    'field' => 'term_id',
                    'terms' => $active_country_ids
                ]
            ];
        }
        
        $programs = get_posts($program_args);
        
        // Filter programs by Active_in_Search universities
        if (!empty($programs)) {
            $filtered_programs = [];
            foreach ($programs as $program_id) {
                $university_id = get_post_meta($program_id, 'zh_university', true);
                if ($university_id) {
                    $active_in_search = get_field('Active_in_Search', $university_id);
                    if ($active_in_search == '1' || $active_in_search === true) {
                        $filtered_programs[] = $program_id;
                    }
                }
            }
            $programs = $filtered_programs;
        }
        
        if (empty($programs)) {
            ob_start();
            Template::render('shortcodes/trending-study-areas', ['areas' => []]);
            return ob_get_clean();
        }
        
        // Get specialities for these programs
        $areas = get_terms([
            'taxonomy' => 'sit-speciality',
            'hide_empty' => false,
            'object_ids' => $programs,
            'orderby' => 'count',
            'order' => 'DESC',
            'number' => (int) $atts['number'],
        ]);

        // Check for WP_Error
        if (is_wp_error($areas) || empty($areas)) {
            $areas = [];
        }

        $areas = array_map(function ($area) use ($programs) {
            // Count programs with this speciality
            $count = 0;
            foreach ($programs as $program_id) {
                $program_specialities = wp_get_post_terms($program_id, 'sit-speciality', ['fields' => 'ids']);
                if (!is_wp_error($program_specialities) && in_array($area->term_id, $program_specialities)) {
                    $count++;
                }
            }
            
            return [
                'id' => $area->term_id,
                'name' => $area->name,
                'slug' => $area->slug,
                'count' => $count,
                'image_url'=>!empty(get_term_meta($area->term_id, 'spec_image', true))  ?
                    esc_url(get_term_meta($area->term_id, 'spec_image', true))
                    :'https://placehold.co/60x60',
            ];
        }, $areas);
        
        // Sort by count descending
        usort($areas, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        ob_start();
        Template::render('shortcodes/trending-study-areas', ['areas' => $areas]);
        return ob_get_clean();
    }
}