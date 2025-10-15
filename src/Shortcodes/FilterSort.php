<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Modules\Program;
use SIT\Search\Services\Template;

class FilterSort
{
    public function __invoke()
    {
        // Start session early if not already started
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        // Handle both single level and multiple levels (level[])
        $degree = '';
        if (isset($_GET['level']) && $_GET['level'] != 0) {
            if (is_array($_GET['level'])) {
                $degree = array_map('intval', $_GET['level']);
            } else {
                $degree = intval($_GET['level']);
            }
        }
        $country = isset($_GET['country']) && $_GET['country'] != 0 ? intval($_GET['country']) : '';
        $speciality = isset($_GET['speciality']) && $_GET['speciality'] != 0 ? intval($_GET['speciality']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''; // Sanitize input
        $type = isset($_GET['univerity-type']) && $_GET['univerity-type'] != 0 ? $_GET['univerity-type'] : '';

        
        $feeFilter = $_GET['feeFiter'] ?? '';
        $duration = $_GET['duration'] ?? '';
        $isScholarShip = $_GET['isScholarShip'] ?? '';
        $language = $_GET['language'] ?? '';

        if (!empty($duration)) {
            $duration = explode(' ', $duration)[0];
        }

        $tax_query = array('relation' => 'AND');
        $degree_name='';
        $country_name='';
        $speciality_name='';
        if (!empty($degree)) {
            if (is_array($degree)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                    'operator' => 'IN',
                );
                // Get names of all selected degrees
                $degree_names = array();
                foreach ($degree as $degree_id) {
                    $term = get_term($degree_id);
                    if ($term) {
                        $degree_names[] = $term->name;
                    }
                }
                $degree_name = implode(', ', $degree_names);
            } else {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                );
                $term = get_term($degree);
                $degree_name = $term ? $term->name : '';
            }
        }

        // Add language filter (supports multiple selections)
        if (!empty($language)) {
            $languages = is_array($language) ? $language : [$language];
            $language_terms = array();
            foreach ($languages as $lang_value) {
                // Check if it's a term ID (numeric) or term name (string)
                if (is_numeric($lang_value)) {
                    // It's a term ID
                    $language_terms[] = intval($lang_value);
                } else {
                    // It's a term name, find by name
                    $lang_term = get_term_by('name', trim($lang_value), 'sit-language');
                    if ($lang_term) {
                        $language_terms[] = $lang_term->term_id;
                    }
                }
            }
            if (!empty($language_terms)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-language',
                    'field'    => 'term_id',
                    'terms'    => $language_terms,
                    'operator' => 'IN',
                );
            }
        }

        // Apply active countries filter
        $allowed_countries = \SIT\Search\Services\ActiveCountries::getActiveCountryIds();
        
        // If user selected a specific country, use it only if it's in active countries
        if (!empty($country)) {
            if (in_array($country, $allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $country,
                );
                $term = get_term($country);
                $country_name = $term->name;
            } else {
                // If selected country is not Turkey/North Cyprus, default to both
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        } else {
            // No specific country selected, default to active countries
            if (!empty($allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        }

        if (!empty($speciality)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-speciality',
                'field'    => 'term_id',
                'terms'    => $speciality,
            );
            $term = get_term($speciality);
            $speciality_name=$term->name;
        }

        $meta_query = array('relation' => 'AND');

        $meta_query[] = array(
            'key'     => 'zh_university',
            'compare' => 'EXISTS',
        );

        // Filter programs by university's Active_in_Search status
        $active_university_ids = array();
        $all_universities = get_posts(array(
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        foreach ($all_universities as $uni_id) {
            $active_in_search = get_field('Active_in_Search', $uni_id);
            if ($active_in_search == '1' || $active_in_search === true) {
                $active_university_ids[] = $uni_id;
            }
        }
        
        if (!empty($active_university_ids)) {
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => $active_university_ids,
                'compare' => 'IN',
            );
        } else {
            // No active universities found, return no results
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => array(-1),
                'compare' => 'IN',
            );
        }

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

        if (!empty($duration) && is_numeric($duration)) {
            $meta_query[] = array(
                'key'     => 'Study_Years',
                'value'   => intval($duration),
                'compare' => '=',
                'type'    => 'NUMERIC',
            );
        }

        if (!empty($isScholarShip)) {
            $meta_query[] = array(
                'key'     => 'isScholarShip',
                'value'   => $isScholarShip,
                'compare' => '=',
            );
        }

        // Add university filter (supports multiple selections)
        $university = $_GET['university'] ?? '';
        if (!empty($university)) {
            $universities = is_array($university) ? $university : [$university];
            $university_ids = array();
            
            foreach ($universities as $uni_name) {
                $university_posts = get_posts(array(
                    'post_type' => 'sit-university',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'title' => $uni_name,
                    'fields' => 'ids'
                ));
                
                if (!empty($university_posts)) {
                    $university_ids = array_merge($university_ids, $university_posts);
                }
            }
            
            if (!empty($university_ids)) {
                // Filter university IDs to only include those with Active_in_Search = 1
                $active_university_ids = array();
                foreach ($university_ids as $uni_id) {
                    $active_in_search = get_field('Active_in_Search', $uni_id);
                    if ($active_in_search == '1' || $active_in_search === true) {
                        $active_university_ids[] = $uni_id;
                    }
                }
                
                if (!empty($active_university_ids)) {
                    $meta_query[] = array(
                        'key'     => 'zh_university',
                        'value'   => $active_university_ids,
                        'compare' => 'IN',
                    );
                } else {
                    // No active universities found, return no results
                    $meta_query[] = array(
                        'key'     => 'zh_university',
                        'value'   => array(-1), // Non-existent ID to return no results
                        'compare' => 'IN',
                    );
                }
            }
        }

        $university_ids = array();

        if (!empty($search)) {
            $uni_type_search_ids=self::get_uni__search_ids();
            foreach ($uni_type_search_ids as $item) {
                if (!in_array($item, $university_ids)) {
                    $university_ids[] = $item;
                }
            }
        }

        $uni_type_ids=self::get_uni_ids();
        if((!empty($type) && $type != 'All') && empty($search)){
            foreach ($uni_type_ids as $item) {
                if (!in_array($item, $university_ids)) {
                    $university_ids[] = $item;
                }
            }
        }

        if((!empty($type) && $type != 'All') && !empty($search)){
            $university_ids = array_values(array_filter($university_ids, function($item) use ($uni_type_ids) {
                return in_array($item, $uni_type_ids);
            }));
        }




        if (!empty($search) && (!empty($type) && $type != 'All')) {

            $meta_query[] = array(
                'relation' => 'AND',
                array(
                    'key'     => 'zh_university',
                    'value'   => $university_ids,
                    'compare' => 'IN',
                ),
                array(
                    'key'     => 'Product_Name',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ),
            );
        } elseif(empty($search) && (!empty($type) && $type != 'All')){
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => $university_ids,
                'compare' => 'IN',
            );
        } elseif(!empty($search) && empty($type)){
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => 'zh_university',
                    'value'   => $university_ids,
                    'compare' => 'IN',
                ),
                array(
                    'key'     => 'Product_Name',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ),
            );
        } 




        $args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => 21,
            'post_status'    => 'publish',
            'paged'          => $paged,
            'meta_query'     => $meta_query,
        );
        $pdf_args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
        );

        if (!empty($degree) || !empty($country) || !empty($speciality)) {
            $args['tax_query'] = $tax_query;
            $pdf_args['tax_query'] = $tax_query;
        }




        // Sorting will be handled after query execution to prioritize university priority


        $query = new \WP_Query($args);
        $pdf_query = new \WP_Query($pdf_args);
        $counter = $query->found_posts;
        $programs = $query->get_posts();
        $pdf_programs = $pdf_query->get_posts();
        $programs = array_map(function ($program) {
            $uniid = get_post_meta($program->ID, 'zh_university', true);
            $university = get_post($uniid);
//            $zoho_product_id = get_post_meta($program->ID, 'zoho_product_id', true);
//            Program::up_program($zoho_product_id);
            $language_terms = get_the_terms($program->ID, 'sit-language');
            $language_name = '';
            if ($language_terms && !is_wp_error($language_terms)) {
                $language_name = $language_terms[0]->name;
            }
            
            return [
                'title' => $program->post_title,
                'link' => get_permalink($program->ID),
                'uni_title' => $university->post_title,
                'country' => get_the_terms($university->ID, 'sit-country')[0]->name,
                'language' => $language_name,
                'description' => !empty(get_post_meta($program->ID, 'Description', true)) ?
                    get_post_meta($program->ID, 'Description', true)
                    : 'Empty',
                'type'=>get_post_meta($university->ID, 'Sector', true),
                'Service_fee' => get_post_meta($program->ID, 'Service_fee', true),
                'Application_Fee' => get_post_meta($program->ID, 'Application_Fee', true),
                'ranking' => get_post_meta($university->ID, 'QS_Rank', true),
                'duration' => get_post_meta($program->ID, 'Study_Years', true),
                'students' => get_post_meta($university->ID, 'Number_Of_Students', true),
                'fee' => get_post_meta($program->ID, 'Official_Tuition', true),
                'Tuition_Currency' => get_post_meta($program->ID, 'Tuition_Currency', true),
                'discounted_fee' => get_post_meta($program->ID, 'Discounted_Tuition', true),
                'Advanced_Discount' => get_post_meta($program->ID, 'Advanced_Discount', true),
                'university_priority' => get_post_meta($university->ID, 'university_priority', true),
                'image_url' => !empty(get_post_meta($university->ID, 'uni_image', true)) ?
                    esc_url(get_post_meta($university->ID, 'uni_image', true))
                    : 'https://placehold.co/714x340?text=University',
            ];
        }, $programs);

        // Sort programs by university priority first, then by selected sort method
        usort($programs, function($a, $b) use ($sort) {
            // First, sort by university priority (higher priority first)
            $priority_a = intval($a['university_priority'] ?: 0);
            $priority_b = intval($b['university_priority'] ?: 0);
            
            if ($priority_a !== $priority_b) {
                return $priority_b - $priority_a; // Higher priority first
            }
            
            // If priorities are equal, apply secondary sorting
            switch ($sort) {
                case 'fee_low':
                    return intval($a['fee'] ?: 0) - intval($b['fee'] ?: 0);
                case 'fee_high':
                    return intval($b['fee'] ?: 0) - intval($a['fee'] ?: 0);
                case 'popular':
                    return intval($b['views_count'] ?: 0) - intval($a['views_count'] ?: 0);
                case 'newest':
                    return strtotime($b['date'] ?: '0') - strtotime($a['date'] ?: '0');
                default:
                    return 0; // Keep original order for 'featured' or unknown sorts
            }
        });

        $pdf_programs = array_map(function ($program) {
            $uniid = get_post_meta($program->ID, 'zh_university', true);
            $university = get_post($uniid);
//            $zoho_product_id = get_post_meta($program->ID, 'zoho_product_id', true);
//            Program::up_program($zoho_product_id);
            return [
                'title' => $program->post_title,
                'link' => $program->guid,
                'uni_title' => $university->post_title,
                'country' => get_the_terms($university->ID, 'sit-country')[0]->name,
                'description' => !empty(get_post_meta($program->ID, 'Description', true)) ?
                    get_post_meta($program->ID, 'Description', true)
                    : 'Empty',
                'ranking' => get_post_meta($university->ID, 'QS_Rank', true),
                'duration' => get_post_meta($program->ID, 'Study_Years', true),
                'students' => get_post_meta($university->ID, 'Number_Of_Students', true),
                'fee' => get_post_meta($program->ID, 'Official_Tuition', true),
                'discounted_fee' => get_post_meta($program->ID, 'Discounted_Tuition', true),
                'Advanced_Discount' => get_post_meta($program->ID, 'Advanced_Discount', true),
                'image_url' => !empty(get_post_meta($university->ID, 'uni_image', true)) ?
                    esc_url(get_post_meta($university->ID, 'uni_image', true))
                    : 'https://placehold.co/714x340?text=University',
            ];
        }, $pdf_programs);


        //store session as recent_search
        $recent_search = [
            'degree' => $degree,
            'country' => $country,
            'speciality' => $speciality,
            'feeFilter' => $feeFilter,
            'duration' => $duration,
            'isScholarShip' => $isScholarShip,
            'sort' => $sort,
        ];

        $recent_searches = $_SESSION['recent_searches'] ?? [];
        $recent_searches = array_merge([$recent_search], $recent_searches);
        $_SESSION['recent_searches'] = $recent_searches;
        $disstr='';
        if($degree_name != '' && $country_name != '' && $speciality_name != ''){
            $disstr="This document provides a comprehensive list of ".$degree_name." degrees in ".$speciality_name." from ".$country_name." leading universities. Each program includes details about duration, tuition fees, language requirements, application deadlines, and more.";
        }
        // Extract unique languages from the results
        $available_languages = [];
        $language_ids = [];
        foreach ($programs as $program_data) {
            // Assuming the original post ID is not directly in $program_data, we need to get it.
            // This part is tricky as the original post object is lost after mapping.
            // Let's re-fetch the terms using the title as a workaround, though it's not ideal.
            // A better solution would be to pass the program ID into the mapped array.
            // For now, let's assume we can get the languages from the mapped data if available.
            if (!empty($program_data['language'])) {
                // This only gets the first language. We need all of them.
                // The logic needs to be more robust.
            }
        }

        // Create a separate query to get ALL programs for filter options (not paginated)
        $filter_args = $args;
        $filter_args['posts_per_page'] = -1; // Get all posts
        unset($filter_args['paged']); // Remove pagination
        $filter_query = new \WP_Query($filter_args);
        
        // Get all languages from ALL results (not just current page)
        $all_program_posts = $filter_query->get_posts();
        $unique_languages = [];
        $all_universities_for_filter = [];
        $unique_degrees = [];
        
        foreach ($all_program_posts as $program_post) {
            // Extract languages
            $language_terms = get_the_terms($program_post->ID, 'sit-language');
            if ($language_terms && !is_wp_error($language_terms)) {
                foreach ($language_terms as $term) {
                    if (!isset($unique_languages[$term->term_id])) {
                        $unique_languages[$term->term_id] = $term;
                    }
                }
            }
            
            // Extract degrees
            $degree_terms = get_the_terms($program_post->ID, 'sit-degree');
            if ($degree_terms && !is_wp_error($degree_terms)) {
                foreach ($degree_terms as $term) {
                    if (!isset($unique_degrees[$term->term_id])) {
                        $unique_degrees[$term->term_id] = $term;
                    }
                }
            }
            
            // Extract universities for filter
            $uni_id = get_post_meta($program_post->ID, 'zh_university', true);
            if ($uni_id) {
                $uni_title = get_the_title($uni_id);
                if ($uni_title && !in_array($uni_title, $all_universities_for_filter)) {
                    $all_universities_for_filter[] = $uni_title;
                }
            }
        }
        
        // Sort universities
        sort($all_universities_for_filter);

        // Convert unique degrees array to indexed array
        $all_degrees = array_values($unique_degrees);

        ob_start();
        Template::render('shortcodes/filter-sort', [
            'disstr' => $disstr,
            'pdf_program' => $pdf_programs,
            'programs' => $programs, 
            'query' => $query, 
            'degreeid' => $degree, 
            'specialityid' => $speciality, 
            'countryid' => $country,
            'available_languages' => $unique_languages, // Pass unique languages to the template
            'all_degrees' => $all_degrees, // Pass all degrees to the template
            'all_universities_for_filter' => $all_universities_for_filter, // Pass all universities from ALL results
        ]);
        return ob_get_clean();
    }

    public function get_uni_ids(){

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        // Handle both single level and multiple levels (level[])
        $degree = '';
        if (isset($_GET['level']) && $_GET['level'] != 0) {
            if (is_array($_GET['level'])) {
                $degree = array_map('intval', $_GET['level']);
            } else {
                $degree = intval($_GET['level']);
            }
        }
        $country = isset($_GET['country']) && $_GET['country'] != 0 ? intval($_GET['country']) : '';
        $speciality = isset($_GET['speciality']) && $_GET['speciality'] != 0 ? intval($_GET['speciality']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''; // Sanitize input
        $type = isset($_GET['univerity-type']) && $_GET['univerity-type'] != 0 ? $_GET['univerity-type'] : '';

        $feeFilter = $_GET['feeFiter'] ?? '';
        $duration = $_GET['duration'] ?? '';
        $isScholarShip = $_GET['isScholarShip'] ?? '';
        $language = $_GET['language'] ?? '';

        if (!empty($duration)) {
            $duration = explode(' ', $duration)[0];
        }

        $tax_query = array('relation' => 'AND');
        $degree_name='';
        $country_name='';
        $speciality_name='';
        if (!empty($degree)) {
            if (is_array($degree)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                    'operator' => 'IN',
                );
                // Get names of all selected degrees
                $degree_names = array();
                foreach ($degree as $degree_id) {
                    $term = get_term($degree_id);
                    if ($term) {
                        $degree_names[] = $term->name;
                    }
                }
                $degree_name = implode(', ', $degree_names);
            } else {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                );
                $term = get_term($degree);
                $degree_name = $term ? $term->name : '';
            }
        }

        // Add language filter (supports multiple selections)
        if (!empty($language)) {
            $languages = is_array($language) ? $language : [$language];
            $language_terms = array();
            foreach ($languages as $lang_value) {
                // Check if it's a term ID (numeric) or term name (string)
                if (is_numeric($lang_value)) {
                    // It's a term ID
                    $language_terms[] = intval($lang_value);
                } else {
                    // It's a term name, find by name
                    $lang_term = get_term_by('name', trim($lang_value), 'sit-language');
                    if ($lang_term) {
                        $language_terms[] = $lang_term->term_id;
                    }
                }
            }
            if (!empty($language_terms)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-language',
                    'field'    => 'term_id',
                    'terms'    => $language_terms,
                    'operator' => 'IN',
                );
            }
        }

        // Apply active countries filter
        $allowed_countries = \SIT\Search\Services\ActiveCountries::getActiveCountryIds();
        
        // If user selected a specific country, use it only if it's in active countries
        if (!empty($country)) {
            if (in_array($country, $allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $country,
                );
                $term = get_term($country);
                $country_name = $term->name;
            } else {
                // If selected country is not Turkey/North Cyprus, default to both
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        } else {
            // No specific country selected, default to active countries
            if (!empty($allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        }

        if (!empty($speciality)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-speciality',
                'field'    => 'term_id',
                'terms'    => $speciality,
            );
            $term = get_term($speciality);
            $speciality_name=$term->name;
        }

        $meta_query = array('relation' => 'AND');

        $meta_query[] = array(
            'key'     => 'zh_university',
            'compare' => 'EXISTS',
        );

        // Filter programs by university's Active_in_Search status
        $active_university_ids = array();
        $all_universities = get_posts(array(
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        foreach ($all_universities as $uni_id) {
            $active_in_search = get_field('Active_in_Search', $uni_id);
            if ($active_in_search == '1' || $active_in_search === true) {
                $active_university_ids[] = $uni_id;
            }
        }
        
        if (!empty($active_university_ids)) {
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => $active_university_ids,
                'compare' => 'IN',
            );
        } else {
            // No active universities found, return no results
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => array(-1),
                'compare' => 'IN',
            );
        }
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
        if (!empty($duration) && is_numeric($duration)) {
            $meta_query[] = array(
                'key'     => 'Study_Years',
                'value'   => intval($duration),
                'compare' => '=',
                'type'    => 'NUMERIC',
            );
        }
        if (!empty($isScholarShip)) {
            $meta_query[] = array(
                'key'     => 'isScholarShip',
                'value'   => $isScholarShip,
                'compare' => '=',
            );
        }
        $args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
        );
        if (!empty($degree) || !empty($country) || !empty($speciality)) {
            $args['tax_query'] = $tax_query;
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
        $query = new \WP_Query($args);
        $programs = $query->get_posts();
        $zh_university_values = array();
        $filtered_university_ids = array();
        if (!empty($programs)) {
            foreach ($programs as $program) {
                $value = get_post_meta($program->ID, 'zh_university', true);
                if (!empty($value)) {
                    $zh_university_values[] = $value;
                }
            }
            $zh_university_values = array_unique($zh_university_values);
        }
        if (!empty($zh_university_values)) {
            foreach ($zh_university_values as $university_id) {
                $sector_value = get_post_meta($university_id, 'Sector', true);
                if ($sector_value === $type) {
                    $filtered_university_ids[] = $university_id;
                }
            }

            $filtered_university_ids = array_unique($filtered_university_ids);
        }
        return $filtered_university_ids;
    }

    public function get_uni__search_ids(){

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        // Handle both single level and multiple levels (level[])
        $degree = '';
        if (isset($_GET['level']) && $_GET['level'] != 0) {
            if (is_array($_GET['level'])) {
                $degree = array_map('intval', $_GET['level']);
            } else {
                $degree = intval($_GET['level']);
            }
        }
        $country = isset($_GET['country']) && $_GET['country'] != 0 ? intval($_GET['country']) : '';
        $speciality = isset($_GET['speciality']) && $_GET['speciality'] != 0 ? intval($_GET['speciality']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''; // Sanitize input
        $type = isset($_GET['univerity-type']) && $_GET['univerity-type'] != 0 ? $_GET['univerity-type'] : '';

        $feeFilter = $_GET['feeFiter'] ?? '';
        $duration = $_GET['duration'] ?? '';
        $isScholarShip = $_GET['isScholarShip'] ?? '';
        $language = $_GET['language'] ?? '';

        if (!empty($duration)) {
            $duration = explode(' ', $duration)[0];
        }

        $tax_query = array('relation' => 'AND');
        $degree_name='';
        $country_name='';
        $speciality_name='';
        if (!empty($degree)) {
            if (is_array($degree)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                    'operator' => 'IN',
                );
                // Get names of all selected degrees
                $degree_names = array();
                foreach ($degree as $degree_id) {
                    $term = get_term($degree_id);
                    if ($term) {
                        $degree_names[] = $term->name;
                    }
                }
                $degree_name = implode(', ', $degree_names);
            } else {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                );
                $term = get_term($degree);
                $degree_name = $term ? $term->name : '';
            }
        }

        // Add language filter (supports multiple selections)
        if (!empty($language)) {
            $languages = is_array($language) ? $language : [$language];
            $language_terms = array();
            foreach ($languages as $lang_value) {
                // Check if it's a term ID (numeric) or term name (string)
                if (is_numeric($lang_value)) {
                    // It's a term ID
                    $language_terms[] = intval($lang_value);
                } else {
                    // It's a term name, find by name
                    $lang_term = get_term_by('name', trim($lang_value), 'sit-language');
                    if ($lang_term) {
                        $language_terms[] = $lang_term->term_id;
                    }
                }
            }
            if (!empty($language_terms)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-language',
                    'field'    => 'term_id',
                    'terms'    => $language_terms,
                    'operator' => 'IN',
                );
            }
        }

        // Apply active countries filter
        $allowed_countries = \SIT\Search\Services\ActiveCountries::getActiveCountryIds();
        
        // If user selected a specific country, use it only if it's in active countries
        if (!empty($country)) {
            if (in_array($country, $allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $country,
                );
                $term = get_term($country);
                $country_name = $term->name;
            } else {
                // If selected country is not Turkey/North Cyprus, default to both
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        } else {
            // No specific country selected, default to active countries
            if (!empty($allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        }

        if (!empty($speciality)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-speciality',
                'field'    => 'term_id',
                'terms'    => $speciality,
            );
            $term = get_term($speciality);
            $speciality_name=$term->name;
        }

        $meta_query = array('relation' => 'AND');

        $meta_query[] = array(
            'key'     => 'zh_university',
            'compare' => 'EXISTS',
        );

        // Filter programs by university's Active_in_Search status
        $active_university_ids = array();
        $all_universities = get_posts(array(
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        foreach ($all_universities as $uni_id) {
            $active_in_search = get_field('Active_in_Search', $uni_id);
            if ($active_in_search == '1' || $active_in_search === true) {
                $active_university_ids[] = $uni_id;
            }
        }
        
        if (!empty($active_university_ids)) {
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => $active_university_ids,
                'compare' => 'IN',
            );
        } else {
            // No active universities found, return no results
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => array(-1),
                'compare' => 'IN',
            );
        }
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
        if (!empty($duration) && is_numeric($duration)) {
            $meta_query[] = array(
                'key'     => 'Study_Years',
                'value'   => intval($duration),
                'compare' => '=',
                'type'    => 'NUMERIC',
            );
        }
        if (!empty($isScholarShip)) {
            $meta_query[] = array(
                'key'     => 'isScholarShip',
                'value'   => $isScholarShip,
                'compare' => '=',
            );
        }
        $args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
        );
        if (!empty($degree) || !empty($country) || !empty($speciality)) {
            $args['tax_query'] = $tax_query;
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
        $query = new \WP_Query($args);
        $programs = $query->get_posts();
        $zh_university_values = array();
        $filtered_university_ids = array();
        if (!empty($programs)) {
            foreach ($programs as $program) {
                $value = get_post_meta($program->ID, 'zh_university', true);
                if (!empty($value)) {
                    $zh_university_values[] = $value;
                }
            }
            $zh_university_values = array_unique($zh_university_values);
        }
        if (!empty($zh_university_values)) {
            $university_query = new \WP_Query(array(
                'post_type'      => 'sit-university',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'post__in'       => $zh_university_values,
                'meta_query'     => array(
                    array(
                        'key'     => 'Account_Name',
                        'value'   => $search,
                        'compare' => 'LIKE',
                    ),
                ),
                'fields'         => 'ids',
            ));

            if ($university_query->have_posts()) {
                $filtered_university_ids = $university_query->posts;
            }
            $filtered_university_ids = array_unique($filtered_university_ids);
        }
        return $filtered_university_ids;
    }
}