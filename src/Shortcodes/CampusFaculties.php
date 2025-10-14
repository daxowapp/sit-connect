<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class CampusFaculties
{
    public function __invoke()
    {
        ob_start();
        $current_post_id = get_the_ID();
        $post_title = get_the_title($current_post_id);
        $faculties = get_post_meta($current_post_id, 'sit-faculty', true);
        $oth_uniid = get_post_meta($current_post_id, 'zh_university', true);
        $degree_id = isset($_GET['level']) && $_GET['level'] != 0 ? intval($_GET['level']) : '';
        $country_id = isset($_GET['country']) && $_GET['country'] != 0 ? intval($_GET['country']) : '';
        $speciality_id = isset($_GET['speciality']) && $_GET['speciality'] != 0 ? intval($_GET['speciality']) : '';
        if (!empty($faculties) && $faculties!='good') {
            $ids = explode(',', $faculties);
            $terms = get_terms(array(
                'taxonomy'   => 'sit-faculty',
                'include'    => $ids,
                'hide_empty' => false,
            ));

            $all_programs = [];

            // Check if the university is active in search first
            $active_in_search = get_field('Active_in_Search', $oth_uniid);
            if ($active_in_search != '1' && $active_in_search !== true) {
                $terms = array(); // Return empty array if university is not active
            } else {
                $terms = array_map(function ($term) use ($oth_uniid, &$all_programs) {
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
                    $degree = isset($_GET['level']) && $_GET['level'] != 0 ? intval($_GET['level']) : '';
                    $country = isset($_GET['country']) && $_GET['country'] != 0 ? intval($_GET['country']) : '';
                    $speciality = isset($_GET['speciality']) && $_GET['speciality'] != 0 ? intval($_GET['speciality']) : '';
                    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

                    $feeFilter = $_GET['feeFiter'] ?? '';
                    $duration = $_GET['duration'] ?? '';
                    $isScholarShip = $_GET['isScholarShip'] ?? '';
                    $language = $_GET['language'] ?? [];

                    // Ensure language is always an array
                    if (!is_array($language)) {
                        $language = [$language];
                    }

                    if (!empty($duration)) {
                        $duration = explode(' ', $duration)[0];
                    }

                    $tax_query = array('relation' => 'AND');

                    if (!empty($degree)) {
                        $tax_query[] = array(
                            'taxonomy' => 'sit-degree',
                            'field'    => 'term_id',
                            'terms'    => $degree,
                        );
                    }

                    if (!empty($language)) {
                        $tax_query[] = array(
                            'taxonomy' => 'sit-language',
                            'field'    => 'term_id',
                            'terms'    => $language,
                            'operator' => 'IN', // Support multiple language selections
                        );
                    }

                    if (!empty($country)) {
                        $tax_query[] = array(
                            'taxonomy' => 'sit-country',
                            'field'    => 'term_id',
                            'terms'    => $country,
                        );
                    }

                    if (!empty($speciality)) {
                        $tax_query[] = array(
                            'taxonomy' => 'sit-speciality',
                            'field'    => 'term_id',
                            'terms'    => $speciality,
                        );
                    }

                    $meta_query = array('relation' => 'AND');

                    $meta_query[] = array(
                        'key'     => 'zh_university',
                        'value' => $oth_uniid,
                    );

                $tax_query[]=array(
                    array(
                        'taxonomy' => 'sit-faculty',
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ),
                );

                if (!empty($feeFilter)) {
                    $feeRange = explode('-', $feeFilter);
                    if (count($feeRange) == 2) {
                        $meta_query[] = array(
                            'key'     => 'Official_Tuition',
                            'value'   => array(intval($feeRange[0]), intval($feeRange[1])),
                            'type'    => 'NUMERIC',
                            'compare' => 'BETWEEN',
                        );
                    }
                }

                // Check both Study_Years and Duration fields for duration filter
                if (!empty($duration) && is_numeric($duration)) {
                    $meta_query[] = array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'Study_Years',
                            'value'   => intval($duration),
                            'compare' => '=',
                            'type'    => 'NUMERIC',
                        ),
                        array(
                            'key'     => 'Duration',
                            'value'   => intval($duration),
                            'compare' => '=',
                            'type'    => 'NUMERIC',
                        ),
                    );
                }

                if (!empty($isScholarShip)) {
                    $meta_query[] = array(
                        'key'     => 'isScholarShip',
                        'value'   => $isScholarShip,
                        'compare' => '=',
                    );
                }

                if (!empty($search)) {
                    $meta_query[] = array(
                        'key'     => 'Product_Name',
                        'value'   => $search,
                        'compare' => 'LIKE',
                    );
                }

                $args = array(
                    'post_type'      => 'sit-program',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'meta_query'     => $meta_query,
                );

                if (!empty($degree) || !empty($country) || !empty($speciality) || !empty($language)) {
                    $args['tax_query'] = $tax_query;
                }
                else{
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'sit-campus',
                            'field'    => 'term_id',
                            'terms'    => $campus_id,
                        ),
                    );
                }



                switch ($sort) {
                    case 'fee_low':
                        $args['meta_key'] = 'Official_Tuition';
                        $args['orderby']  = 'meta_value_num';
                        $args['order']    = 'ASC';
                        break;

                    case 'fee_high':
                        $args['meta_key'] = 'Official_Tuition';
                        $args['orderby']  = 'meta_value_num';
                        $args['order']    = 'DESC';
                        break;

                    case 'popular':
                        $args['meta_key'] = 'views_count';
                        $args['orderby']  = 'meta_value_num';
                        $args['order']    = 'DESC';
                        break;

                    case 'newest':
                        $args['orderby'] = 'date';
                        $args['order']   = 'DESC';
                        break;
                }

//                echo '<pre>';
//                print_r($args);
//                echo '</pre>';
                $query = new \WP_Query($args);

                $programs = array_map(function ($post) {

                    $oth_uniid = get_post_meta($post->ID, 'zh_university', true);
                    $unii_title = get_the_title($oth_uniid);
                    $country_terms = get_the_terms($post->ID, 'sit-country');
                    $country_name = !empty($country_terms) && !is_wp_error($country_terms)
                        ? $country_terms[0]->name
                        : '';

                    return [
                        'uni_id'        => $post->ID,
                        'title' => $post->post_title,
                        'link' => get_permalink($post->ID),
                        'uni_title' => $unii_title,
                        'country' => $country_name,
                        'description' => !empty(get_post_meta($post->ID, 'Description', true)) ?
                            get_post_meta($post->ID, 'Description', true)
                            : 'Empty',
                        'ranking' => get_post_meta($oth_uniid, 'QS_Rank', true),
                        'duration' => get_post_meta($post->ID, 'Study_Years', true),
                        'students' => get_post_meta($oth_uniid, 'Number_Of_Students', true),
                        'fee' => get_post_meta($post->ID, 'Official_Tuition', true),
                        'discounted_fee' => get_post_meta($post->ID, 'Discounted_Tuition', true),
                        'Advanced_Discount' => get_post_meta($post->ID, 'Advanced_Discount', true),
                        'image_url' => !empty(get_post_meta($oth_uniid, 'uni_image', true)) ?
                            esc_url(get_post_meta($oth_uniid, 'uni_image', true))
                            : 'https://placehold.co/714x340?text=University',
                    ];
                }, $query->posts);

                $all_programs = array_merge($all_programs, $programs);

                return [
                    'id'        => $term->term_id,
                    'name'      => $term->name,
                    'slug'      => $term->slug,
                    'count'     => count($programs),
                    'image_url' => !empty(get_term_meta($term->term_id, 'spec_image', true)) ?
                        esc_url(get_term_meta($term->term_id, 'spec_image', true)) :
                        'https://placehold.co/60x60',
                ];
            }, $terms);
            }
            $degree_name='';
            $country_name='';
            $speciality_name='';
            if(!empty($degree_id)){
                $degree_name=get_term($degree_id,'sit-degree')->name;
            }
            if(!empty($country_id)){
                $country_name=get_term($country_id,'sit-country')->name;
            }
            if(!empty($speciality_id)){
                $speciality_name=get_term($speciality_id,'sit-speciality')->name;
            }
            $disstr='';
            if($post_title != ''){
                $disstr="This document provides a comprehensive list of programs degrees in ".$post_title.". Each program includes details about duration, tuition fees, language requirements, application deadlines, and more.";
            }
            // Get filter data for sidebar
            $all_degrees = get_terms(['taxonomy' => 'sit-degree', 'hide_empty' => false]);
            
            // Extract unique languages and universities from programs
            $unique_languages = [];
            $all_universities_for_filter = [];
            
            foreach ($all_programs as $program) {
                // Extract languages
                $language_terms = get_the_terms($program->ID, 'sit-language');
                if ($language_terms && !is_wp_error($language_terms)) {
                    foreach ($language_terms as $term) {
                        if (!isset($unique_languages[$term->term_id])) {
                            $unique_languages[$term->term_id] = $term;
                        }
                    }
                }
                
                // Extract universities
                $university_id = get_post_meta($program->ID, 'university', true);
                if ($university_id) {
                    $university = get_post($university_id);
                    if ($university && !in_array($university->post_title, $all_universities_for_filter)) {
                        $all_universities_for_filter[] = $university->post_title;
                    }
                }
            }
            
            // Sort arrays
            sort($all_universities_for_filter);
            
            Template::render('shortcodes/single-campus', [
                'terms'    => $terms,
                'disstr'  => $disstr,
                'programs' => $all_programs,
                'degreeid' => $degree_id,
                'specialityid' => $speciality_id,
                'countryid' => $country_id,
                'filter_data' => [
                    'degrees' => $all_degrees,
                    'universities' => $all_universities_for_filter
                ],
                'available_languages' => $unique_languages
            ]);
        } else {
            echo 'No Faculties';
        }


        return ob_get_clean();
    }
}