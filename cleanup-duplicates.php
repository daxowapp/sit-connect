<?php
/**
 * Cleanup Duplicate Universities
 * 
 * This script removes duplicate university entries created by sync
 * Run this ONCE from WordPress admin or via WP-CLI
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Load WordPress
    require_once('../../../wp-load.php');
}

function sit_cleanup_duplicate_universities() {
    global $wpdb;
    
    echo "<h2>SIT Connect - Cleanup Duplicate Universities</h2>";
    echo "<p>Starting cleanup process...</p>";
    
    // Get all universities
    $universities = get_posts([
        'post_type' => 'sit-university',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'orderby' => 'date',
        'order' => 'ASC' // Keep oldest first
    ]);
    
    echo "<p>Found " . count($universities) . " total universities</p>";
    
    $duplicates_removed = 0;
    $no_zoho_id = 0;
    $kept_universities = [];
    $seen_titles = [];
    
    foreach ($universities as $university) {
        $zoho_id = get_field('zoho_account_id', $university->ID); // Use ACF function
        
        if (empty($zoho_id)) {
            // Try direct meta query
            $zoho_id = get_post_meta($university->ID, 'zoho_account_id', true);
        }
        
        if (empty($zoho_id)) {
            // No Zoho ID - check by title to find duplicates
            $title = $university->post_title;
            
            if (isset($seen_titles[$title])) {
                // Duplicate by title - delete it
                echo "<p style='color: red;'>❌ Deleting duplicate (no Zoho ID): '{$title}' (ID: {$university->ID})</p>";
                wp_delete_post($university->ID, true);
                $duplicates_removed++;
            } else {
                $seen_titles[$title] = $university->ID;
                echo "<p style='color: orange;'>⚠️ Keeping (no Zoho ID): '{$title}' (ID: {$university->ID})</p>";
                $no_zoho_id++;
            }
            continue;
        }
        
        // Check if we've already seen this Zoho ID
        if (isset($kept_universities[$zoho_id])) {
            // This is a duplicate - delete it
            echo "<p style='color: red;'>❌ Deleting duplicate: '{$university->post_title}' (ID: {$university->ID}, Zoho ID: {$zoho_id})</p>";
            wp_delete_post($university->ID, true); // true = force delete (skip trash)
            $duplicates_removed++;
        } else {
            // This is the first occurrence - keep it
            $kept_universities[$zoho_id] = $university->ID;
            echo "<p style='color: green;'>✅ Keeping: '{$university->post_title}' (ID: {$university->ID}, Zoho ID: {$zoho_id})</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Summary:</h3>";
    echo "<p><strong>Total universities found:</strong> " . count($universities) . "</p>";
    echo "<p><strong>Duplicates removed:</strong> $duplicates_removed</p>";
    echo "<p><strong>Universities kept (with Zoho ID):</strong> " . count($kept_universities) . "</p>";
    echo "<p><strong>Universities without Zoho ID:</strong> $no_zoho_id</p>";
    echo "<p style='color: green;'><strong>✅ Cleanup complete!</strong></p>";
    echo "<p style='color: blue;'><strong>ℹ️ You can now run sync again without creating duplicates.</strong></p>";
    
    return [
        'total' => count($universities),
        'removed' => $duplicates_removed,
        'kept' => count($kept_universities),
        'no_zoho_id' => $no_zoho_id
    ];
}

// Run if accessed directly
if (current_user_can('manage_options')) {
    sit_cleanup_duplicate_universities();
} else {
    echo "<p style='color: red;'>Error: You don't have permission to run this script.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SIT Connect - Cleanup Duplicates</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            color: #333;
            border-bottom: 3px solid #AA151B;
            padding-bottom: 10px;
        }
        p {
            padding: 8px;
            margin: 5px 0;
            background: white;
            border-left: 4px solid #ddd;
        }
        hr {
            margin: 30px 0;
            border: none;
            border-top: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <p><a href="<?php echo admin_url('admin.php?page=sit-connect'); ?>">← Back to Dashboard</a></p>
</body>
</html>
