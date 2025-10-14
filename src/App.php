<?php

namespace SIT\Search;

use SIT\Search\Actions\RegisterMenu;
use SIT\Search\Actions\RegisterSyncMenu;
use SIT\Search\Actions\RegisterActiveCountries;
use SIT\Search\Actions\RegisterResultsPage;
use SIT\Search\Actions\SearchProgramAjax;
use SIT\Search\Actions\GenerateEmbeddingsAjax;
use SIT\Search\Actions\GetCitiesByCountryAjax;
use SIT\Search\Actions\RegisterSettingsPage;
use SIT\Search\Actions\RegisterZohoCompatibility;
use SIT\Search\Actions\InjectCustomColors;
use SIT\Search\Modules\Campus;
use SIT\Search\Modules\City;
use SIT\Search\Modules\Country;
use SIT\Search\Modules\Degree;
use SIT\Search\Modules\Language;
use SIT\Search\Modules\Faculty;
use SIT\Search\Modules\Program;
use SIT\Search\Modules\Speciality;
use SIT\Search\Modules\Lead;
use SIT\Search\Modules\University;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;
use SIT\Search\Services\LicenseChecker;
use SIT\Search\Shortcodes\ApplyNow;
use SIT\Search\Shortcodes\SearchProgram;
use SIT\Search\Shortcodes\ProgramArchive;
use SIT\Search\Shortcodes\ProgramSteps;
use SIT\Search\Shortcodes\SearchBar;
use SIT\Search\Shortcodes\SingleUniversity;
use SIT\Search\Shortcodes\TopUniversities;
use SIT\Search\Shortcodes\TrendingStudyAreas;
use SIT\Search\Shortcodes\Universities;
use SIT\Search\Shortcodes\UniversityCountries;
use SIT\Search\Shortcodes\FilterSort;
use SIT\Search\Shortcodes\Breadcrump;
use SIT\Search\Shortcodes\Singleprogram;
use SIT\Search\Shortcodes\CampusFaculties;
use SIT\Search\Shortcodes\UniversityPrograms;
use SIT\Search\Shortcodes\UniversityGrid;
use SIT\Search\Shortcodes\AISearchAdmin;
use SIT\Search\Endpoints\ProgramEndpoint;
use SIT\Search\Endpoints\CountryEndpoint;
use SIT\Search\Endpoints\ProgramDataEndpoint;
use SIT\Search\Endpoints\CampusEndpoint;
use SIT\Search\Endpoints\UniversityDataEndpoint;
use SIT\Search\Endpoints\LanguageEndpoint;
use SIT\Search\Endpoints\DegreeEndpoint;
use SIT\Search\Endpoints\FacultyEndpoint;
use SIT\Search\Endpoints\SpecialityEndpoint;
use SIT\Search\Endpoints\CityEndpoint;

class App
{
    protected static $instance;
    /**
     * List of actions and their handlers
     *
     * @var array
     */
    protected array $actions = array(
        RegisterMenu::class,
        RegisterSyncMenu::class,
        RegisterActiveCountries::class,
        RegisterResultsPage::class,
        SearchProgramAjax::class,
        GenerateEmbeddingsAjax::class,
        GetCitiesByCountryAjax::class,
        RegisterSettingsPage::class,
        RegisterZohoCompatibility::class,
        InjectCustomColors::class,
    );

    /**
     * List of filters and their handlers
     *
     * @var array
     */
    protected array $filters = array();
    /**
     * List of shortcodes and their handlers
     *
     * @var array
     */
    protected array $shortcodes = array(
        'sit_search_bar' => SearchBar::class,
        'sit_top_universities' => TopUniversities::class,
        'trending_study_areas' => TrendingStudyAreas::class,
        'sit_university_countries' => UniversityCountries::class,
        'filter_sort' => FilterSort::class,
        'bread_crump' => Breadcrump::class,
        'single_program' => Singleprogram::class,
        'single_univesity' => SingleUniversity::class,
        'program_steps' => ProgramSteps::class,
        'apply_now' => ApplyNow::class,
        'universities' => Universities::class,
        'campus_faculties' => CampusFaculties::class,
        'university_program' => UniversityPrograms::class,
        'program_archive' => ProgramArchive::class,
        'search_program' => SearchProgram::class,
        'university_grid' => UniversityGrid::class,
        'ai_search_admin' => AISearchAdmin::class,
    );

    public function register_endpoints(): void
    {
        add_action('rest_api_init', function () {
            (new Endpoints\ProgramEndpoint)->register_routes();
            (new Endpoints\CountryEndpoint)->register_routes();
            (new Endpoints\ProgramDataEndpoint)->register_routes();
            (new Endpoints\CampusEndpoint)->register_routes();
            (new Endpoints\UniversityDataEndpoint)->register_routes();
            (new Endpoints\LanguageEndpoint)->register_routes();
            (new Endpoints\DegreeEndpoint)->register_routes();
            (new Endpoints\FacultyEndpoint)->register_routes();
            (new Endpoints\SpecialityEndpoint)->register_routes();
            (new Endpoints\CityEndpoint)->register_routes();
        });
    }

    public function __construct()
    {
        if (isset($_GET['debug_mode']) && $_GET['debug_mode'] == 1) {
            //show all errors
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }
        $this->register_endpoints();
        $this->setup_hooks();
        $this->setup_shortcodes();

        // Initialize license checker
        $license_checker = LicenseChecker::getInstance();
        $license_checker->showLicenseNotice();

        add_action('init', array($this, 'debugging'));

        add_action('wp_enqueue_scripts', array($this, 'setup_assets'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));


        add_filter('manage_edit-sit-program_columns', function ($columns) {
            $columns['university'] = __('University', SIT_SEARCH_TEXT_DOMAIN);
            $columns['sit_country'] = __('Country', SIT_SEARCH_TEXT_DOMAIN);
            $columns['sit_faculty'] = __('Faculty', SIT_SEARCH_TEXT_DOMAIN);
            $columns['sit_speciality'] = __('Speciality', SIT_SEARCH_TEXT_DOMAIN);

            $date_column = $columns['date'];
            unset($columns['date']);
            $columns['date'] = $date_column;
            return $columns;
        });

        add_filter('manage_edit-sit-campus_columns', function ($columns) {
            $columns['university'] = __('University', SIT_SEARCH_TEXT_DOMAIN);
            $date_column = $columns['date'];
            unset($columns['date']);
            $columns['date'] = $date_column;
            return $columns;
        });

        add_filter('manage_edit-sit-university_columns', function ($columns) {
            $columns['sit_program'] = __('Programs', SIT_SEARCH_TEXT_DOMAIN);
            $columns['sit_country'] = __('Country', SIT_SEARCH_TEXT_DOMAIN);
            $date_column = $columns['date'];
            unset($columns['date']);
            $columns['date'] = $date_column;
            return $columns;
        });

        add_action('manage_sit-university_posts_custom_column', function ($column, $post_id) {

            if ($column === 'sit_program') {
                $args = array(
                    'post_type' => 'sit-program',
                    'meta_query' => array(
                        array(
                            'key' => 'zh_university',
                            'value' => $post_id,
                            'compare' => '='
                        )
                    )
                );

                $query = new \WP_Query($args);
                $programs = $query->get_posts();

                if ($programs) {
                    $program_titles = array_map(function ($program) {
                        return '<strong><a class="row-title"  href="/wp-admin/post.php?post=' . $program->ID . '&action=edit">' . $program->post_title . '</a></strong>';
                    }, $programs);
                    echo join('<br>', $program_titles);
                } else {
                    echo __('No Programs', SIT_SEARCH_TEXT_DOMAIN);
                }

            }

            if ($column === 'sit_country') {
                $terms = get_the_terms($post_id, 'sit-country');
                echo $terms ? esc_html(join(', ', wp_list_pluck($terms, 'name'))) : __('No Country', SIT_SEARCH_TEXT_DOMAIN);
            }

        }, 10, 2);

        add_action('manage_sit-program_posts_custom_column', function ($column, $post_id) {
            if ($column === 'university') {
                $uni_post_id = get_post_meta($post_id, 'zh_university', true);
                if ($uni_post_id) {
                    $post_title = get_the_title($uni_post_id);
                    echo $post_title ?
                        '<strong><a class="row-title" href="/wp-admin/post.php?post=' . $uni_post_id . '&action=edit" aria-label="' . $post_title . '">' . $post_title . '</a></strong>'
                        : __('No Data', SIT_SEARCH_TEXT_DOMAIN);
                } else {
                    echo __('No Data', SIT_SEARCH_TEXT_DOMAIN);
                }
            }


            if ($column === 'sit_country') {
                $terms = get_the_terms($post_id, 'sit-country');
                echo $terms ? esc_html(join(', ', wp_list_pluck($terms, 'name'))) : __('No Country', SIT_SEARCH_TEXT_DOMAIN);
            }

            if ($column === 'sit_speciality') {
                $terms = get_the_terms($post_id, 'sit-speciality');
                echo $terms ? esc_html(join(', ', wp_list_pluck($terms, 'name'))) : __('No speciality', SIT_SEARCH_TEXT_DOMAIN);
            }


            if ($column === 'sit_faculty') {
                $terms = get_the_terms($post_id, 'sit-faculty');
                echo $terms ? esc_html(join(', ', wp_list_pluck($terms, 'name'))) : __('No faculty', SIT_SEARCH_TEXT_DOMAIN);
            }

        }, 10, 2);

        add_action('manage_sit-campus_posts_custom_column', function ($column, $post_id) {
            if ($column === 'university') {
                $uni_post_id = get_post_meta($post_id, 'zh_university', true);
                if ($uni_post_id) {
                    $post_title = get_the_title($uni_post_id);
                    echo $post_title ?
                        '<strong><a class="row-title" href="/wp-admin/post.php?post=' . $uni_post_id . '&action=edit" aria-label="' . $post_title . '">' . $post_title . '</a></strong>'
                        : __('No Data', SIT_SEARCH_TEXT_DOMAIN);
                } else {
                    echo __('No Data', SIT_SEARCH_TEXT_DOMAIN);
                }
            }

        }, 10, 2);
        add_filter('manage_edit-sit-campus_sortable_columns', function ($sortable_columns) {
            $sortable_columns['university'] = 'university';
            return $sortable_columns;
        });

        add_filter('manage_edit-sit-program_sortable_columns', function ($sortable_columns) {
            $sortable_columns['university'] = 'university';
            $sortable_columns['sit_country'] = 'sit_country';
            return $sortable_columns;
        });

        add_action('pre_get_posts', function ($query) {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }

            $orderby = $query->get('orderby');

            if ($orderby === 'university') {
                $query->set('meta_key', 'zh_university');
                $query->set('orderby', 'meta_value');
            }
        });


        /**
         * Registering Webhook Handlers
         * @var Webhook $webhook
         */
        (new Webhook())->registerHandlers([
            'university' => \SIT\Search\Handlers\University::class,
            'program' => \SIT\Search\Handlers\Program::class,
            'country' => \SIT\Search\Handlers\Country::class,
            'city' => \SIT\Search\Handlers\City::class,
            'speciality' => \SIT\Search\Handlers\Speciality::class,
            'degree' => \SIT\Search\Handlers\Degree::class,
            'faculty' => \SIT\Search\Handlers\Faculty::class,
            'language' => \SIT\Search\Handlers\Language::class,
            'campus' => \SIT\Search\Handlers\Campus::class,
            'speciality-del' => \SIT\Search\Handlers\SpecialityDel::class,
            'city-del' => \SIT\Search\Handlers\CityDel::class,
            'degree-del' => \SIT\Search\Handlers\DegreeDel::class,
            'faculty-del' => \SIT\Search\Handlers\FacultyDel::class,
            'language-del' => \SIT\Search\Handlers\LanguageDel::class,
            'campus-del' => \SIT\Search\Handlers\CampusDel::class,
            'country-del' => \SIT\Search\Handlers\CountryDel::class,
            'university-del' => \SIT\Search\Handlers\UniversityDel::class,
            'program-del' => \SIT\Search\Handlers\ProgramDel::class
        ]);
    }

    /**
     * Set up hooks
     */
    public function setup_hooks()
    {
        foreach ($this->actions as $handler) {
            foreach ($handler::$hooks as $action) {
                add_action($action, new $handler, $handler::$priority, $handler::$arguments);
            }
        }

        foreach ($this->filters as $handler) {
            foreach ($handler::$hooks as $filter) {
                add_filter($filter, new $handler, $handler::$priority, $handler::$arguments);
            }
        }
    }

    /**
     * Set up shortcodes
     */
    public function setup_shortcodes()
    {
        foreach ($this->shortcodes as $shortcode => $handler) {
            add_shortcode($shortcode, new $handler);
        }
    }

    /**
     * Get current instance of app
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function debugging()
    {

        $search = [];
        if (isset($_GET['speciality']) && !is_array($_GET['speciality'])) {
            $search['speciality'] = esc_attr($_GET['speciality']);
        }

        if (isset($_GET['country']) && !is_array($_GET['country'])) {
            $search['country'] = esc_attr($_GET['country']);
        }

        if (isset($_GET['level']) && !is_array($_GET['level'])) {
            $search['level'] = esc_attr($_GET['level']);
        }

        $recent_search = isset($_COOKIE['recent_search']) ? json_decode(stripslashes($_COOKIE['recent_search']), true) : [];

        if (!empty($search)) {
            if (!in_array($search, $recent_search)) {
                $recent_search[] = $search;
                setcookie('recent_search', json_encode($recent_search), time() + (86400 * 30), '/');
            }
        } else {
            setcookie('recent_search', json_encode($recent_search), time() + (86400 * 30), '/');
        }

        if (isset($_GET['expire_token'])) {
            delete_transient('zoho_access_token');
        }

        if (isset($_GET['debugging'])) {
//
            echo "<pre>";
            // City::sync(1, 200, 5000);
        //    print_r(\SIT\Search\Modules\City::get_fields());
//            echo 'inn';

//            $access_token = '1000.b3b16beb5faf580f87c1261b6cb070e8.45ea5a011689c1a4c8da982d0f2738b7';
//
//            $url = 'https://www.zohoapis.com/crm/v2/settings/fields?module=Products';
//
//            $ch = curl_init($url);
//
//            curl_setopt($ch, CURLOPT_HTTPHEADER, [
//                'Authorization: Zoho-oauthtoken ' . $access_token
//            ]);
//
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//            $response = curl_exec($ch);
//
//            curl_close($ch);
//
//            $response_data = json_decode($response, true);
//            print_r($response_data);

        //    print_r(\SIT\Search\Modules\University::get_fields());
//            \SIT\Search\Modules\University::sync(1,200,200);
                // $criteria = urlencode('(Country.name:equals:China)');
                // $sty = (new Zoho())->request("Products/search?criteria=$criteria");
                // print_r($sty);
        //    $sty=(new Zoho())->request('Campus/6421426000014151038/6421426000013599889');
        //    foreach($sty['data'] as $val) {
        //        $faid = $val['Faculty']['id'];
        //        $faname = $val['Faculty']['name'];
        //        echo $faid;
        //    }
//            $sty=(new Zoho())->request('Campus/6421426000014151038/6421426000013599889');
//            if ($sty['data']) {
//                $faculty_ids = [];
//                foreach($sty['data'] as $val){
//                    $faid=$val['Faculty']['id'];
//                    echo $faid;
//                    $faname=$val['Faculty']['name'];
//                    $argsdd = array(
//                        'meta_key' => 'zoho_faculty_id',
//                        'meta_value' => $faid,
//                        'taxonomy' => 'sit-faculty',
//                        'hide_empty' => false,
//                    );
//
//                    $faculty = get_terms($argsdd);
//
//                    $faculty = $faculty ? reset($faculty) : null;
//
//                    if (!$faculty) {
//                        $faculty = wp_insert_term($faname, 'sit-faculty');
//                        $faculty_ids[] = $faculty['term_id'];
//                    } else {
//                        $faculty_ids[] = $faculty->term_id;
//                    }
//                }
//                print_r($faculty_ids);
//            }
//            print_r((new Zoho())->request('Products/6421426000002314866'));
        //    Campus::sync(1,200,5000);
        //    Program::sync(1,200,5000);
        //    Faculty::sync(1, 200, 5000);
        //    City::sync(1, 200, 5000);

        //    for ($page = 15; $page <= 15; $page++) {
        //        echo $page;
        //        echo '<br>';
        //        Program::sync($page,10,100);
        //    }
//            Speciality::sync(1, 200, 1);

//            $args = array(
//                'post_type' => 'sit-program',
//                'posts_per_page' => -1,
//                'meta_query' => array(
//                    'relation' => 'AND',
//                    array(
//                        'key'     => 'zoho_product_id',
//                        'compare' => 'EXISTS',
//                    ),
//                    array(
//                        'relation' => 'OR',
//                        array(
//                            'key'     => 'Description',
//                            'value'   => '',
//                            'compare' => '='
//                        ),
//                        array(
//                            'key'     => 'Description',
//                            'compare' => 'NOT EXISTS'
//                        ),
//                    )
//                ),
//            );
//
//            $query = new \WP_Query($args);
//            $con=0;
//            if ($query->have_posts()) {
//                while ($query->have_posts()) {
//                    $query->the_post();
//                    $zoho_product_id = get_post_meta(get_the_ID(), 'zoho_product_id', true);
//                    echo '<p>' . esc_html($zoho_product_id) . '</p>';
//                    Program::up_program($zoho_product_id);
////                    $con++;
//                    update_post_meta(get_the_ID(),'ok_des','yes');
//                }
//                wp_reset_postdata();
//            } else {
//                echo 'No sit-program posts found with empty or missing Description.';
//            }
//            echo '<p>' . esc_html($con) . '</p>';


//            Program::up_program(6421426000001734319);
//            Degree::sync(1, 100, 100);
//                Campus::sync(1, 100, 100);
//            print_r(\SIT\Search\Modules\Program::get_items());
//            Language::sync(1, 100, 100);
//            $args = array(
//                'taxonomy'   => 'sit-country', // Replace with your taxonomy
//                'hide_empty' => false, // Set to true to exclude empty terms
//                'meta_query' => array(
//                    array(
//                        'key'   => 'zoho_country_id', // Replace with your meta key
//                        'value' => '6421426000000655279', // Replace with your meta value
//                        'compare' => '=' // Use '=', 'LIKE', '>', '<', etc. as needed
//                    ),
//                ),
//            );
//
//            $terms = get_terms($args);
//
//            if (!empty($terms) && !is_wp_error($terms)) {
//                foreach ($terms as $term) {
//                    echo '<p>' . esc_html($term->name) . '</p>';
//                }
//            } else {
//                echo 'No terms found.';
//            }

            exit();
        }
    }

    public function setup_assets()
    {
        //OwlCarousel2
        wp_enqueue_style('sit-owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.css');
        wp_enqueue_style('sit-owl-theme', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.css');
        wp_enqueue_script('sit-owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', ['jquery'], null, true);

        //select2
        wp_enqueue_style('sit-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
        wp_enqueue_script('sit-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], null, true);

        //sit-search.css
        wp_enqueue_style('sit-search', STI_SEARCH_URL . 'assets/css/sit-search.css?h=' . time());
        wp_enqueue_style('guides-css', STI_SEARCH_URL . 'assets/css/guides.css?h=' . time());
        wp_enqueue_script('sit-search', STI_SEARCH_URL . 'assets/js/main.js?h=' . time(), ['jquery'], null, true);
        wp_enqueue_script('ajax-js', 'https://code.jquery.com/jquery-3.6.4.min.js');
        wp_enqueue_script('guides-js', STI_SEARCH_URL . 'assets/js/guides.js?h=' . time(), ['jquery'], null, true);
        wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js', array(), null, true);
        wp_localize_script('sit-search', 'upd_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('program_search_nonce')
        ]);

    }


    public function admin_assets()
    {
        wp_enqueue_style('sit-search-admin', STI_SEARCH_URL . 'assets/css/sit-search-admin.css?h=' . time());
    }
}