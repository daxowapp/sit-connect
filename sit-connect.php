<?php

/**
 * Plugin Name: SIT Connect
 * Plugin URI: https://sitconnect.com
 * Description: A powerful university and program management plugin with CRM integration, customizable colors, and advanced search capabilities. Perfect for educational institutions and study abroad agencies.
 * Version: 2.0.0
 * Author: Sabeeh
 * Author URI: https://sabeeh.dev
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sit-connect
 * Domain Path: /languages
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Requires Plugins:
 */

if (!defined('ABSPATH')) {
    exit;
}

// Freemius Integration (Optional - only loads if SDK is installed)
if (!function_exists('sc_fs')) {
    // Create a helper function for easy SDK access.
    function sc_fs() {
        global $sc_fs;

        if (!isset($sc_fs)) {
            // Check if Freemius SDK exists before loading
            $freemius_path = dirname(__FILE__) . '/freemius/start.php';
            
            if (file_exists($freemius_path)) {
                // Include Freemius SDK.
                require_once $freemius_path;
                
                $sc_fs = fs_dynamic_init(array(
                    'id'                  => '21157',
                    'slug'                => 'sit-connect',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_889390a925a9663528fbb1bdcbb74',
                    'is_premium'          => false,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'menu'                => array(
                        'slug'           => 'sit-connect',
                        'account'        => false,
                        'contact'        => false,
                        'support'        => false,
                    ),
                ));
            } else {
                // Freemius SDK not installed - return null
                $sc_fs = null;
            }
        }

        return $sc_fs;
    }

    // Init Freemius only if SDK exists.
    if (file_exists(dirname(__FILE__) . '/freemius/start.php')) {
        sc_fs();
        // Signal that SDK was initiated.
        do_action('sc_fs_loaded');
    }
}

// Plugin Constants
define('SIT_CONNECT_VERSION', '2.0.0');
define('SIT_CONNECT_FILE', __FILE__);
define('SIT_CONNECT_DIR', plugin_dir_path(__FILE__));
define('SIT_CONNECT_URL', plugin_dir_url(__FILE__));
define('SIT_CONNECT_BASENAME', plugin_basename(__FILE__));

// Legacy constants for backward compatibility
define('STI_SEARCH_VERSION', SIT_CONNECT_VERSION);
define('STI_SEARCH_DIR', SIT_CONNECT_DIR);
define('STI_SEARCH_URL', SIT_CONNECT_URL);
define('SIT_SEARCH_TEXT_DOMAIN', 'sit-connect');
define('SIT_SEARCH_ASSETS', SIT_CONNECT_URL . 'assets/');

// Stripe Keys (move to settings or environment variables in production)
if (!defined('STRIPE_PUBLIC_KEY')) {
    define('STRIPE_PUBLIC_KEY', get_option('sit_connect_stripe_public_key', ''));
}
if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', get_option('sit_connect_stripe_secret_key', ''));
}

require 'vendor/autoload.php';

// GitHub Auto-Update System
// Check for updates from GitHub repository
add_action('plugins_loaded', 'sit_connect_init_github_updater');

function sit_connect_init_github_updater() {
    // Check if Plugin Update Checker library exists
    $puc_path = SIT_CONNECT_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
    
    if (file_exists($puc_path)) {
        require $puc_path;
        
        // Initialize the update checker
        $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
            'https://github.com/daxowapp/sit-connect', // Your GitHub repo URL
            SIT_CONNECT_FILE, // Full path to the main plugin file
            'sit-connect' // Plugin slug
        );
        
        // Set the branch to check for updates (optional, defaults to 'main')
        $myUpdateChecker->setBranch('main');
        
        // For private repositories, use GitHub token from wp-config.php
        if (defined('SIT_CONNECT_GITHUB_TOKEN')) {
            $myUpdateChecker->setAuthentication(SIT_CONNECT_GITHUB_TOKEN);
        }
        
        // Optional: Enable release assets (if you want to use release assets instead of source code)
        // $myUpdateChecker->getVcsApi()->enableReleaseAssets();
    }
}

add_action('plugins_loaded', 'sit_search');

function sit_search()
{
    // Always load the app (needed for settings page)
    return \SIT\Search\App::get_instance();
}

// Check license and block frontend/features if not valid
add_action('init', 'sit_connect_check_license', 1);

function sit_connect_check_license() {
    // Check if license is valid
    $has_valid_license = false;
    
    // Check Freemius license
    if (function_exists('sc_fs') && sc_fs() !== null) {
        // Freemius is installed - check if user has active license
        $freemius = sc_fs();
        
        // Allow free plan, trial, premium, or paying users
        if ($freemius->is_registered() || $freemius->is_paying() || $freemius->is_trial() || $freemius->is_premium()) {
            $has_valid_license = true;
        } else {
            // Freemius installed but no active license
            $has_valid_license = false;
        }
    } else {
        // No Freemius - check custom license
        $license_status = get_option('sit_connect_license_status', 'inactive');
        if ($license_status === 'active') {
            $has_valid_license = true;
        }
    }
    
    // If no valid license, block everything except settings page
    if (!$has_valid_license) {
        // Show admin notice
        add_action('admin_notices', 'sit_connect_license_required_notice');
        
        // Block all shortcodes
        add_action('wp_loaded', 'sit_connect_block_shortcodes', 999);
        
        // Block frontend features
        add_action('template_redirect', 'sit_connect_block_frontend');
    }
}

// Block all shortcodes if no license
function sit_connect_block_shortcodes() {
    $shortcodes = [
        'sit_search_bar', 'sit_top_universities', 'trending_study_areas',
        'sit_university_countries', 'filter_sort', 'bread_crump',
        'single_program', 'single_univesity', 'program_steps',
        'apply_now', 'universities', 'campus_faculties',
        'university_program', 'program_archive', 'search_program',
        'university_grid', 'ai_search_admin'
    ];
    
    foreach ($shortcodes as $shortcode) {
        remove_shortcode($shortcode);
        add_shortcode($shortcode, 'sit_connect_license_required_shortcode');
    }
}

// Shortcode replacement when no license
function sit_connect_license_required_shortcode() {
    if (current_user_can('manage_options')) {
        return '<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; text-align: center;">
            <strong>SIT Connect - License Required</strong><br>
            This feature requires an active license. 
            <a href="' . admin_url('admin.php?page=sit-connect-settings') . '" style="color: #0073aa;">Activate License</a>
        </div>';
    }
    return '';
}

// Block frontend custom post types if no license
function sit_connect_block_frontend() {
    if (is_singular(['sit-program', 'sit-university', 'sit-campus']) || 
        is_post_type_archive(['sit-program', 'sit-university', 'sit-campus'])) {
        
        if (current_user_can('manage_options')) {
            wp_die(
                '<h1>License Required</h1>
                <p>SIT Connect requires an active license to display this content.</p>
                <p><a href="' . admin_url('admin.php?page=sit-connect-settings') . '">Activate License</a></p>',
                'License Required',
                ['response' => 403, 'back_link' => true]
            );
        } else {
            wp_die(
                '<h1>Content Unavailable</h1>
                <p>This content is currently unavailable. Please contact the site administrator.</p>',
                'Content Unavailable',
                ['response' => 403, 'back_link' => true]
            );
        }
    }
}

// Show license required notice
function sit_connect_license_required_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Don't show on settings page
    if (isset($_GET['page']) && $_GET['page'] === 'sit-connect-settings') {
        return;
    }
    
    // Check if Freemius is registered (don't show if already registered)
    if (function_exists('sc_fs') && sc_fs() !== null) {
        $freemius = sc_fs();
        if ($freemius->is_registered()) {
            return; // Already registered, don't show notice
        }
    }
    
    ?>
    <div class="notice notice-error is-dismissible">
        <p>
            <strong><?php _e('SIT Connect - License Required', 'sit-connect'); ?></strong><br>
            <?php _e('This plugin requires an active license to function. All features are currently disabled.', 'sit-connect'); ?>
        </p>
        <p>
            <a href="<?php echo admin_url('admin.php?page=sit-connect-settings'); ?>" class="button button-primary">
                <?php _e('Activate License Now', 'sit-connect'); ?>
            </a>
        </p>
    </div>
    <?php
}

// Admin notice if Freemius is not installed (for development)
add_action('admin_notices', 'sit_connect_freemius_notice');

function sit_connect_freemius_notice() {
    // Only show to admins and only if Freemius SDK is not installed
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (!file_exists(dirname(__FILE__) . '/freemius/start.php')) {
        // Check if user dismissed the notice
        if (get_option('sit_connect_freemius_notice_dismissed')) {
            return;
        }
        
        ?>
        <div class="notice notice-info is-dismissible" id="sit-connect-freemius-notice">
            <p>
                <strong><?php _e('SIT Connect - Development Mode', 'sit-connect'); ?></strong><br>
                <?php _e('Freemius SDK is not installed. The plugin will work normally, but licensing features are disabled.', 'sit-connect'); ?>
                <br>
                <?php _e('To enable licensing for commercial use, download Freemius SDK from', 'sit-connect'); ?> 
                <a href="https://github.com/Freemius/wordpress-sdk" target="_blank">GitHub</a> 
                <?php _e('and place it in the /freemius/ folder.', 'sit-connect'); ?>
            </p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=sit-connect-settings'); ?>" class="button button-primary">
                    <?php _e('Plugin Settings', 'sit-connect'); ?>
                </a>
                <button type="button" class="button button-secondary" onclick="sitConnectDismissNotice()">
                    <?php _e('Dismiss (Development Mode)', 'sit-connect'); ?>
                </button>
            </p>
        </div>
        <script>
        function sitConnectDismissNotice() {
            jQuery.post(ajaxurl, {
                action: 'sit_connect_dismiss_freemius_notice',
                nonce: '<?php echo wp_create_nonce('sit_connect_dismiss_notice'); ?>'
            }, function() {
                jQuery('#sit-connect-freemius-notice').fadeOut();
            });
        }
        </script>
        <?php
    }
}

// Handle notice dismissal
add_action('wp_ajax_sit_connect_dismiss_freemius_notice', 'sit_connect_dismiss_freemius_notice');

function sit_connect_dismiss_freemius_notice() {
    check_ajax_referer('sit_connect_dismiss_notice', 'nonce');
    
    if (current_user_can('manage_options')) {
        update_option('sit_connect_freemius_notice_dismissed', true);
        wp_send_json_success();
    }
    
    wp_send_json_error();
}

// Inject custom colors into frontend (direct hook as fallback)
add_action('wp_head', 'sit_connect_inject_colors', 9999);

function sit_connect_inject_colors() {
    $primary_color = get_option('sit_connect_primary_color', '#AA151B');
    $primary_dark_color = get_option('sit_connect_primary_dark_color', '#8B1116');
    $secondary_color = get_option('sit_connect_secondary_color', '#F1BF00');
    $accent_color = get_option('sit_connect_accent_color', '#C29900');
    
    // Calculate lighter shade for primary color
    $hex = str_replace('#', '', $primary_color);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $r = max(0, min(255, $r + 30));
    $g = max(0, min(255, $g + 30));
    $b = max(0, min(255, $b + 30));
    $primary_light_color = '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    
    ?>
    <style id="sit-connect-custom-colors">
        :root {
            /* Apply Primary Colors */
            --apply-primary: <?php echo esc_attr($primary_color); ?> !important;
            --apply-primary-dark: <?php echo esc_attr($primary_dark_color); ?> !important;
            --apply-secondary: <?php echo esc_attr($secondary_color); ?> !important;
            --apply-accent: <?php echo esc_attr($accent_color); ?> !important;
            
            /* University Primary Colors */
            --uni-primary: <?php echo esc_attr($primary_color); ?> !important;
            --uni-primary-light: <?php echo esc_attr($primary_light_color); ?> !important;
            --uni-primary-dark: <?php echo esc_attr($primary_dark_color); ?> !important;
            --uni-secondary: <?php echo esc_attr($secondary_color); ?> !important;
            --uni-accent: <?php echo esc_attr($accent_color); ?> !important;
            --uni-gradient: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_dark_color); ?> 100%) !important;
            
            /* Program Page Colors */
            --programPage-primary: <?php echo esc_attr($primary_color); ?> !important;
            --programPage-primary-dark: <?php echo esc_attr($primary_dark_color); ?> !important;
            --programPage-secondary: <?php echo esc_attr($secondary_color); ?> !important;
            --programPage-accent: <?php echo esc_attr($accent_color); ?> !important;
            --programPage-gradient: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_dark_color); ?> 100%) !important;
            
            /* Program Archive Page Colors */
            --ProgramArchivePage-primary: <?php echo esc_attr($primary_color); ?> !important;
            --ProgramArchivePage-primary-dark: <?php echo esc_attr($primary_dark_color); ?> !important;
            --ProgramArchivePage-primary-light: <?php echo esc_attr($primary_light_color); ?> !important;
            --ProgramArchivePage-secondary: <?php echo esc_attr($secondary_color); ?> !important;
            --ProgramArchivePage-accent: <?php echo esc_attr($accent_color); ?> !important;
        }
    </style>
    <?php
}

// Plugin activation hook for database table creation - temporarily disabled
// register_activation_hook(__FILE__, 'sit_search_activate');

/*
function sit_search_activate()
{
    // Create embeddings table on activation
    require_once 'vendor/autoload.php';
    
    // Create the table directly without instantiating the service
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'sit_program_embeddings';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        program_id bigint(20) NOT NULL,
        embedding longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY program_id (program_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
*/

function allow_svg_uploads($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_uploads');

add_filter('http_request_timeout', 'custom_http_request_timeout', 10, 1);

function custom_http_request_timeout($timeout_value)
{
    return 60;
}

add_action('wp_head', 'custom_add_meta_description_for_cpt');

function custom_add_meta_description_for_cpt()
{
    if (is_singular('sit-program')) {
        global $post;

        $current_uni_id = get_post_meta($post->ID, 'zh_university', true);
        $university = get_post($current_uni_id);
        $current_post_title = get_the_title($post->ID);
        $desc = get_post_meta($post->ID, 'Description', true);
        $keywords = get_post_meta($post->ID, 'Keywords', true);

        $description = $current_post_title . ' ' . $university->post_title . ' ' . $desc;
        $description = trim_description($description);
        
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
    }
}

function trim_description($text, $min = 150, $max = 160)
{
    $text = strip_tags($text);
    $text = trim($text);

    if (strlen($text) >= $min && strlen($text) <= $max) {
        return $text;
    }

    if (strlen($text) > $max) {
        $trimmed = substr($text, 0, $max);
        $lastSpace = strrpos($trimmed, ' ');
        return substr($trimmed, 0, $lastSpace) . '...';
    }

    return $text;
}

add_filter('rank_math/frontend/title', function ($title) {
    if (is_singular('sit-program')) {
        $id = get_the_ID();
        $current_uni_id = get_post_meta($id, 'zh_university', true);
        $university = get_post($current_uni_id);
        $custom_title = get_the_title() . ' ' . $university->post_title . ' - SIT Connect';
        return $custom_title;
    }

    return $title;
});
