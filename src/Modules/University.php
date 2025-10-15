<?php

namespace SIT\Search\Modules;

use SIT\Search\Services\Functions;
use SIT\Search\Services\Module;
use SIT\Search\Services\Zoho;

class University extends Module
{
    protected static $module = 'Accounts';

    public static function sync(int $page = 1, int $per_page = 1, int $limit = 1)
    {
        $fields = self::get_fields();

        foreach ($fields as $field) {
            Functions::create_field_if_needed($field, 'group_67936c1053468');
        }

        do {
            $items = self::get_items($page, $per_page);

            foreach ($items['data'] as $item) {
                if (!$item['Active_in_Search']) continue;
                $zoho_id = $item['id'];
                $university_name = $item['Account_Name'];

                // Find existing post by Zoho ID
                global $wpdb;
                $existing_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = 'zoho_account_id' 
                    AND meta_value = %s",
                    $zoho_id
                ));

                // Remove duplicates if any exist (keep only first one)
                if (count($existing_ids) > 1) {
                    error_log("SIT Sync: Found " . count($existing_ids) . " duplicates for '{$university_name}' - removing extras");
                    $keep_id = $existing_ids[0];
                    array_shift($existing_ids); // Remove first from array
                    foreach ($existing_ids as $duplicate_id) {
                        wp_delete_post($duplicate_id, true);
                    }
                    $post_id = $keep_id;
                } elseif (!empty($existing_ids)) {
                    // Found existing post - update it
                    $post_id = $existing_ids[0];
                    error_log("SIT Sync: Updating existing post ID {$post_id} for '{$university_name}' (Zoho ID: {$zoho_id})");
                    
                    // Update post title if changed
                    wp_update_post([
                        'ID' => $post_id,
                        'post_title' => $university_name
                    ]);
                } else {
                    // No existing post - create new
                    error_log("SIT Sync: Creating new post for '{$university_name}' (Zoho ID: {$zoho_id})");
                    $post_id = self::create_item($item);
                    
                    if ($post_id) {
                        error_log("SIT Sync: Created post ID {$post_id} for '{$university_name}'");
                    }
                }
                
                // Update all fields for this post
                if ($post_id) {
                    $fields = self::get_fields();

                    foreach ($fields as $field) {

                        if ($field['field_name'] == 'University_Country' && isset($item['University_Country']['id'])) {

                            $country_zoho_id = $item['University_Country']['id'];

                            $args = array(
                                'meta_key' => 'zoho_country_id',
                                'meta_value' => $country_zoho_id,
                                'taxonomy' => 'sit-country',
                                'hide_empty' => false,
                            );

                            $country = get_terms($args);

                            $country = $country ? reset($country) : null;

                            wp_set_object_terms($post_id, null, 'sit-country');

                            if (!$country) {
                                $country = wp_insert_term($item['University_Country']['Name'], 'sit-country');
                                update_term_meta($country['term_id'], 'zoho_country_id', $country_zoho_id);

                                wp_set_object_terms($post_id, $country['term_id'], 'sit-country');
                            } else {
                                wp_set_object_terms($post_id, $country->term_id, 'sit-country');
                            }

                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'University_City' && isset($item['University_City']['id'])) {

                            $city_zoho_id = $item['University_City']['id'];

                            $args = array(
                                'meta_key' => 'zoho_city_id',
                                'meta_value' => $city_zoho_id,
                                'taxonomy' => 'sit-city',
                                'hide_empty' => false,
                            );

                            $city = get_terms($args);

                            $city = $city ? reset($city) : null;

                            wp_set_object_terms($post_id, null, 'sit-city');

                            if (!$city) {
                                $city = wp_insert_term($item['University_City']['Name'], 'sit-city');
                                update_term_meta($city['term_id'], 'zoho_city_id', $city_zoho_id);

                                wp_set_object_terms($post_id, $city['term_id'], 'sit-city');
                            } else {
                                wp_set_object_terms($post_id, $city->term_id, 'sit-city');
                            }

                            update_field($field['field_name'], $item[$field['field_name']]['name'], $post_id);
                            continue;
                        }

                        if ($field['field_name'] == 'File_Upload_1' && isset($item['File_Upload_1']) && !empty($item['File_Upload_1'])) {
                            $file_id_api = $item['File_Upload_1'][0]['file_Id'];
                            $file_id = get_post_meta($post_id, 'file_id', true);
                            $current_image = get_post_meta($post_id, 'uni_image', true);
                            
                            // Download image if: file_id changed OR no image exists OR image is 'null'
                            if ($file_id != $file_id_api || empty($current_image) || $current_image === 'null') {
                                update_post_meta($post_id, 'file_id', $item['File_Upload_1'][0]['file_Id']);
                                $att_id = $item['File_Upload_1'][0]['attachment_Id'];
                                $file_name = $item['File_Upload_1'][0]['file_Name'];
                                
                                error_log("SIT Sync: Downloading image for university ID {$post_id} - Attachment ID: {$att_id}");
                                $image_path = (new Zoho())->request_image('Attachments/' . $att_id, $file_name);

                                if ($image_path) {
                                    update_post_meta($post_id, 'uni_image', $image_path);
                                    error_log("SIT Sync: ✅ Image saved: {$image_path}");
                                } else {
                                    update_post_meta($post_id, 'uni_image', 'null');
                                    error_log("SIT Sync: ❌ Image download failed for university ID {$post_id}");
                                }
                            }
                            continue;
                        }

                        if ($field['field_name'] == 'University_Logo' && isset($item['University_Logo']) && !empty($item['University_Logo'])) {
                            $file_id_api = $item['University_Logo'][0]['file_Id'];
                            $file_id = get_post_meta($post_id, 'file_logo__id', true);
                            $current_logo = get_post_meta($post_id, 'uni_logo', true);
                            
                            // Download logo if: file_id changed OR no logo exists OR logo is 'null'
                            if ($file_id != $file_id_api || empty($current_logo) || $current_logo === 'null') {
                                update_post_meta($post_id, 'file_logo__id', $item['University_Logo'][0]['file_Id']);
                                $att_id = $item['University_Logo'][0]['attachment_Id'];
                                $file_name = $item['University_Logo'][0]['file_Name'];
                                
                                error_log("SIT Sync: Downloading logo for university ID {$post_id} - Attachment ID: {$att_id}");
                                $image_path = (new Zoho())->request_image('Attachments/' . $att_id, $file_name);

                                if ($image_path) {
                                    update_post_meta($post_id, 'uni_logo', $image_path);
                                    error_log("SIT Sync: ✅ Logo saved: {$image_path}");
                                } else {
                                    update_post_meta($post_id, 'uni_logo', 'null');
                                    error_log("SIT Sync: ❌ Logo download failed for university ID {$post_id}");
                                }
                            }
                            continue;
                        }

                        if (isset($item[$field['field_name']])) {
                            update_field($field['field_name'], $item[$field['field_name']], $post_id);
                        }
                    }
                }
            }

            $page++;
        } while ($items['info']['more_records'] && $page <= $limit);
    }

    public static function create_item($data)
    {
        $post_id = wp_insert_post([
            'post_title' => $data['Account_Name'],
            'post_type' => 'sit-university',
            'post_status' => 'publish'
        ]);

        if ($post_id) {
            // Set Zoho ID using both ACF and direct meta (to ensure it's saved)
            update_field('zoho_account_id', $data['id'], $post_id);
            update_post_meta($post_id, 'zoho_account_id', $data['id']);
            
            // Also set as ACF field with underscore prefix (ACF internal)
            update_post_meta($post_id, '_zoho_account_id', 'field_' . md5('zoho_account_id'));
        }

        $fields = self::get_fields();

        if (!empty($fields) && is_array($fields)) {
            foreach ($fields as $field) {

                if ($field['field_name'] == 'University_Country' && isset($data['University_Country']['id'])) {

                    $country_zoho_id = $data['University_Country']['id'];

                    $args = array(
                        'meta_key' => 'zoho_country_id',
                        'meta_value' => $country_zoho_id,
                        'taxonomy' => 'sit-country',
                        'hide_empty' => false,
                    );

                    $country = get_terms($args);

                    $country = $country ? reset($country) : null;

                    wp_set_object_terms($post_id, null, 'sit-country');

                    if (!$country) {
                        $country = wp_insert_term($data['University_Country']['Name'], 'sit-country');
                        update_term_meta($country['term_id'], 'zoho_country_id', $country_zoho_id);

                        wp_set_object_terms($post_id, $country['term_id'], 'sit-country');
                    } else {
                        wp_set_object_terms($post_id, $country->term_id, 'sit-country');
                    }

                    update_field($field['field_name'], $data[$field['field_name']]['name'], $post_id);
                    continue;
                }

                if ($field['field_name'] == 'University_City' && isset($data['University_City']['id'])) {

                    $city_zoho_id = $data['University_City']['id'];

                    $args = array(
                        'meta_key' => 'zoho_city_id',
                        'meta_value' => $city_zoho_id,
                        'taxonomy' => 'sit-city',
                        'hide_empty' => false,
                    );

                    $city = get_terms($args);

                    $city = $city ? reset($city) : null;

                    wp_set_object_terms($post_id, null, 'sit-city');

                    if (!$city) {
                        $city = wp_insert_term($data['University_City']['Name'], 'sit-city');
                        update_term_meta($city['term_id'], 'zoho_city_id', $city_zoho_id);

                        wp_set_object_terms($post_id, $city['term_id'], 'sit-city');
                    } else {
                        wp_set_object_terms($post_id, $city->term_id, 'sit-city');
                    }

                    update_field($field['field_name'], $data[$field['field_name']]['name'], $post_id);
                    continue;
                }

                if ($field['field_name'] == 'File_Upload_1' && isset($data['File_Upload_1'])) {
                    $file_id_api=$data['File_Upload_1'][0]['file_Id'];
                    $file_id=get_post_meta($post_id ,'file_id',true);
                    if($file_id != $file_id_api){
                        update_post_meta($post_id ,'file_id',$data['File_Upload_1'][0]['file_Id']);
                        $att_id=$data['File_Upload_1'][0]['attachment_Id'];
                        $file_name=$data['File_Upload_1'][0]['file_Name'];
                        $image_path = (new Zoho())->request_image('Attachments/'.$att_id, $file_name);

                        if ($image_path) {
                            update_post_meta($post_id ,'uni_image',$image_path);
                        } else {
                            update_post_meta($post_id ,'uni_image','null');
                        }
                    }
                    continue;
                }

                if ($field['field_name'] == 'University_Logo' && isset($data['University_Logo'])) {
                    $file_id_api=$data['University_Logo'][0]['file_Id'];
                    $file_id=get_post_meta($post_id ,'file_logo__id',true);
                    if($file_id != $file_id_api){
                        update_post_meta($post_id ,'file_logo__id',$data['University_Logo'][0]['file_Id']);
                        $att_id=$data['University_Logo'][0]['attachment_Id'];
                        $file_name=$data['University_Logo'][0]['file_Name'];
                        $image_path = (new Zoho())->request_image('Attachments/'.$att_id, $file_name);

                        if ($image_path) {
                            update_post_meta($post_id ,'uni_logo',$image_path);
                        } else {
                            update_post_meta($post_id ,'uni_logo','null');
                        }
                    }
                    continue;
                }


                if (isset($data[$field['field_name']])) {
                    update_field($field['field_name'], $data[$field['field_name']], $post_id);
                }
            }
        }

        return $post_id;
    }

    public static function get_enabled_fields()
    {
        return get_option('university_enabled_fields', []);
    }

    public static function get_item($post_id, $type = 'post'): array|\WP_Post|null
    {
        if ($type == 'post') {
            return get_post($post_id);
        } else {
            $zoho_id = get_field('zoho_account_id', $post_id);

            return $zoho_id ? (new Zoho())->request('Accounts/' . $zoho_id) : null;
        }
    }
}