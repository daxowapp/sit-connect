<?php
/**
 * Zoho Sync Admin Page
 *
 * @package SIT\Search
 */

// Handle AJAX sync requests
if (isset($_POST['sync_action'])) {
    check_admin_referer('sit_sync_action', 'sit_sync_nonce');
    
    // Increase timeout for sync operations
    @set_time_limit(300); // 5 minutes
    @ini_set('memory_limit', '512M');
    
    $action = sanitize_text_field($_POST['sync_action']);
    $country_filter = isset($_POST['country_filter']) ? sanitize_text_field($_POST['country_filter']) : '';
    
    try {
        $result = \SIT\Search\Services\ZohoSyncService::sync($action, $country_filter);
        
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    } catch (\Exception $e) {
        echo '<div class="notice notice-error"><p><strong>Sync Error:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
        error_log('SIT Sync Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    }
}

// Check Zoho connection
$zoho_configured = false;
$zoho_connected = false;

if (function_exists('get_field')) {
    $client_id = get_field('zoho_client_id', 'option');
    $client_secret = get_field('zoho_client_secret', 'option');
    $refresh_token = get_field('zoho_secret_code', 'option');
    
    if (!empty($client_id) && !empty($client_secret) && !empty($refresh_token)) {
        $zoho_configured = true;
        
        try {
            $zoho = new \SIT\Search\Services\Zoho();
            $access_token = $zoho->get_access_token();
            if ($access_token) {
                $zoho_connected = true;
            }
        } catch (Exception $e) {
            // Connection failed
        }
    }
}

// Get available countries for filtering
$countries = get_terms([
    'taxonomy' => 'sit-country',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
]);

?>
<div class="wrap">
    <h1 class="wp-heading-inline">🔄 Zoho Data Sync</h1>
    
    <style>
        .sync-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .sync-card h2 {
            margin-top: 0;
            color: #AA151B;
        }
        .sync-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        .sync-status.connected {
            background: #d4edda;
            color: #155724;
        }
        .sync-status.disconnected {
            background: #f8d7da;
            color: #721c24;
        }
        .sync-status.not-configured {
            background: #fff3cd;
            color: #856404;
        }
        .sync-button {
            background: #AA151B !important;
            border-color: #8B1116 !important;
            color: #fff !important;
            text-shadow: none !important;
            margin-right: 10px !important;
        }
        .sync-button:hover {
            background: #8B1116 !important;
        }
        .sync-button:disabled {
            background: #ccc !important;
            border-color: #ccc !important;
            cursor: not-allowed;
        }
        .sync-form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        .sync-form select {
            max-width: 300px;
        }
        .sync-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: #f7fafc;
            border-left: 4px solid #AA151B;
            padding: 15px;
            border-radius: 4px;
        }
        .stat-box h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #666;
        }
        .stat-box .number {
            font-size: 32px;
            font-weight: bold;
            color: #AA151B;
        }
        .sync-description {
            color: #666;
            font-size: 14px;
            margin: 10px 0;
        }
        .sync-progress {
            display: none;
            margin: 15px 0;
            padding: 10px;
            background: #f0f0f1;
            border-radius: 4px;
        }
        .sync-progress.active {
            display: block;
        }
    </style>
    
    <!-- Zoho Connection Status -->
    <div class="sync-card">
        <h2>📡 Zoho API Connection</h2>
        
        <?php if (!function_exists('get_field')): ?>
            <p>
                <span class="sync-status not-configured">⚠ ACF Not Active</span>
                <span class="sync-description">Install and activate Advanced Custom Fields plugin to configure Zoho credentials.</span>
            </p>
        <?php elseif (!$zoho_configured): ?>
            <p>
                <span class="sync-status not-configured">⚠ Not Configured</span>
                <span class="sync-description">Configure Zoho API credentials in <a href="<?php echo admin_url('admin.php?page=acf-options'); ?>">ACF Options</a>.</span>
            </p>
        <?php elseif ($zoho_connected): ?>
            <p>
                <span class="sync-status connected">✓ Connected</span>
                <span class="sync-description">Successfully connected to Zoho CRM API.</span>
            </p>
        <?php else: ?>
            <p>
                <span class="sync-status disconnected">✗ Connection Failed</span>
                <span class="sync-description">Unable to connect to Zoho API. Check your credentials.</span>
            </p>
        <?php endif; ?>
    </div>
    
    <!-- Current Data Statistics -->
    <div class="sync-card">
        <h2>📊 Current Data Statistics</h2>
        
        <div class="sync-stats">
            <div class="stat-box">
                <h3>Countries</h3>
                <div class="number"><?php echo count(get_terms(['taxonomy' => 'sit-country', 'hide_empty' => false])); ?></div>
            </div>
            <div class="stat-box">
                <h3>Cities</h3>
                <div class="number"><?php echo count(get_terms(['taxonomy' => 'sit-city', 'hide_empty' => false])); ?></div>
            </div>
            <div class="stat-box">
                <h3>Universities</h3>
                <?php 
                $uni_count = wp_count_posts('sit-university');
                $total_unis = isset($uni_count->publish) ? $uni_count->publish : 0;
                ?>
                <div class="number"><?php echo $total_unis; ?></div>
            </div>
            <div class="stat-box">
                <h3>Programs</h3>
                <?php 
                $prog_count = wp_count_posts('sit-program');
                $total_progs = isset($prog_count->publish) ? $prog_count->publish : 0;
                ?>
                <div class="number"><?php echo $total_progs; ?></div>
            </div>
        </div>
    </div>
    
    <?php if ($zoho_connected): ?>
    
    <!-- Sync Countries -->
    <div class="sync-card">
        <h2>🌍 Sync Countries</h2>
        <p class="sync-description">Fetch and sync all countries from Zoho CRM.</p>
        
        <form method="post" id="sync-countries-form">
            <?php wp_nonce_field('sit_sync_action', 'sit_sync_nonce'); ?>
            <input type="hidden" name="sync_action" value="countries">
            <button type="submit" class="button button-primary sync-button">
                Sync All Countries
            </button>
        </form>
        <div class="sync-progress" id="progress-countries"></div>
    </div>
    
    <!-- Sync Cities -->
    <div class="sync-card">
        <h2>🏙️ Sync Cities</h2>
        <p class="sync-description">Fetch and sync all cities from Zoho CRM.</p>
        
        <form method="post" id="sync-cities-form">
            <?php wp_nonce_field('sit_sync_action', 'sit_sync_nonce'); ?>
            <input type="hidden" name="sync_action" value="cities">
            <button type="submit" class="button button-primary sync-button">
                Sync All Cities
            </button>
        </form>
        <div class="sync-progress" id="progress-cities"></div>
    </div>
    
    <!-- Sync Universities -->
    <div class="sync-card">
        <h2>🎓 Sync Universities</h2>
        <p class="sync-description">Fetch and sync universities from Zoho CRM. You can filter by country to sync only specific universities.</p>
        
        <form method="post" id="sync-universities-form" class="sync-form">
            <?php wp_nonce_field('sit_sync_action', 'sit_sync_nonce'); ?>
            <input type="hidden" name="sync_action" value="universities">
            
            <label for="uni-country-filter"><strong>Filter by Country:</strong></label>
            <select name="country_filter" id="uni-country-filter">
                <option value="">All Countries</option>
                <?php if (!empty($countries) && !is_wp_error($countries)): ?>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?php echo esc_attr($country->name); ?>">
                            <?php echo esc_html($country->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            
            <button type="submit" class="button button-primary sync-button">
                Sync Universities
            </button>
        </form>
        <div class="sync-progress" id="progress-universities"></div>
    </div>
    
    <!-- Sync Programs -->
    <div class="sync-card">
        <h2>📚 Sync Programs</h2>
        <p class="sync-description">Fetch and sync programs from Zoho CRM. You can filter by country to sync only programs from specific universities.</p>
        
        <form method="post" id="sync-programs-form" class="sync-form">
            <?php wp_nonce_field('sit_sync_action', 'sit_sync_nonce'); ?>
            <input type="hidden" name="sync_action" value="programs">
            
            <label for="prog-country-filter"><strong>Filter by Country:</strong></label>
            <select name="country_filter" id="prog-country-filter">
                <option value="">All Countries</option>
                <?php if (!empty($countries) && !is_wp_error($countries)): ?>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?php echo esc_attr($country->name); ?>">
                            <?php echo esc_html($country->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            
            <button type="submit" class="button button-primary sync-button">
                Sync Programs
            </button>
        </form>
        <div class="sync-progress" id="progress-programs"></div>
    </div>
    
    <!-- Sync Other Taxonomies -->
    <div class="sync-card">
        <h2>🏷️ Sync Other Data</h2>
        <p class="sync-description">Sync degrees, faculties, languages, and specialities.</p>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form method="post" style="display: inline;">
                <?php wp_nonce_field('sit_sync_action', 'sit_sync_nonce'); ?>
                <input type="hidden" name="sync_action" value="degrees">
                <button type="submit" class="button sync-button">Sync Degrees</button>
            </form>
            
            <form method="post" style="display: inline;">
                <?php wp_nonce_field('sit_sync_action', 'sit_sync_nonce'); ?>
                <input type="hidden" name="sync_action" value="faculties">
                <button type="submit" class="button sync-button">Sync Faculties</button>
            </form>
            
            <form method="post" style="display: inline;">
                <?php wp_nonce_field('sit_sync_action', 'sit_sync_nonce'); ?>
                <input type="hidden" name="sync_action" value="languages">
                <button type="submit" class="button sync-button">Sync Languages</button>
            </form>
            
            <form method="post" style="display: inline;">
                <?php wp_nonce_field('sit_sync_action', 'sit_sync_nonce'); ?>
                <input type="hidden" name="sync_action" value="specialities">
                <button type="submit" class="button sync-button">Sync Specialities</button>
            </form>
        </div>
    </div>
    
    <?php endif; ?>
    
    <!-- Help Section -->
    <div class="sync-card">
        <h2>❓ Help & Information</h2>
        
        <h3>How to Use:</h3>
        <ol>
            <li><strong>Sync Countries First:</strong> Always sync countries before other data.</li>
            <li><strong>Sync Cities:</strong> Sync all cities from Zoho.</li>
            <li><strong>Sync Universities:</strong> Choose "All Countries" or select a specific country (e.g., Spain).</li>
            <li><strong>Sync Programs:</strong> Filter by country to sync only programs from specific universities.</li>
        </ol>
        
        <h3>For Spain Only:</h3>
        <ol>
            <li>Sync Countries (to ensure Spain exists)</li>
            <li>Sync Cities</li>
            <li>Sync Universities → Select "Spain" from dropdown</li>
            <li>Sync Programs → Select "Spain" from dropdown</li>
        </ol>
        
        <h3>Notes:</h3>
        <ul>
            <li>Syncing may take a few minutes depending on data volume.</li>
            <li>Existing data will be updated, not duplicated.</li>
            <li>The page will reload after each sync to show updated statistics.</li>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Show progress when form is submitted
    $('form[id^="sync-"]').on('submit', function() {
        var formId = $(this).attr('id');
        var progressId = formId.replace('form', 'progress');
        $('#' + progressId).addClass('active').html('⏳ Syncing... Please wait, this may take a few minutes.');
        $(this).find('button').prop('disabled', true).text('Syncing...');
    });
});
</script>
