<?php

namespace SIT\Search\Actions;

/**
 * Register Settings Page for SIT Connect
 * Allows users to customize colors and manage license
 */
class RegisterSettingsPage
{
    public static $hooks = ['admin_menu'];
    public static $priority = 99;
    public static $arguments = 0;

    public function __invoke()
    {
        add_submenu_page(
            'sit-connect',
            __('Settings', SIT_SEARCH_TEXT_DOMAIN),
            __('Settings', SIT_SEARCH_TEXT_DOMAIN),
            'manage_options',
            'sit-connect-settings',
            [$this, 'render_settings_page']
        );

        // Register settings
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings()
    {
        // Color Settings
        register_setting('sit_connect_colors', 'sit_connect_primary_color', [
            'type' => 'string',
            'default' => '#AA151B',
            'sanitize_callback' => 'sanitize_hex_color'
        ]);

        register_setting('sit_connect_colors', 'sit_connect_primary_dark_color', [
            'type' => 'string',
            'default' => '#8B1116',
            'sanitize_callback' => 'sanitize_hex_color'
        ]);

        register_setting('sit_connect_colors', 'sit_connect_secondary_color', [
            'type' => 'string',
            'default' => '#F1BF00',
            'sanitize_callback' => 'sanitize_hex_color'
        ]);

        register_setting('sit_connect_colors', 'sit_connect_accent_color', [
            'type' => 'string',
            'default' => '#C29900',
            'sanitize_callback' => 'sanitize_hex_color'
        ]);

        // License Settings
        register_setting('sit_connect_license', 'sit_connect_license_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        register_setting('sit_connect_license', 'sit_connect_license_status', [
            'type' => 'string',
            'default' => 'inactive'
        ]);

        register_setting('sit_connect_license', 'sit_connect_license_email', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email'
        ]);

        // Zoho API Settings
        register_setting('sit_connect_zoho', 'zoho_client_id', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        register_setting('sit_connect_zoho', 'zoho_client_secret', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        register_setting('sit_connect_zoho', 'zoho_secret_code', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
    }

    public function render_settings_page()
    {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get current values
        $primary_color = get_option('sit_connect_primary_color', '#AA151B');
        $primary_dark_color = get_option('sit_connect_primary_dark_color', '#8B1116');
        $secondary_color = get_option('sit_connect_secondary_color', '#F1BF00');
        $accent_color = get_option('sit_connect_accent_color', '#C29900');
        
        $license_key = get_option('sit_connect_license_key', '');
        $license_status = get_option('sit_connect_license_status', 'inactive');
        $license_email = get_option('sit_connect_license_email', '');

        $zoho_client_id = get_field('zoho_client_id', 'option') ?? '';
        $zoho_client_secret = get_field('zoho_client_secret', 'option') ?? '';
        $zoho_secret_code = get_field('zoho_secret_code', 'option') ?? '';

        // Handle form submission
        if (isset($_POST['sit_connect_save_colors']) && check_admin_referer('sit_connect_colors_nonce')) {
            update_option('sit_connect_primary_color', sanitize_hex_color($_POST['primary_color']));
            update_option('sit_connect_primary_dark_color', sanitize_hex_color($_POST['primary_dark_color']));
            update_option('sit_connect_secondary_color', sanitize_hex_color($_POST['secondary_color']));
            update_option('sit_connect_accent_color', sanitize_hex_color($_POST['accent_color']));
            
            echo '<div class="notice notice-success"><p>' . __('Colors saved successfully!', SIT_SEARCH_TEXT_DOMAIN) . '</p></div>';
            
            // Refresh values
            $primary_color = get_option('sit_connect_primary_color');
            $primary_dark_color = get_option('sit_connect_primary_dark_color');
            $secondary_color = get_option('sit_connect_secondary_color');
            $accent_color = get_option('sit_connect_accent_color');
        }

        // Handle license activation
        if (isset($_POST['sit_connect_activate_license']) && check_admin_referer('sit_connect_license_nonce')) {
            $result = $this->activate_license(
                sanitize_text_field($_POST['license_key']),
                sanitize_email($_POST['license_email'])
            );
            
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
            
            $license_key = get_option('sit_connect_license_key', '');
            $license_status = get_option('sit_connect_license_status', 'inactive');
            $license_email = get_option('sit_connect_license_email', '');
        }

        // Handle license deactivation
        if (isset($_POST['sit_connect_deactivate_license']) && check_admin_referer('sit_connect_license_nonce')) {
            $this->deactivate_license();
            echo '<div class="notice notice-success"><p>' . __('License deactivated successfully!', SIT_SEARCH_TEXT_DOMAIN) . '</p></div>';
            
            $license_key = '';
            $license_status = 'inactive';
            $license_email = '';
        }

        // Handle Zoho API settings
        if (isset($_POST['sit_connect_save_zoho']) && check_admin_referer('sit_connect_zoho_nonce')) {
            update_field('zoho_client_id', sanitize_text_field($_POST['zoho_client_id']), 'option');
            update_field('zoho_client_secret', sanitize_text_field($_POST['zoho_client_secret']), 'option');
            update_field('zoho_secret_code', sanitize_text_field($_POST['zoho_secret_code']), 'option');
            
            // Clear the cached access token
            delete_transient('zoho_access_token');
            
            echo '<div class="notice notice-success"><p>' . __('Zoho API settings saved successfully!', SIT_SEARCH_TEXT_DOMAIN) . '</p></div>';
            
            // Refresh values
            $zoho_client_id = get_field('zoho_client_id', 'option') ?? '';
            $zoho_client_secret = get_field('zoho_client_secret', 'option') ?? '';
            $zoho_secret_code = get_field('zoho_secret_code', 'option') ?? '';
        }

        ?>
        <div class="wrap sit-connect-settings">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="sit-connect-tabs">
                <button class="sit-tab-button active" data-tab="colors"><?php _e('Color Customization', SIT_SEARCH_TEXT_DOMAIN); ?></button>
                <button class="sit-tab-button" data-tab="zoho"><?php _e('Zoho API', SIT_SEARCH_TEXT_DOMAIN); ?></button>
                <button class="sit-tab-button" data-tab="license"><?php _e('License', SIT_SEARCH_TEXT_DOMAIN); ?></button>
            </div>

            <!-- Color Customization Tab -->
            <div class="sit-tab-content active" id="colors-tab">
                <form method="post" action="">
                    <?php wp_nonce_field('sit_connect_colors_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="primary_color"><?php _e('Primary Color', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                            </th>
                            <td>
                                <input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr($primary_color); ?>" class="sit-color-picker" />
                                <input type="text" value="<?php echo esc_attr($primary_color); ?>" class="sit-color-text" readonly />
                                <p class="description"><?php _e('Main brand color used throughout the plugin', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="primary_dark_color"><?php _e('Primary Dark Color', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                            </th>
                            <td>
                                <input type="color" id="primary_dark_color" name="primary_dark_color" value="<?php echo esc_attr($primary_dark_color); ?>" class="sit-color-picker" />
                                <input type="text" value="<?php echo esc_attr($primary_dark_color); ?>" class="sit-color-text" readonly />
                                <p class="description"><?php _e('Darker shade of primary color for gradients and hover states', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="secondary_color"><?php _e('Secondary Color', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                            </th>
                            <td>
                                <input type="color" id="secondary_color" name="secondary_color" value="<?php echo esc_attr($secondary_color); ?>" class="sit-color-picker" />
                                <input type="text" value="<?php echo esc_attr($secondary_color); ?>" class="sit-color-text" readonly />
                                <p class="description"><?php _e('Secondary accent color', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="accent_color"><?php _e('Accent Color', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                            </th>
                            <td>
                                <input type="color" id="accent_color" name="accent_color" value="<?php echo esc_attr($accent_color); ?>" class="sit-color-picker" />
                                <input type="text" value="<?php echo esc_attr($accent_color); ?>" class="sit-color-text" readonly />
                                <p class="description"><?php _e('Accent color for highlights and special elements', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="sit-color-preview">
                        <h3><?php _e('Preview', SIT_SEARCH_TEXT_DOMAIN); ?></h3>
                        <div class="preview-buttons">
                            <button type="button" class="preview-btn primary-btn"><?php _e('Primary Button', SIT_SEARCH_TEXT_DOMAIN); ?></button>
                            <button type="button" class="preview-btn secondary-btn"><?php _e('Secondary Button', SIT_SEARCH_TEXT_DOMAIN); ?></button>
                        </div>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="sit_connect_save_colors" class="button button-primary" value="<?php _e('Save Colors', SIT_SEARCH_TEXT_DOMAIN); ?>" />
                        <button type="button" id="reset-colors" class="button"><?php _e('Reset to Default', SIT_SEARCH_TEXT_DOMAIN); ?></button>
                    </p>
                </form>
            </div>

            <!-- Zoho API Tab -->
            <div class="sit-tab-content" id="zoho-tab">
                <form method="post" action="">
                    <?php wp_nonce_field('sit_connect_zoho_nonce'); ?>
                    
                    <p><?php _e('Enter your Zoho CRM API credentials to enable synchronization between your WordPress site and Zoho CRM.', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="zoho_client_id"><?php _e('Client ID', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                            </th>
                            <td>
                                <input type="text" id="zoho_client_id" name="zoho_client_id" value="<?php echo esc_attr($zoho_client_id); ?>" class="regular-text" />
                                <p class="description"><?php _e('Your Zoho CRM Client ID from the API Console', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="zoho_client_secret"><?php _e('Client Secret', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                            </th>
                            <td>
                                <input type="password" id="zoho_client_secret" name="zoho_client_secret" value="<?php echo esc_attr($zoho_client_secret); ?>" class="regular-text" />
                                <p class="description"><?php _e('Your Zoho CRM Client Secret from the API Console', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="zoho_secret_code"><?php _e('Refresh Token', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                            </th>
                            <td>
                                <textarea id="zoho_secret_code" name="zoho_secret_code" rows="3" class="large-text"><?php echo esc_textarea($zoho_secret_code); ?></textarea>
                                <p class="description"><?php _e('Your Zoho CRM Refresh Token (also called Secret Code)', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="notice notice-info inline">
                        <p>
                            <strong><?php _e('How to get Zoho API credentials:', SIT_SEARCH_TEXT_DOMAIN); ?></strong><br>
                            1. Go to <a href="https://api-console.zoho.com/" target="_blank">Zoho API Console</a><br>
                            2. Create a new Self Client application<br>
                            3. Copy the Client ID and Client Secret<br>
                            4. Generate a Refresh Token using the authorization process<br>
                            5. Paste all credentials here and save
                        </p>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="sit_connect_save_zoho" class="button button-primary" value="<?php _e('Save Zoho API Settings', SIT_SEARCH_TEXT_DOMAIN); ?>" />
                    </p>
                </form>
            </div>

            <!-- License Tab -->
            <div class="sit-tab-content" id="license-tab">
                <?php if (function_exists('sc_fs') && sc_fs() !== null): ?>
                    <!-- Freemius is handling licensing -->
                    <div class="notice notice-info inline">
                        <p>
                            <strong><?php _e('Licensing Managed by Freemius', SIT_SEARCH_TEXT_DOMAIN); ?></strong><br>
                            <?php _e('Your license is managed through Freemius. Use the account menu to manage your license.', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </p>
                    </div>
                    
                    <?php 
                    $freemius = sc_fs();
                    if ($freemius->is_registered()): ?>
                        <div class="license-active-notice">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <strong><?php _e('License Active', SIT_SEARCH_TEXT_DOMAIN); ?></strong>
                        </div>
                        <?php if ($freemius->is_free_plan()): ?>
                            <p><?php _e('You are using the FREE plan. All features are available!', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                            <p>
                                <a href="<?php echo $freemius->get_upgrade_url(); ?>" class="button button-secondary">
                                    <?php _e('Upgrade to Premium', SIT_SEARCH_TEXT_DOMAIN); ?>
                                </a>
                            </p>
                        <?php elseif ($freemius->is_trial()): ?>
                            <p><?php _e('You are using a trial version.', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                        <?php else: ?>
                            <p><?php _e('Your license is active and all features are unlocked!', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="notice notice-error inline">
                            <p>
                                <strong><?php _e('No Active License', SIT_SEARCH_TEXT_DOMAIN); ?></strong><br>
                                <?php _e('Please activate your license to use SIT Connect.', SIT_SEARCH_TEXT_DOMAIN); ?>
                            </p>
                        </div>
                        <p>
                            <a href="<?php echo $freemius->get_activation_url(); ?>" class="button button-primary">
                                <?php _e('Activate License', SIT_SEARCH_TEXT_DOMAIN); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Custom License System (Freemius not installed) -->
                    <div class="notice notice-warning inline">
                        <p>
                            <strong><?php _e('Custom License System', SIT_SEARCH_TEXT_DOMAIN); ?></strong><br>
                            <?php _e('For production use, please install Freemius SDK for automatic license management.', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </p>
                    </div>
                    
                <form method="post" action="">
                    <?php wp_nonce_field('sit_connect_license_nonce'); ?>
                    
                    <?php if ($license_status === 'active'): ?>
                        <div class="license-active-notice">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <strong><?php _e('License Active', SIT_SEARCH_TEXT_DOMAIN); ?></strong>
                        </div>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('License Key', SIT_SEARCH_TEXT_DOMAIN); ?></th>
                                <td>
                                    <code><?php echo esc_html($this->mask_license_key($license_key)); ?></code>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Email', SIT_SEARCH_TEXT_DOMAIN); ?></th>
                                <td><?php echo esc_html($license_email); ?></td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="sit_connect_deactivate_license" class="button button-secondary" value="<?php _e('Deactivate License', SIT_SEARCH_TEXT_DOMAIN); ?>" />
                        </p>
                    <?php else: ?>
                        <p><?php _e('Enter your license key to activate SIT Connect and receive updates and support.', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="license_key"><?php _e('License Key', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="license_key" name="license_key" value="<?php echo esc_attr($license_key); ?>" class="regular-text" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="license_email"><?php _e('Email Address', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                                </th>
                                <td>
                                    <input type="email" id="license_email" name="license_email" value="<?php echo esc_attr($license_email); ?>" class="regular-text" required />
                                    <p class="description"><?php _e('The email address used for purchase', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="sit_connect_activate_license" class="button button-primary" value="<?php _e('Activate License', SIT_SEARCH_TEXT_DOMAIN); ?>" />
                        </p>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <style>
            .sit-connect-settings {
                max-width: 900px;
            }
            
            .sit-connect-tabs {
                margin: 20px 0;
                border-bottom: 1px solid #ccc;
            }
            
            .sit-tab-button {
                background: none;
                border: none;
                padding: 10px 20px;
                cursor: pointer;
                font-size: 14px;
                border-bottom: 2px solid transparent;
                margin-right: 10px;
            }
            
            .sit-tab-button.active {
                border-bottom-color: #2271b1;
                color: #2271b1;
                font-weight: 600;
            }
            
            .sit-tab-content {
                display: none;
                padding: 20px 0;
            }
            
            .sit-tab-content.active {
                display: block;
            }
            
            .sit-color-picker {
                width: 100px;
                height: 40px;
                border: 1px solid #ddd;
                border-radius: 4px;
                cursor: pointer;
            }
            
            .sit-color-text {
                margin-left: 10px;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                width: 100px;
                text-align: center;
                font-family: monospace;
            }
            
            .sit-color-preview {
                background: #f5f5f5;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            
            .preview-buttons {
                display: flex;
                gap: 15px;
                margin-top: 15px;
            }
            
            .preview-btn {
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            
            .primary-btn {
                background-color: <?php echo esc_attr($primary_color); ?>;
                color: white;
            }
            
            .primary-btn:hover {
                background-color: <?php echo esc_attr($primary_dark_color); ?>;
            }
            
            .secondary-btn {
                background-color: <?php echo esc_attr($secondary_color); ?>;
                color: #333;
            }
            
            .secondary-btn:hover {
                background-color: <?php echo esc_attr($accent_color); ?>;
            }
            
            .license-active-notice {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .license-active-notice .dashicons {
                font-size: 24px;
                width: 24px;
                height: 24px;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Tab switching
            $('.sit-tab-button').on('click', function() {
                var tab = $(this).data('tab');
                
                $('.sit-tab-button').removeClass('active');
                $(this).addClass('active');
                
                $('.sit-tab-content').removeClass('active');
                $('#' + tab + '-tab').addClass('active');
            });
            
            // Color picker sync
            $('.sit-color-picker').on('input', function() {
                $(this).next('.sit-color-text').val($(this).val());
                updatePreview();
            });
            
            // Update preview colors
            function updatePreview() {
                var primary = $('#primary_color').val();
                var primaryDark = $('#primary_dark_color').val();
                var secondary = $('#secondary_color').val();
                var accent = $('#accent_color').val();
                
                $('.primary-btn').css('background-color', primary);
                $('.primary-btn').hover(
                    function() { $(this).css('background-color', primaryDark); },
                    function() { $(this).css('background-color', primary); }
                );
                
                $('.secondary-btn').css('background-color', secondary);
                $('.secondary-btn').hover(
                    function() { $(this).css('background-color', accent); },
                    function() { $(this).css('background-color', secondary); }
                );
            }
            
            // Reset to default colors
            $('#reset-colors').on('click', function() {
                if (confirm('<?php _e('Are you sure you want to reset to default colors?', SIT_SEARCH_TEXT_DOMAIN); ?>')) {
                    $('#primary_color').val('#AA151B');
                    $('#primary_dark_color').val('#8B1116');
                    $('#secondary_color').val('#F1BF00');
                    $('#accent_color').val('#C29900');
                    
                    $('.sit-color-picker').each(function() {
                        $(this).next('.sit-color-text').val($(this).val());
                    });
                    
                    updatePreview();
                }
            });
        });
        </script>
        <?php
    }

    private function activate_license($license_key, $email)
    {
        // Freemius handles licensing automatically
        // This is kept for custom license validation if needed
        
        // Validate with Freemius or custom server
        $api_url = apply_filters('sit_connect_license_api_url', 'https://your-license-server.com/api/activate');
        
        $response = wp_remote_post($api_url, [
            'body' => [
                'license_key' => $license_key,
                'email' => $email,
                'domain' => home_url()
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => __('Could not connect to license server. Please try again later.', SIT_SEARCH_TEXT_DOMAIN)
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['success']) && $body['success']) {
            update_option('sit_connect_license_key', $license_key);
            update_option('sit_connect_license_status', 'active');
            update_option('sit_connect_license_email', $email);
            update_option('sit_connect_license_activated_at', time());
            
            return [
                'success' => true,
                'message' => __('License activated successfully!', SIT_SEARCH_TEXT_DOMAIN)
            ];
        }

        return [
            'success' => false,
            'message' => $body['message'] ?? __('Invalid license key or email.', SIT_SEARCH_TEXT_DOMAIN)
        ];
    }

    private function deactivate_license()
    {
        $license_key = get_option('sit_connect_license_key');
        
        // TODO: Replace with your actual license server URL
        $api_url = 'https://your-license-server.com/api/deactivate';
        
        wp_remote_post($api_url, [
            'body' => [
                'license_key' => $license_key,
                'domain' => home_url()
            ],
            'timeout' => 15
        ]);

        delete_option('sit_connect_license_key');
        update_option('sit_connect_license_status', 'inactive');
        delete_option('sit_connect_license_email');
        delete_option('sit_connect_license_activated_at');
    }

    private function mask_license_key($key)
    {
        if (strlen($key) <= 8) {
            return $key;
        }
        
        return substr($key, 0, 4) . str_repeat('*', strlen($key) - 8) . substr($key, -4);
    }
}
