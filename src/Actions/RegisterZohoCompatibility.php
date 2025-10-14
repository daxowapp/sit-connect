<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\ZohoFieldValidator;

/**
 * Register Zoho Compatibility Checker Page
 */
class RegisterZohoCompatibility
{
    public static $hooks = ['admin_menu'];
    public static $priority = 100;
    public static $arguments = 0;
    
    public function __invoke()
    {
        add_submenu_page(
            'sit-connect',
            __('Zoho Compatibility', SIT_SEARCH_TEXT_DOMAIN),
            __('Zoho Compatibility', SIT_SEARCH_TEXT_DOMAIN),
            'manage_options',
            'sit-zoho-compatibility',
            [$this, 'render_page']
        );
    }
    
    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $validator = ZohoFieldValidator::get_instance();
        
        // Handle actions
        if (isset($_POST['check_compatibility']) && check_admin_referer('sit_zoho_compat_nonce')) {
            $validator->clear_cache();
            $report = $validator->check_compatibility();
            $missing = $validator->get_missing_fields();
        } else {
            $report = null;
            $missing = $validator->get_missing_fields();
        }
        
        if (isset($_POST['clear_missing']) && check_admin_referer('sit_zoho_compat_nonce')) {
            $validator->clear_missing_fields();
            $missing = [];
            echo '<div class="notice notice-success"><p>' . __('Missing fields log cleared!', SIT_SEARCH_TEXT_DOMAIN) . '</p></div>';
        }
        
        ?>
        <div class="wrap sit-zoho-compatibility">
            <h1><?php _e('Zoho CRM Compatibility Check', SIT_SEARCH_TEXT_DOMAIN); ?></h1>
            
            <div class="notice notice-info">
                <p>
                    <strong><?php _e('What is this?', SIT_SEARCH_TEXT_DOMAIN); ?></strong><br>
                    <?php _e('This tool checks if your Zoho CRM configuration has all the fields needed by SIT Connect. Different Zoho accounts may have different custom fields.', SIT_SEARCH_TEXT_DOMAIN); ?>
                </p>
            </div>
            
            <!-- Check Compatibility Button -->
            <div class="card" style="max-width: 800px; margin: 20px 0;">
                <h2><?php _e('Run Compatibility Check', SIT_SEARCH_TEXT_DOMAIN); ?></h2>
                <p><?php _e('Click the button below to check your Zoho CRM configuration against SIT Connect requirements.', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('sit_zoho_compat_nonce'); ?>
                    <p>
                        <input type="submit" name="check_compatibility" class="button button-primary" value="<?php _e('Check Compatibility', SIT_SEARCH_TEXT_DOMAIN); ?>" />
                    </p>
                </form>
            </div>
            
            <?php if ($report): ?>
            <!-- Compatibility Report -->
            <div class="card" style="max-width: 100%; margin: 20px 0;">
                <h2><?php _e('Compatibility Report', SIT_SEARCH_TEXT_DOMAIN); ?></h2>
                
                <?php foreach ($report as $module => $data): ?>
                <div style="margin-bottom: 30px;">
                    <h3><?php echo esc_html($module); ?> 
                        <span style="color: #2271b1; font-size: 14px;">
                            (<?php echo count($data['fields']); ?> fields available)
                        </span>
                    </h3>
                    
                    <?php
                    $suggestions = $validator->suggest_field_mapping($module);
                    $has_missing = !empty($suggestions['missing']);
                    ?>
                    
                    <?php if ($has_missing): ?>
                    <div class="notice notice-warning inline">
                        <p>
                            <strong><?php _e('Missing Fields:', SIT_SEARCH_TEXT_DOMAIN); ?></strong><br>
                            <?php echo implode(', ', array_map('esc_html', $suggestions['missing'])); ?>
                        </p>
                        <p>
                            <?php _e('These fields are expected but not found in your Zoho CRM. The plugin will work without them, but some features may be limited.', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="notice notice-success inline">
                        <p>✅ <?php _e('All expected fields are available!', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Field Mapping -->
                    <details style="margin-top: 15px;">
                        <summary style="cursor: pointer; font-weight: 600;">
                            <?php _e('View Field Mapping', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </summary>
                        <table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th><?php _e('Expected Field', SIT_SEARCH_TEXT_DOMAIN); ?></th>
                                    <th><?php _e('Zoho Field', SIT_SEARCH_TEXT_DOMAIN); ?></th>
                                    <th><?php _e('Status', SIT_SEARCH_TEXT_DOMAIN); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suggestions['mapping'] as $expected => $actual): ?>
                                <tr>
                                    <td><code><?php echo esc_html($expected); ?></code></td>
                                    <td><code><?php echo esc_html($actual); ?></code></td>
                                    <td>
                                        <?php if ($expected === $actual): ?>
                                            <span style="color: green;">✓ <?php _e('Exact Match', SIT_SEARCH_TEXT_DOMAIN); ?></span>
                                        <?php else: ?>
                                            <span style="color: orange;">⚠ <?php _e('Similar Field', SIT_SEARCH_TEXT_DOMAIN); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php foreach ($suggestions['missing'] as $missing_field): ?>
                                <tr style="background: #fff3cd;">
                                    <td><code><?php echo esc_html($missing_field); ?></code></td>
                                    <td><em><?php _e('Not found', SIT_SEARCH_TEXT_DOMAIN); ?></em></td>
                                    <td><span style="color: red;">✗ <?php _e('Missing', SIT_SEARCH_TEXT_DOMAIN); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </details>
                    
                    <!-- Available Fields -->
                    <details style="margin-top: 15px;">
                        <summary style="cursor: pointer; font-weight: 600;">
                            <?php _e('All Available Fields in Your Zoho', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </summary>
                        <div style="margin-top: 10px; padding: 10px; background: #f5f5f5; max-height: 300px; overflow-y: auto;">
                            <?php foreach ($data['fields'] as $field): ?>
                                <span style="display: inline-block; margin: 3px; padding: 5px 10px; background: white; border: 1px solid #ddd; border-radius: 3px; font-size: 12px;">
                                    <?php echo esc_html($field); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </details>
                </div>
                <hr>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Missing Fields Log -->
            <?php if (!empty($missing)): ?>
            <div class="card" style="max-width: 800px; margin: 20px 0;">
                <h2><?php _e('Missing Fields Detected During Sync', SIT_SEARCH_TEXT_DOMAIN); ?></h2>
                <p><?php _e('These fields were requested during synchronization but not found in Zoho data:', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                
                <ul style="list-style: disc; margin-left: 20px;">
                    <?php foreach ($missing as $field): ?>
                    <li><code><?php echo esc_html($field); ?></code></li>
                    <?php endforeach; ?>
                </ul>
                
                <form method="post" action="">
                    <?php wp_nonce_field('sit_zoho_compat_nonce'); ?>
                    <p>
                        <input type="submit" name="clear_missing" class="button" value="<?php _e('Clear Log', SIT_SEARCH_TEXT_DOMAIN); ?>" />
                    </p>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Recommendations -->
            <div class="card" style="max-width: 800px; margin: 20px 0;">
                <h2><?php _e('Recommendations', SIT_SEARCH_TEXT_DOMAIN); ?></h2>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php _e('Run this check after installing the plugin on a new site', SIT_SEARCH_TEXT_DOMAIN); ?></li>
                    <li><?php _e('If fields are missing, the plugin will still work but may have limited functionality', SIT_SEARCH_TEXT_DOMAIN); ?></li>
                    <li><?php _e('You can add missing fields to your Zoho CRM if needed', SIT_SEARCH_TEXT_DOMAIN); ?></li>
                    <li><?php _e('Contact support if you need help mapping custom fields', SIT_SEARCH_TEXT_DOMAIN); ?></li>
                </ul>
            </div>
            
            <style>
                .sit-zoho-compatibility .card {
                    padding: 20px;
                    background: white;
                    border: 1px solid #ccd0d4;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                }
                
                .sit-zoho-compatibility details {
                    border: 1px solid #ddd;
                    padding: 10px;
                    border-radius: 4px;
                    background: #fafafa;
                }
                
                .sit-zoho-compatibility details[open] {
                    background: white;
                }
                
                .sit-zoho-compatibility summary {
                    padding: 5px;
                }
                
                .sit-zoho-compatibility summary:hover {
                    background: #f0f0f0;
                }
            </style>
        </div>
        <?php
    }
}
