<?php

namespace SIT\Search\Services;

class ZohoSyncService
{
    public static function sync($action, $country_filter = '')
    {
        try {
            $zoho = new Zoho();
            
            switch ($action) {
                case 'countries':
                    return self::syncCountries($zoho);
                    
                case 'cities':
                    return self::syncCities($zoho);
                    
                case 'universities':
                    return self::syncUniversities($zoho, $country_filter);
                    
                case 'programs':
                    return self::syncPrograms($zoho, $country_filter);
                    
                case 'degrees':
                    return self::syncDegrees($zoho);
                    
                case 'faculties':
                    return self::syncFaculties($zoho);
                    
                case 'languages':
                    return self::syncLanguages($zoho);
                    
                case 'specialities':
                    return self::syncSpecialities($zoho);
                    
                default:
                    return ['success' => false, 'message' => 'Invalid sync action'];
            }
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    private static function syncCountries($zoho)
    {
        $module = \SIT\Search\Modules\Country::$module ?? 'Countries';
        $items = self::fetchAllFromZoho($zoho, $module);
        
        $success = 0;
        $updated = 0;
        $created = 0;
        $errors = 0;
        
        foreach ($items as $item) {
            try {
                $zoho_id = $item['id'];
                
                // Check if country already exists
                $existing_terms = get_terms([
                    'taxonomy' => 'sit-country',
                    'meta_key' => 'zoho_country_id',
                    'meta_value' => $zoho_id,
                    'hide_empty' => false
                ]);
                
                if (!empty($existing_terms) && !is_wp_error($existing_terms)) {
                    // Update existing
                    $term = $existing_terms[0];
                    wp_update_term($term->term_id, 'sit-country', [
                        'name' => $item['Name'] ?? $item['Country_Name'] ?? ''
                    ]);
                    $updated++;
                    $success++;
                } else {
                    // Create new
                    $term_id = \SIT\Search\Modules\Country::create_item($item);
                    if ($term_id) {
                        $created++;
                        $success++;
                    } else {
                        $errors++;
                    }
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Synced {$success} countries ({$created} created, {$updated} updated)" . ($errors > 0 ? ", {$errors} failed" : "")
        ];
    }
    
    private static function syncCities($zoho)
    {
        $module = \SIT\Search\Modules\City::$module ?? 'Cities';
        $items = self::fetchAllFromZoho($zoho, $module);
        
        $success = 0;
        $updated = 0;
        $created = 0;
        $errors = 0;
        
        foreach ($items as $item) {
            try {
                $zoho_id = $item['id'];
                
                // Check if city already exists
                $existing_terms = get_terms([
                    'taxonomy' => 'sit-city',
                    'meta_key' => 'zoho_city_id',
                    'meta_value' => $zoho_id,
                    'hide_empty' => false
                ]);
                
                if (!empty($existing_terms) && !is_wp_error($existing_terms)) {
                    // Update existing
                    $term = $existing_terms[0];
                    wp_update_term($term->term_id, 'sit-city', [
                        'name' => $item['Name'] ?? $item['City_Name'] ?? ''
                    ]);
                    $updated++;
                    $success++;
                } else {
                    // Create new
                    $term_id = \SIT\Search\Modules\City::create_item($item);
                    if ($term_id) {
                        $created++;
                        $success++;
                    } else {
                        $errors++;
                    }
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Synced {$success} cities ({$created} created, {$updated} updated)" . ($errors > 0 ? ", {$errors} failed" : "")
        ];
    }
    
    private static function syncUniversities($zoho, $country_filter = '')
    {
        $module = \SIT\Search\Modules\University::$module ?? 'Accounts';
        
        // Build Zoho API criteria for country filter to save API credits
        $criteria = '';
        if (!empty($country_filter)) {
            // Zoho API format: (University_Country:equals:Spain)
            $criteria = "(University_Country:equals:{$country_filter})";
        }
        
        $items = self::fetchAllFromZoho($zoho, $module, 10, $criteria);
        
        $success = 0;
        $updated = 0;
        $created = 0;
        $errors = 0;
        $skipped = 0;
        $debug_countries = [];
        
        global $wpdb;
        
        $total_items = count($items);
        $processed = 0;
        
        foreach ($items as $item) {
            $processed++;
            
            // Flush output every 10 items to prevent browser timeout
            if ($processed % 10 === 0) {
                echo "<!-- Processing {$processed}/{$total_items} universities -->";
                if (function_exists('wp_ob_end_flush_all')) {
                    wp_ob_end_flush_all();
                }
                flush();
            }
            // Skip inactive universities
            if (empty($item['Active_in_Search'])) {
                $skipped++;
                continue;
            }
            
            // Filter by country if specified
            if (!empty($country_filter)) {
                // Try different possible field structures
                $uni_country = $item['University_Country']['Name'] ?? 
                              $item['University_Country']['name'] ?? 
                              $item['Country']['Name'] ?? 
                              $item['Country']['name'] ?? 
                              $item['Country'] ?? 
                              '';
                
                // Collect unique country names for debugging
                if (!empty($uni_country) && !in_array($uni_country, $debug_countries)) {
                    $debug_countries[] = $uni_country;
                }
                
                if (strcasecmp($uni_country, $country_filter) !== 0) {
                    $skipped++;
                    continue;
                }
            }
            
            try {
                $zoho_id = $item['id'];
                
                // Check if university already exists
                $existing_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = 'zoho_account_id' 
                    AND meta_value = %s",
                    $zoho_id
                ));
                
                // Remove duplicates if any
                if (count($existing_ids) > 1) {
                    $keep_id = $existing_ids[0];
                    array_shift($existing_ids);
                    foreach ($existing_ids as $dup_id) {
                        wp_delete_post($dup_id, true);
                    }
                    $post_id = $keep_id;
                    $updated++;
                } elseif (!empty($existing_ids)) {
                    // Update existing - update title first
                    $post_id = $existing_ids[0];
                    wp_update_post([
                        'ID' => $post_id,
                        'post_title' => $item['Account_Name']
                    ]);
                    $updated++;
                } else {
                    // Create new
                    $post_id = \SIT\Search\Modules\University::create_item($item);
                    $created++;
                }
                
                // Update ALL fields for this university (whether new or existing)
                if ($post_id) {
                    // Get all mapped fields
                    $fields = \SIT\Search\Modules\University::get_fields();
                    
                    foreach ($fields as $field) {
                        $field_name = $field['field_name'];
                        
                        // Handle University Image (File_Upload_1)
                        if ($field_name == 'File_Upload_1') {
                            if (isset($item['File_Upload_1']) && !empty($item['File_Upload_1'])) {
                                $file_id_api = $item['File_Upload_1'][0]['file_Id'];
                                $file_id = get_post_meta($post_id, 'file_id', true);
                                $current_image = get_post_meta($post_id, 'uni_image', true);
                                
                                // Always download if file_id changed or no valid image exists
                                if ($file_id != $file_id_api || empty($current_image) || $current_image === 'null') {
                                    update_post_meta($post_id, 'file_id', $file_id_api);
                                    $att_id = $item['File_Upload_1'][0]['attachment_Id'];
                                    $file_name = $item['File_Upload_1'][0]['file_Name'];
                                    
                                    error_log("SIT Sync: Downloading image for university {$post_id} - Attachment: {$att_id}");
                                    $image_path = $zoho->request_image('Attachments/' . $att_id, $file_name);
                                    
                                    if ($image_path) {
                                        update_post_meta($post_id, 'uni_image', $image_path);
                                        error_log("SIT Sync: ✅ Image saved: {$image_path}");
                                    } else {
                                        update_post_meta($post_id, 'uni_image', 'null');
                                        error_log("SIT Sync: ❌ Image download failed for university {$post_id}");
                                    }
                                }
                            } else {
                                // No image in Zoho, set to null
                                update_post_meta($post_id, 'uni_image', 'null');
                            }
                            continue;
                        }
                        
                        // Handle University Logo
                        if ($field_name == 'University_Logo' && isset($item['University_Logo']) && !empty($item['University_Logo'])) {
                            $file_id_api = $item['University_Logo'][0]['file_Id'];
                            $file_id = get_post_meta($post_id, 'file_logo__id', true);
                            $current_logo = get_post_meta($post_id, 'uni_logo', true);
                            
                            if ($file_id != $file_id_api || empty($current_logo) || $current_logo === 'null') {
                                update_post_meta($post_id, 'file_logo__id', $item['University_Logo'][0]['file_Id']);
                                $att_id = $item['University_Logo'][0]['attachment_Id'];
                                $file_name = $item['University_Logo'][0]['file_Name'];
                                
                                $image_path = $zoho->request_image('Attachments/' . $att_id, $file_name);
                                if ($image_path) {
                                    update_post_meta($post_id, 'uni_logo', $image_path);
                                } else {
                                    update_post_meta($post_id, 'uni_logo', 'null');
                                }
                            }
                            continue;
                        }
                        
                        // Skip other special fields that are handled separately
                        if (in_array($field_name, ['Account_Name', 'University_Country', 'University_City'])) {
                            continue;
                        }
                        
                        // Update all other fields
                        if (isset($item[$field_name])) {
                            update_field($field_name, $item[$field_name], $post_id);
                        }
                    }
                    
                    $success++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }
        
        $message = "Synced {$success} universities ({$created} created, {$updated} updated)";
        if (!empty($country_filter)) {
            $message .= " from {$country_filter}";
        }
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped";
        }
        if ($errors > 0) {
            $message .= ", {$errors} failed";
        }
        
        // Add debug info about found countries
        if (!empty($debug_countries) && $success == 0) {
            $message .= ". Found countries in Zoho: " . implode(', ', array_slice($debug_countries, 0, 10));
        }
        
        return ['success' => true, 'message' => $message];
    }
    
    private static function syncPrograms($zoho, $country_filter = '')
    {
        $module = \SIT\Search\Modules\Program::$module ?? 'Products';
        
        // Build Zoho API criteria for country filter to save API credits
        $criteria = '';
        if (!empty($country_filter)) {
            // Zoho API format: (Country:equals:Spain)
            $criteria = "(Country:equals:{$country_filter})";
        }
        
        $items = self::fetchAllFromZoho($zoho, $module, 10, $criteria);
        
        $success = 0;
        $updated = 0;
        $created = 0;
        $errors = 0;
        $skipped = 0;
        $debug_countries = [];
        
        global $wpdb;
        
        $total_items = count($items);
        $processed = 0;
        
        foreach ($items as $item) {
            $processed++;
            
            // Flush output every 10 items to prevent browser timeout
            if ($processed % 10 === 0) {
                echo "<!-- Processing {$processed}/{$total_items} programs -->";
                if (function_exists('wp_ob_end_flush_all')) {
                    wp_ob_end_flush_all();
                }
                flush();
            }
            // Filter by country if specified
            if (!empty($country_filter)) {
                // Try different possible field structures
                $prog_country = $item['Country']['name'] ?? 
                               $item['Country']['Name'] ?? 
                               $item['University_Country']['Name'] ?? 
                               $item['University_Country']['name'] ?? 
                               '';
                
                // Collect unique country names for debugging
                if (!empty($prog_country) && !in_array($prog_country, $debug_countries)) {
                    $debug_countries[] = $prog_country;
                }
                
                if (strcasecmp($prog_country, $country_filter) !== 0) {
                    $skipped++;
                    continue;
                }
            }
            
            try {
                $zoho_id = $item['id'];
                
                // Check if program already exists
                $existing_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = 'zoho_product_id' 
                    AND meta_value = %s",
                    $zoho_id
                ));
                
                // Remove duplicates if any
                if (count($existing_ids) > 1) {
                    $keep_id = $existing_ids[0];
                    array_shift($existing_ids);
                    foreach ($existing_ids as $dup_id) {
                        wp_delete_post($dup_id, true);
                    }
                    $post_id = $keep_id;
                    $updated++;
                } elseif (!empty($existing_ids)) {
                    // Update existing
                    $post_id = $existing_ids[0];
                    wp_update_post([
                        'ID' => $post_id,
                        'post_title' => $item['Product_Name'] ?? $item['Name'] ?? ''
                    ]);
                    $updated++;
                } else {
                    // Create new
                    $post_id = \SIT\Search\Modules\Program::create_item($item);
                    $created++;
                }
                
                // Update ALL fields for this program
                if ($post_id) {
                    $fields = \SIT\Search\Modules\Program::get_fields();
                    
                    foreach ($fields as $field) {
                        $field_name = $field['field_name'];
                        
                        // Handle Program Image (File_Upload_1)
                        if ($field_name == 'File_Upload_1' && isset($item['File_Upload_1']) && !empty($item['File_Upload_1'])) {
                            $file_id_api = $item['File_Upload_1'][0]['file_Id'];
                            $file_id = get_post_meta($post_id, 'program_file_id', true);
                            $current_image = get_post_meta($post_id, 'program_image', true);
                            
                            if ($file_id != $file_id_api || empty($current_image) || $current_image === 'null') {
                                update_post_meta($post_id, 'program_file_id', $item['File_Upload_1'][0]['file_Id']);
                                $att_id = $item['File_Upload_1'][0]['attachment_Id'];
                                $file_name = $item['File_Upload_1'][0]['file_Name'];
                                
                                $image_path = $zoho->request_image('Attachments/' . $att_id, $file_name);
                                if ($image_path) {
                                    update_post_meta($post_id, 'program_image', $image_path);
                                } else {
                                    update_post_meta($post_id, 'program_image', 'null');
                                }
                            }
                            continue;
                        }
                        
                        // Skip special fields
                        if (in_array($field_name, ['Product_Name', 'Name', 'University', 'Country'])) {
                            continue;
                        }
                        
                        if (isset($item[$field_name])) {
                            update_field($field_name, $item[$field_name], $post_id);
                        }
                    }
                    
                    $success++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }
        
        $message = "Synced {$success} programs ({$created} created, {$updated} updated)";
        if (!empty($country_filter)) {
            $message .= " from {$country_filter}";
        }
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped";
        }
        if ($errors > 0) {
            $message .= ", {$errors} failed";
        }
        
        // Add debug info about found countries
        if (!empty($debug_countries) && $success == 0) {
            $message .= ". Found countries in Zoho: " . implode(', ', array_slice($debug_countries, 0, 10));
        }
        
        return ['success' => true, 'message' => $message];
    }
    
    private static function syncDegrees($zoho)
    {
        $module = \SIT\Search\Modules\Degree::$module ?? 'Degrees';
        $items = self::fetchAllFromZoho($zoho, $module);
        
        $success = 0;
        $updated = 0;
        $created = 0;
        
        foreach ($items as $item) {
            try {
                $zoho_id = $item['id'];
                $existing_terms = get_terms([
                    'taxonomy' => 'sit-degree',
                    'meta_key' => 'zoho_degree_id',
                    'meta_value' => $zoho_id,
                    'hide_empty' => false
                ]);
                
                if (!empty($existing_terms) && !is_wp_error($existing_terms)) {
                    $term = $existing_terms[0];
                    wp_update_term($term->term_id, 'sit-degree', ['name' => $item['Name'] ?? '']);
                    $updated++;
                    $success++;
                } else {
                    $term_id = \SIT\Search\Modules\Degree::create_item($item);
                    if ($term_id) { $created++; $success++; }
                }
            } catch (\Exception $e) {}
        }
        
        return ['success' => true, 'message' => "Synced {$success} degrees ({$created} created, {$updated} updated)"];
    }
    
    private static function syncFaculties($zoho)
    {
        $module = \SIT\Search\Modules\Faculty::$module ?? 'Faculties';
        $items = self::fetchAllFromZoho($zoho, $module);
        
        $success = 0;
        $updated = 0;
        $created = 0;
        
        foreach ($items as $item) {
            try {
                $zoho_id = $item['id'];
                $existing_terms = get_terms([
                    'taxonomy' => 'sit-faculty',
                    'meta_key' => 'zoho_faculty_id',
                    'meta_value' => $zoho_id,
                    'hide_empty' => false
                ]);
                
                if (!empty($existing_terms) && !is_wp_error($existing_terms)) {
                    $term = $existing_terms[0];
                    wp_update_term($term->term_id, 'sit-faculty', ['name' => $item['Name'] ?? '']);
                    $updated++;
                    $success++;
                } else {
                    $term_id = \SIT\Search\Modules\Faculty::create_item($item);
                    if ($term_id) { $created++; $success++; }
                }
            } catch (\Exception $e) {}
        }
        
        return ['success' => true, 'message' => "Synced {$success} faculties ({$created} created, {$updated} updated)"];
    }
    
    private static function syncLanguages($zoho)
    {
        $module = \SIT\Search\Modules\Language::$module ?? 'Languages';
        $items = self::fetchAllFromZoho($zoho, $module);
        
        $success = 0;
        $updated = 0;
        $created = 0;
        
        foreach ($items as $item) {
            try {
                $zoho_id = $item['id'];
                $existing_terms = get_terms([
                    'taxonomy' => 'sit-language',
                    'meta_key' => 'zoho_language_id',
                    'meta_value' => $zoho_id,
                    'hide_empty' => false
                ]);
                
                if (!empty($existing_terms) && !is_wp_error($existing_terms)) {
                    $term = $existing_terms[0];
                    wp_update_term($term->term_id, 'sit-language', ['name' => $item['Name'] ?? '']);
                    $updated++;
                    $success++;
                } else {
                    $term_id = \SIT\Search\Modules\Language::create_item($item);
                    if ($term_id) { $created++; $success++; }
                }
            } catch (\Exception $e) {}
        }
        
        return ['success' => true, 'message' => "Synced {$success} languages ({$created} created, {$updated} updated)"];
    }
    
    private static function syncSpecialities($zoho)
    {
        $module = \SIT\Search\Modules\Speciality::$module ?? 'Specialities';
        $items = self::fetchAllFromZoho($zoho, $module);
        
        $success = 0;
        $updated = 0;
        $created = 0;
        
        foreach ($items as $item) {
            try {
                $zoho_id = $item['id'];
                $existing_terms = get_terms([
                    'taxonomy' => 'sit-speciality',
                    'meta_key' => 'zoho_speciality_id',
                    'meta_value' => $zoho_id,
                    'hide_empty' => false
                ]);
                
                if (!empty($existing_terms) && !is_wp_error($existing_terms)) {
                    $term = $existing_terms[0];
                    wp_update_term($term->term_id, 'sit-speciality', ['name' => $item['Name'] ?? '']);
                    $updated++;
                    $success++;
                } else {
                    $term_id = \SIT\Search\Modules\Speciality::create_item($item);
                    if ($term_id) { $created++; $success++; }
                }
            } catch (\Exception $e) {}
        }
        
        return ['success' => true, 'message' => "Synced {$success} specialities ({$created} created, {$updated} updated)"];
    }
    
    private static function fetchAllFromZoho($zoho, $module, $max_pages = 10, $criteria = '')
    {
        $all_items = [];
        $page = 1;
        $per_page = 200;
        
        do {
            // Build URL with optional criteria filter
            $url = "{$module}?page={$page}&per_page={$per_page}";
            if (!empty($criteria)) {
                $url .= "&criteria=" . urlencode($criteria);
            }
            
            $response = $zoho->request($url);
            
            if (isset($response['data']) && is_array($response['data'])) {
                $all_items = array_merge($all_items, $response['data']);
                $has_more = isset($response['info']['more_records']) && $response['info']['more_records'];
                $page++;
                
                // Safety limit: stop after max_pages to prevent timeout
                if ($page > $max_pages) {
                    error_log("SIT Sync: Reached max pages limit ({$max_pages}) for {$module}");
                    break;
                }
                
                if (!$has_more) {
                    break;
                }
            } else {
                break;
            }
        } while (true);
        
        error_log("SIT Sync: Fetched " . count($all_items) . " items from {$module}");
        return $all_items;
    }
}
