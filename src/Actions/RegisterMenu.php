<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Hook;
use SIT\Search\Services\Template;

class RegisterMenu extends Hook
{
    public static array $hooks = ['admin_menu'];

    public static int $priority = 10;

    public function __invoke()
    {
        add_menu_page(
            'SIT Connect',
            'SIT Connect',
            'manage_options',
            'sit-connect',
            array($this, 'menuPage'),
            'dashicons-admin-site-alt3',
            6
        );

        //Mapping Page
        add_submenu_page(
            'sit-connect',
            'Mapping',
            'Mapping',
            'manage_options',
            'sit-connect-mapping',
            array($this, 'mappingPage')
        );
    }

    public function menuPage()
    {
        // Get statistics
        $universities_count = wp_count_posts('sit-university')->publish ?? 0;
        $programs_count = wp_count_posts('sit-program')->publish ?? 0;
        $campus_count = wp_count_posts('sit-campus')->publish ?? 0;
        
        $countries_count = wp_count_terms(['taxonomy' => 'sit-country', 'hide_empty' => false]);
        $specialities_count = wp_count_terms(['taxonomy' => 'sit-speciality', 'hide_empty' => false]);
        $faculties_count = wp_count_terms(['taxonomy' => 'sit-faculty', 'hide_empty' => false]);
        
        // Check license status (Freemius or custom)
        $license_status = 'inactive';
        $license_type = 'None';
        
        if (function_exists('sc_fs') && sc_fs() !== null) {
            $freemius = sc_fs();
            if ($freemius->is_registered()) {
                $license_status = 'active';
                if ($freemius->is_free_plan()) {
                    $license_type = 'Free Plan';
                } elseif ($freemius->is_trial()) {
                    $license_type = 'Trial';
                } elseif ($freemius->is_premium()) {
                    $license_type = 'Premium';
                } else {
                    $license_type = 'Active';
                }
            }
        } else {
            // Fallback to custom license
            $custom_status = get_option('sit_connect_license_status', 'inactive');
            if ($custom_status === 'active') {
                $license_status = 'active';
                $license_type = 'Custom License';
            }
        }
        
        $primary_color = get_option('sit_connect_primary_color', '#AA151B');
        
        ?>
        <div class="wrap sit-connect-dashboard">
            <h1><?php _e('SIT Connect Dashboard', SIT_SEARCH_TEXT_DOMAIN); ?></h1>
            <p class="about-description"><?php _e('Welcome to SIT Connect - Your complete university and program management system', SIT_SEARCH_TEXT_DOMAIN); ?></p>
            
            <!-- License Status -->
            <div class="sit-dashboard-section">
                <?php if ($license_status === 'active'): ?>
                    <div class="notice notice-success inline">
                        <p><span class="dashicons dashicons-yes-alt"></span> <strong><?php _e('License Active', SIT_SEARCH_TEXT_DOMAIN); ?></strong> - <?php _e('All features unlocked', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                    </div>
                <?php else: ?>
                    <div class="notice notice-warning inline">
                        <p><span class="dashicons dashicons-warning"></span> <strong><?php _e('License Not Activated', SIT_SEARCH_TEXT_DOMAIN); ?></strong> - <a href="<?php echo admin_url('admin.php?page=sit-connect-settings'); ?>"><?php _e('Activate Now', SIT_SEARCH_TEXT_DOMAIN); ?></a></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Statistics Cards -->
            <div class="sit-stats-grid">
                <div class="sit-stat-card">
                    <div class="sit-stat-icon" style="background: <?php echo esc_attr($primary_color); ?>;">
                        <span class="dashicons dashicons-admin-multisite"></span>
                    </div>
                    <div class="sit-stat-content">
                        <h3><?php echo number_format($universities_count); ?></h3>
                        <p><?php _e('Universities', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                        <a href="<?php echo admin_url('edit.php?post_type=sit-university'); ?>"><?php _e('View All', SIT_SEARCH_TEXT_DOMAIN); ?> →</a>
                    </div>
                </div>
                
                <div class="sit-stat-card">
                    <div class="sit-stat-icon" style="background: <?php echo esc_attr($primary_color); ?>;">
                        <span class="dashicons dashicons-welcome-learn-more"></span>
                    </div>
                    <div class="sit-stat-content">
                        <h3><?php echo number_format($programs_count); ?></h3>
                        <p><?php _e('Programs', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                        <a href="<?php echo admin_url('edit.php?post_type=sit-program'); ?>"><?php _e('View All', SIT_SEARCH_TEXT_DOMAIN); ?> →</a>
                    </div>
                </div>
                
                <div class="sit-stat-card">
                    <div class="sit-stat-icon" style="background: <?php echo esc_attr($primary_color); ?>;">
                        <span class="dashicons dashicons-location"></span>
                    </div>
                    <div class="sit-stat-content">
                        <h3><?php echo number_format($campus_count); ?></h3>
                        <p><?php _e('Campuses', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                        <a href="<?php echo admin_url('edit.php?post_type=sit-campus'); ?>"><?php _e('View All', SIT_SEARCH_TEXT_DOMAIN); ?> →</a>
                    </div>
                </div>
                
                <div class="sit-stat-card">
                    <div class="sit-stat-icon" style="background: <?php echo esc_attr($primary_color); ?>;">
                        <span class="dashicons dashicons-admin-site"></span>
                    </div>
                    <div class="sit-stat-content">
                        <h3><?php echo number_format($countries_count); ?></h3>
                        <p><?php _e('Countries', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                        <a href="<?php echo admin_url('edit-tags.php?taxonomy=sit-country'); ?>"><?php _e('Manage', SIT_SEARCH_TEXT_DOMAIN); ?> →</a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="sit-dashboard-row">
                <div class="sit-dashboard-col">
                    <div class="sit-dashboard-box">
                        <h2><span class="dashicons dashicons-admin-generic"></span> <?php _e('Quick Actions', SIT_SEARCH_TEXT_DOMAIN); ?></h2>
                        <div class="sit-quick-actions">
                            <a href="<?php echo admin_url('post-new.php?post_type=sit-university'); ?>" class="button button-primary">
                                <span class="dashicons dashicons-plus-alt"></span> <?php _e('Add University', SIT_SEARCH_TEXT_DOMAIN); ?>
                            </a>
                            <a href="<?php echo admin_url('post-new.php?post_type=sit-program'); ?>" class="button button-primary">
                                <span class="dashicons dashicons-plus-alt"></span> <?php _e('Add Program', SIT_SEARCH_TEXT_DOMAIN); ?>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=sit-connect-sync'); ?>" class="button button-secondary">
                                <span class="dashicons dashicons-update"></span> <?php _e('Sync with Zoho', SIT_SEARCH_TEXT_DOMAIN); ?>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=sit-connect-settings'); ?>" class="button button-secondary">
                                <span class="dashicons dashicons-admin-appearance"></span> <?php _e('Customize Colors', SIT_SEARCH_TEXT_DOMAIN); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="sit-dashboard-col">
                    <div class="sit-dashboard-box">
                        <h2><span class="dashicons dashicons-info"></span> <?php _e('System Information', SIT_SEARCH_TEXT_DOMAIN); ?></h2>
                        <table class="sit-info-table">
                            <tr>
                                <td><strong><?php _e('Plugin Version:', SIT_SEARCH_TEXT_DOMAIN); ?></strong></td>
                                <td><?php echo STI_SEARCH_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Total Specialities:', SIT_SEARCH_TEXT_DOMAIN); ?></strong></td>
                                <td><?php echo number_format($specialities_count); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Total Faculties:', SIT_SEARCH_TEXT_DOMAIN); ?></strong></td>
                                <td><?php echo number_format($faculties_count); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('License Status:', SIT_SEARCH_TEXT_DOMAIN); ?></strong></td>
                                <td>
                                    <?php if ($license_status === 'active'): ?>
                                        <span style="color: #46b450;">● <?php echo esc_html($license_type); ?></span>
                                    <?php else: ?>
                                        <span style="color: #dc3232;">● <?php _e('Inactive', SIT_SEARCH_TEXT_DOMAIN); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="sit-dashboard-box">
                <h2><span class="dashicons dashicons-chart-line"></span> <?php _e('Recent Programs', SIT_SEARCH_TEXT_DOMAIN); ?></h2>
                <?php
                $recent_programs = get_posts([
                    'post_type' => 'sit-program',
                    'posts_per_page' => 5,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);
                
                if ($recent_programs): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Program Name', SIT_SEARCH_TEXT_DOMAIN); ?></th>
                                <th><?php _e('University', SIT_SEARCH_TEXT_DOMAIN); ?></th>
                                <th><?php _e('Date Added', SIT_SEARCH_TEXT_DOMAIN); ?></th>
                                <th><?php _e('Actions', SIT_SEARCH_TEXT_DOMAIN); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_programs as $program): 
                                $uni_id = get_post_meta($program->ID, 'zh_university', true);
                                $uni_name = $uni_id ? get_the_title($uni_id) : __('N/A', SIT_SEARCH_TEXT_DOMAIN);
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html($program->post_title); ?></strong></td>
                                    <td><?php echo esc_html($uni_name); ?></td>
                                    <td><?php echo get_the_date('', $program); ?></td>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($program->ID); ?>"><?php _e('Edit', SIT_SEARCH_TEXT_DOMAIN); ?></a> |
                                        <a href="<?php echo get_permalink($program->ID); ?>" target="_blank"><?php _e('View', SIT_SEARCH_TEXT_DOMAIN); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No programs found. Start by adding your first program!', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Help & Documentation -->
            <div class="sit-dashboard-row">
                <div class="sit-dashboard-col">
                    <div class="sit-dashboard-box">
                        <h2><span class="dashicons dashicons-book"></span> <?php _e('Documentation', SIT_SEARCH_TEXT_DOMAIN); ?></h2>
                        <ul class="sit-help-links">
                            <li><a href="<?php echo admin_url('admin.php?page=sit-connect-settings'); ?>"><span class="dashicons dashicons-admin-appearance"></span> <?php _e('Color Customization Guide', SIT_SEARCH_TEXT_DOMAIN); ?></a></li>
                            <li><a href="<?php echo admin_url('admin.php?page=sit-connect-sync'); ?>"><span class="dashicons dashicons-update"></span> <?php _e('Zoho CRM Integration', SIT_SEARCH_TEXT_DOMAIN); ?></a></li>
                            <li><a href="<?php echo admin_url('admin.php?page=sit-connect-mapping'); ?>"><span class="dashicons dashicons-admin-settings"></span> <?php _e('Field Mapping', SIT_SEARCH_TEXT_DOMAIN); ?></a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="sit-dashboard-col">
                    <div class="sit-dashboard-box sit-support-box">
                        <h2><span class="dashicons dashicons-sos"></span> <?php _e('Need Help?', SIT_SEARCH_TEXT_DOMAIN); ?></h2>
                        <p><?php _e('Get support and updates for SIT Connect', SIT_SEARCH_TEXT_DOMAIN); ?></p>
                        <a href="mailto:support@sitconnect.com" class="button button-secondary">
                            <span class="dashicons dashicons-email"></span> <?php _e('Contact Support', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .sit-connect-dashboard {
                max-width: 1400px;
            }
            
            .sit-dashboard-section {
                margin: 20px 0;
            }
            
            .sit-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            
            .sit-stat-card {
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 20px;
                display: flex;
                align-items: center;
                gap: 20px;
                transition: all 0.3s ease;
            }
            
            .sit-stat-card:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                transform: translateY(-2px);
            }
            
            .sit-stat-icon {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            
            .sit-stat-icon .dashicons {
                color: white;
                font-size: 30px;
                width: 30px;
                height: 30px;
            }
            
            .sit-stat-content h3 {
                margin: 0 0 5px 0;
                font-size: 32px;
                font-weight: 600;
                color: #1d2327;
            }
            
            .sit-stat-content p {
                margin: 0 0 10px 0;
                color: #646970;
                font-size: 14px;
            }
            
            .sit-stat-content a {
                text-decoration: none;
                color: <?php echo esc_attr($primary_color); ?>;
                font-size: 13px;
            }
            
            .sit-dashboard-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            
            .sit-dashboard-box {
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 20px;
            }
            
            .sit-dashboard-box h2 {
                margin-top: 0;
                display: flex;
                align-items: center;
                gap: 10px;
                border-bottom: 2px solid #f0f0f0;
                padding-bottom: 15px;
            }
            
            .sit-quick-actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .sit-quick-actions .button {
                justify-content: flex-start;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .sit-info-table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .sit-info-table td {
                padding: 12px 0;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .sit-info-table tr:last-child td {
                border-bottom: none;
            }
            
            .sit-help-links {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .sit-help-links li {
                margin: 12px 0;
            }
            
            .sit-help-links a {
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 8px;
                color: #2271b1;
                transition: all 0.2s;
            }
            
            .sit-help-links a:hover {
                color: <?php echo esc_attr($primary_color); ?>;
                padding-left: 5px;
            }
            
            .sit-support-box {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
            }
            
            .sit-support-box h2 {
                color: white;
                border-bottom-color: rgba(255,255,255,0.2);
            }
            
            .sit-support-box p {
                color: rgba(255,255,255,0.9);
            }
            
            .sit-support-box .button {
                background: white;
                color: #667eea;
                border: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            
            .sit-support-box .button:hover {
                background: #f0f0f0;
            }
        </style>
        <?php
    }

    public function mappingPage()
    {
        Template::render('admin/mapping');
    }
}