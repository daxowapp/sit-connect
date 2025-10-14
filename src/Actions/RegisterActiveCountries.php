<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Hook;

class RegisterActiveCountries extends Hook
{
    public static array $hooks = ['acf/init'];

    public static int $priority = 10;

    public function __invoke()
    {
        if (function_exists('acf_add_options_page')) {
            
            // Add Active Countries settings page
            acf_add_options_sub_page(array(
                'page_title'  => 'Active Countries',
                'menu_title'  => 'Active Countries',
                'parent_slug' => 'sit-connect',
                'capability'  => 'manage_options',
                'menu_slug'   => 'sit-active-countries',
            ));
            
            // Register ACF fields for active countries
            if (function_exists('acf_add_local_field_group')) {
                acf_add_local_field_group(array(
                    'key' => 'group_active_countries',
                    'title' => 'Active Countries Settings',
                    'fields' => array(
                        array(
                            'key' => 'field_active_countries',
                            'label' => 'Active Countries',
                            'name' => 'active_countries',
                            'type' => 'taxonomy',
                            'instructions' => 'Select which countries should be active and displayed throughout the site. Only universities, programs, and cities from these countries will be shown.',
                            'required' => 0,
                            'taxonomy' => 'sit-country',
                            'field_type' => 'multi_select',
                            'allow_null' => 0,
                            'add_term' => 0,
                            'save_terms' => 0,
                            'load_terms' => 0,
                            'return_format' => 'id',
                            'multiple' => 1,
                        ),
                        array(
                            'key' => 'field_enable_country_filter',
                            'label' => 'Enable Country Filter',
                            'name' => 'enable_country_filter',
                            'type' => 'true_false',
                            'instructions' => 'Enable this to filter all data by the selected active countries. Disable to show data from all countries.',
                            'required' => 0,
                            'default_value' => 1,
                            'ui' => 1,
                            'ui_on_text' => 'Enabled',
                            'ui_off_text' => 'Disabled',
                        ),
                    ),
                    'location' => array(
                        array(
                            array(
                                'param' => 'options_page',
                                'operator' => '==',
                                'value' => 'sit-active-countries',
                            ),
                        ),
                    ),
                    'menu_order' => 0,
                    'position' => 'normal',
                    'style' => 'default',
                    'label_placement' => 'top',
                    'instruction_placement' => 'label',
                ));
            }
        }
    }
}
