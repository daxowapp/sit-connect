<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class SingleUniversity
{
    public function __invoke()
    {
        $current_post_id = get_the_ID();
        $current_uni_id = get_post_meta(get_the_ID(), 'zh_university', true);
        $university = get_post($current_uni_id);
        $current_post_title = get_the_title($current_post_id);
        
        // Check if the university is active in search
        $active_in_search = get_field('Active_in_Search', $current_post_id);
        if ($active_in_search != '1' && $active_in_search !== true) {
            return '<div class="university-inactive">This university is not currently active in search.</div>';
        }

        $programs=[
            'unic_id'=>$current_post_id,
            'title'=>$current_post_title ,
            'pro_country' => ($country_terms = get_the_terms($current_post_id, 'sit-country')) && !is_wp_error($country_terms) && !empty($country_terms) ? $country_terms[0]->name : '',
            'city' => ($city_terms = get_the_terms($current_post_id, 'sit-city')) && !is_wp_error($city_terms) && !empty($city_terms) ? $city_terms[0]->name : '',
            'description' => get_post_meta($current_post_id, 'Description', true),
            'uni_description' => get_post_meta($current_post_id, 'Description', true),
            'ranking' => get_post_meta($current_post_id, 'QS_Rank', true),
            'Tuition_Currency' => get_post_meta($current_post_id, 'Tuition_Currency', true),
            'total_students' => get_post_meta($current_post_id, 'Number_Of_Students', true),
            'University_brochure' => get_post_meta($current_post_id, 'University_brochure', true),
            'image_url'=>!empty(get_post_meta($current_post_id, 'uni_image', true))  ?
                esc_url(get_post_meta($current_post_id, 'uni_image', true))
                :'https://placehold.co/714x340?text=University',
            'Year_Founded'=>get_post_meta($current_post_id, 'Year_Founded', true),
        ];

        $other_args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1, // Get all posts
            'post_status'    => 'publish',
            'meta_key'       => 'zh_university',
            'meta_value'     => $current_post_id,
        );
        $uni_query = new \WP_Query($other_args);
        $universities = $uni_query->get_posts();

        $universities = array_map(function ($university) {
            $oth_uniid = get_post_meta($university->ID, 'zh_university', true);
            return [
                'uni_id' => $university->ID,
                'title' => $university->post_title,
                'guid' => get_permalink($university->ID),
                'fee' => get_post_meta($university->ID, 'Official_Tuition', true),
                'discounted_fee' => get_post_meta($university->ID, 'Discounted_Tuition', true),
                'Advanced_Discount' => get_post_meta($university->ID, 'Advanced_Discount', true),
                'Tuition_Currency' => get_post_meta($university->ID, 'Tuition_Currency', true),
                'level' => ($degree_terms = get_the_terms($university->ID, 'sit-degree')) && !is_wp_error($degree_terms) && !empty($degree_terms) ? $degree_terms[0]->name : '',
                'language' => ($lang_terms = get_the_terms($university->ID, 'sit-language')) && !is_wp_error($lang_terms) && !empty($lang_terms) ? $lang_terms[0]->name : '',
                'country' => ($prog_country_terms = get_the_terms($university->ID, 'sit-country')) && !is_wp_error($prog_country_terms) && !empty($prog_country_terms) ? $prog_country_terms[0]->name : '',
                'city' => ($prog_city_terms = get_the_terms($oth_uniid, 'sit-city')) && !is_wp_error($prog_city_terms) && !empty($prog_city_terms) ? $prog_city_terms[0]->name : '', // Fixed variable name
                'description' => get_post_meta($university->ID, 'Description', true),
                'duration' => get_post_meta($university->ID, 'Study_Years', true), // ADDED MISSING DURATION
                'ranking' => get_post_meta($university->ID, 'QS_Rank', true),
                'accommodation' => is_array(get_post_meta($university->ID, 'Accommodation', true)) ?
                    implode(', ', get_post_meta($university->ID, 'Accommodation', true)) :
                    get_post_meta($university->ID, 'Accommodation', true),
                'students' => get_post_meta($university->ID, 'Number_Of_Students', true),
                'year' => get_post_meta($university->ID, 'Year_Founded', true) ?
                    date('Y', strtotime(get_post_meta($university->ID, 'Year_Founded', true))) :
                    null,
                'image_url'=>!empty(get_post_meta($oth_uniid, 'uni_image', true))  ?
                    esc_url(get_post_meta($oth_uniid, 'uni_image', true))
                    :'https://placehold.co/714x340?text=University',
                'uni_logo'=>!empty(get_post_meta($oth_uniid, 'uni_logo', true))  ?
                    esc_url(get_post_meta($oth_uniid, 'uni_logo', true))
                    :'https://placehold.co/100x50?text=University',
            ];
        }, $universities);

        $campus_args = array(
            'post_type'      => 'sit-campus',
            'posts_per_page' => -1, // Get all posts
            'post_status'    => 'publish',
            'meta_key'       => 'zh_university',
            'meta_value'     => $current_post_id,
        );
        $campus_query = new \WP_Query($campus_args);
        $campuses = $campus_query->get_posts();

        $campuses = array_map(function ($campus) {
            $uniid=get_post_meta($campus->ID, 'zh_university', true);
            return [
                'cam_id' => $campus->ID,
                'title' => $campus->post_title,
                'guid' => get_permalink($campus->ID),
                'map' => get_post_meta($campus->ID, 'Map_Cordination', true),
                'country' => get_the_terms($uniid, 'sit-country')[0]->name,
                'description' => get_post_meta($uniid, 'Description', true),
                'ranking' => get_post_meta($uniid, 'QS_Rank', true),
                'accommodation' => is_array(get_post_meta($uniid, 'Accommodation', true)) ?
                    implode(', ', get_post_meta($uniid, 'Accommodation', true)) :
                    get_post_meta($uniid, 'Accommodation', true),
                'students' => get_post_meta($uniid, 'Number_Of_Students', true),
                'year' => get_post_meta($uniid, 'Year_Founded', true) ?
                    date('Y', strtotime(get_post_meta($uniid, 'Year_Founded', true))) :
                    null,
                'image_url'=>!empty(get_post_meta($uniid, 'uni_image', true))  ?
                    esc_url(get_post_meta($uniid, 'uni_image', true))
                    :'https://placehold.co/714x340?text=University',
            ];
        }, $campuses);

        ob_start();
        Template::render('shortcodes/single-university',['campuses'=>$campuses,'program'=>$programs,'other_uni' => $universities,'university'=>$university]);
        return ob_get_clean();
    }
}