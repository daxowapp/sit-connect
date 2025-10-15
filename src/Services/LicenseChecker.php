<?php

namespace SIT\Search\Services;

/**
 * License Checker Service
 * Validates and checks license status
 */
class LicenseChecker
{
    private static $instance = null;
    private $license_server_url = 'https://your-license-server.com/api';

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if license is active
     * 
     * @return bool
     */
    public function isLicenseActive()
    {
        // Check Freemius first
        if (function_exists('sc_fs') && sc_fs() !== null) {
            $freemius = sc_fs();
            // If Freemius is registered (free or paid), license is active
            if ($freemius->is_registered()) {
                return true;
            }
        }
        
        // Fallback to custom license
        $status = get_option('sit_connect_license_status', 'inactive');
        
        // Check if we need to verify with server (every 7 days)
        $last_check = get_option('sit_connect_license_last_check', 0);
        $current_time = time();
        
        if ($status === 'active' && ($current_time - $last_check) > (7 * 24 * 60 * 60)) {
            $this->verifyLicenseWithServer();
        }
        
        return get_option('sit_connect_license_status', 'inactive') === 'active';
    }

    /**
     * Verify license with remote server
     * 
     * @return bool
     */
    public function verifyLicenseWithServer()
    {
        $license_key = get_option('sit_connect_license_key', '');
        
        if (empty($license_key)) {
            update_option('sit_connect_license_status', 'inactive');
            return false;
        }

        $response = wp_remote_post($this->license_server_url . '/verify', [
            'body' => [
                'license_key' => $license_key,
                'domain' => home_url()
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            // If we can't reach server, keep current status but log the check
            update_option('sit_connect_license_last_check', time());
            return $this->isLicenseActive();
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['valid']) && $body['valid']) {
            update_option('sit_connect_license_status', 'active');
            update_option('sit_connect_license_last_check', time());
            return true;
        } else {
            update_option('sit_connect_license_status', 'inactive');
            update_option('sit_connect_license_last_check', time());
            return false;
        }
    }

    /**
     * Show admin notice if license is not active
     */
    public function showLicenseNotice()
    {
        if (!$this->isLicenseActive() && current_user_can('manage_options')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <strong><?php _e('SIT Connect License Required', SIT_SEARCH_TEXT_DOMAIN); ?></strong><br>
                        <?php _e('Please activate your license to use SIT Connect plugin.', SIT_SEARCH_TEXT_DOMAIN); ?>
                        <a href="<?php echo admin_url('admin.php?page=sit-connect-settings&tab=license'); ?>"><?php _e('Activate Now', SIT_SEARCH_TEXT_DOMAIN); ?></a>
                    </p>
                </div>
                <?php
            });
        }
    }

    /**
     * Disable plugin functionality if license is not active
     * (Optional - use this if you want to completely block functionality)
     * 
     * @return bool
     */
    public function checkLicenseOrDie()
    {
        if (!$this->isLicenseActive()) {
            wp_die(
                __('SIT Connect requires an active license. Please contact your administrator.', SIT_SEARCH_TEXT_DOMAIN),
                __('License Required', SIT_SEARCH_TEXT_DOMAIN),
                ['response' => 403]
            );
        }
        return true;
    }

    /**
     * Get license information
     * 
     * @return array
     */
    public function getLicenseInfo()
    {
        return [
            'key' => get_option('sit_connect_license_key', ''),
            'status' => get_option('sit_connect_license_status', 'inactive'),
            'email' => get_option('sit_connect_license_email', ''),
            'activated_at' => get_option('sit_connect_license_activated_at', 0),
            'last_check' => get_option('sit_connect_license_last_check', 0)
        ];
    }
}
