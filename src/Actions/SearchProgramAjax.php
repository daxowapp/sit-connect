<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Hook;
use SIT\Search\Services\Template;
use SIT\Search\Services\OpenAI;
use SIT\Search\Services\ProgramEmbeddings;

class SearchProgramAjax extends Hook
{
    public static array $hooks = ['wp_ajax_program_search_ajax','wp_ajax_nopriv_program_search_ajax'];

    public static int $priority = 10;

    public function __invoke()
    {
        check_ajax_referer('program_search_nonce', 'nonce');

        $keyword = sanitize_text_field($_POST['keyword']);
        
        // Try AI-powered search first
        $data = $this->aiSearch($keyword);
        
        // If AI search fails or returns no results, fall back to traditional search
        if (empty($data)) {
            $data = $this->traditionalSearch($keyword);
        }

        wp_send_json($data);
    }
    
    /**
     * AI-powered semantic search using OpenAI embeddings
     */
    private function aiSearch(string $keyword): array
    {
        try {
            $openai = new \SIT\Search\Services\SIT_OpenAI_Service();
            $embeddings = new \SIT\Search\Services\ProgramEmbeddings($openai);
            
            // Enhance query with synonyms and variations
            $enhanced_queries = $openai->enhanceSearchQuery($keyword);
            $all_results = [];
            
            // Search with each enhanced query
            foreach ($enhanced_queries as $query) {
                $results = $embeddings->searchPrograms($query, 20, 0.65); // Lower threshold for more results
                $all_results = array_merge($all_results, $results);
            }
            
            // Remove duplicates and sort by similarity
            $unique_results = [];
            foreach ($all_results as $result) {
                $program_id = $result['program_id'];
                if (!isset($unique_results[$program_id]) || $unique_results[$program_id]['similarity'] < $result['similarity']) {
                    $unique_results[$program_id] = $result;
                }
            }
            
            // Sort by similarity
            uasort($unique_results, function($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            // Convert to program data format
            $data = [];
            foreach (array_slice($unique_results, 0, 50) as $result) {
                $program_data = $this->getProgramData($result['program_id']);
                if ($program_data) {
                    $program_data['ai_similarity'] = round($result['similarity'], 3);
                    $data[] = $program_data;
                }
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log('AI Search Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Traditional WordPress search (fallback)
     */
    private function traditionalSearch(string $keyword): array
    {
        $parts = preg_split('/\s+/', $keyword);
        $title_parts = [];
        $fee = '';
        $level = '';
        $language = '';

        $known_levels = ['bachelor', 'master', 'phd', 'diploma', 'associate'];
        $known_languages = ['english', 'turkish', 'french', 'german', 'arabic'];

        foreach ($parts as $part) {
            $cleaned_part = str_replace(',', '', $part);
            $lower_part = strtolower($part);

            if (is_numeric($cleaned_part)) {
                $fee = $cleaned_part;
            } elseif (in_array($lower_part, $known_levels)) {
                $level = $lower_part;
            } elseif (in_array($lower_part, $known_languages)) {
                $language = $lower_part;
            } else {
                $title_parts[] = $part;
            }
        }

        $program_name = implode(' ', $title_parts);

        $meta_query = ['relation' => 'AND'];
        $tax_query = [];
        
        // Add Active_in_Search filter for universities
        $active_university_ids = $this->getActiveUniversityIds();
        if (!empty($active_university_ids)) {
            $meta_query[] = [
                'key'     => 'zh_university',
                'value'   => $active_university_ids,
                'compare' => 'IN',
            ];
        } else {
            // No active universities, return empty
            return [];
        }

        if (!empty($fee)) {
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key'     => 'Official_Tuition',
                    'value'   => $fee,
                    'compare' => '=',
                    'type'    => 'NUMERIC'
                ],
                [
                    'key'     => 'Advanced_Discount',
                    'value'   => $fee,
                    'compare' => '=',
                    'type'    => 'NUMERIC'
                ]
            ];
        }

        if (!empty($level)) {
            $tax_query[] = [
                'taxonomy' => 'sit-degree',
                'field'    => 'slug',
                'terms'    => $level
            ];
        }

        if (!empty($language)) {
            $tax_query[] = [
                'taxonomy' => 'sit-language',
                'field'    => 'slug',
                'terms'    => $language
            ];
        }

        $args = [
            'post_type'      => 'sit-program',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            's'              => $program_name,
            'meta_query'     => $meta_query,
        ];

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        $query = new \WP_Query($args);

        $data = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $program_data = $this->getProgramData(get_the_ID());
                if ($program_data) {
                    $data[] = $program_data;
                }
            }
            wp_reset_postdata();
        }

        return $data;
    }
    
    /**
     * Get active university IDs
     */
    private function getActiveUniversityIds(): array
    {
        $active_university_ids = [];
        $all_universities = get_posts([
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        foreach ($all_universities as $uni_id) {
            $active_in_search = get_field('Active_in_Search', $uni_id);
            if ($active_in_search == '1' || $active_in_search === true) {
                $active_university_ids[] = $uni_id;
            }
        }
        
        return $active_university_ids;
    }
    
    /**
     * Get formatted program data
     */
    private function getProgramData(int $program_id): ?array
    {
        $program = get_post($program_id);
        if (!$program) {
            return null;
        }
        
        $uniid = get_post_meta($program_id, 'zh_university', true);
        if (!$uniid) {
            return null;
        }
        
        // Check if university is active
        $active_in_search = get_field('Active_in_Search', $uniid);
        if ($active_in_search != '1' && $active_in_search !== true) {
            return null;
        }
        
        $university = get_post($uniid);
        if (!$university) {
            return null;
        }
        
        return [
            'url'        => get_permalink($program_id),
            'title'      => $program->post_title,
            'university' => $university->post_title,
            'language'   => get_the_term_list($program_id, 'sit-language', '', ', '),
            'tuition'    => get_post_meta($program_id, 'Official_Tuition', true),
            'discounted' => get_post_meta($program_id, 'Advanced_Discount', true),
            'period'     => get_post_meta($program_id, 'Study_Years', true),
            'level'      => get_the_term_list($program_id, 'sit-degree', '', ', '),
        ];
    }
}