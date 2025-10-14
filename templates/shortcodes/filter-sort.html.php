<?php
// Start session only if not already started and headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$degree_term = get_term($degreeid, 'sit-degree');
$country_term = get_term($countryid, 'sit-country');
$speciality_term = get_term($specialityid, 'sit-speciality');

// Check if terms are valid (not WP_Error)
$degree_valid = $degree_term && !is_wp_error($degree_term);
$country_valid = $country_term && !is_wp_error($country_term);
$speciality_valid = $speciality_term && !is_wp_error($speciality_term);

if($speciality_valid && $degree_valid && $country_valid){
    $heading = $degree_term->name . ' ' . $speciality_term->name . ' Courses In ' . $country_term->name;
} elseif($speciality_valid && $country_valid) {
    $heading = $speciality_term->name . ' Courses In ' . $country_term->name;
} elseif($speciality_valid && $degree_valid) {
    $heading = $degree_term->name . ' ' . $speciality_term->name . ' Courses';
} elseif($speciality_valid) {
    $heading = $speciality_term->name . ' Courses';
} else {
    $heading = "Search For Course";
}

?>

<!-- Filter styles are embedded in this file for now -->

<?php
// Prepare data for shared templates
$results_count = isset($query) ? $query->found_posts : 0;
$search_value = isset($_GET['search']) ? $_GET['search'] : '';

// Configure which filters to show for this page
$filter_config = [
    'degree' => true,
    'duration' => true,
    'language' => true,
    'price' => true,
    'university' => true,
    'scholarship' => true
];

// Prepare filter data
$filter_data = [
    'degrees' => isset($all_degrees) ? $all_degrees : [],
    'universities' => isset($all_universities_for_filter) ? $all_universities_for_filter : []
];
?>

<div class="header-container">
    <div class="header-title">
        <h1><?= $heading ?></h1>
    </div>
    <div class="header-info">
        <span class="courses-found"><?= $query->found_posts ?> courses found</span>
        <div class="search-by-name">
            <?php 
            $search_value = '';
            if (isset($_GET['search'])) {
                $search_value = is_array($_GET['search']) ? '' : esc_attr($_GET['search']);
            }
            ?>
            <input type="text" id="search-university" value="<?= $search_value ?>" placeholder="Search by name..." />
            <button>Go</button>
        </div>
        <div class="header-actions">
            <!-- Mobile Filter Toggle -->
            <button class="mobile-filter-toggle" onclick="toggleMobileFilters()">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16.7077 9.00023H6.41185M2.77768 9.00023H1.29102M2.77768 9.00023C2.77768 8.51842 2.96908 8.05635 3.30977 7.71566C3.65046 7.37497 4.11254 7.18357 4.59435 7.18357C5.07616 7.18357 5.53824 7.37497 5.87893 7.71566C6.21962 8.05635 6.41102 8.51842 6.41102 9.00023C6.41102 9.48204 6.21962 9.94412 5.87893 10.2848C5.53824 10.6255 5.07616 10.8169 4.59435 10.8169C4.11254 10.8169 3.65046 10.6255 3.30977 10.2848C2.96908 9.94412 2.77768 9.48204 2.77768 9.00023ZM16.7077 14.5061H11.9177M11.9177 14.5061C11.9177 14.988 11.7258 15.4506 11.3851 15.7914C11.0443 16.1321 10.5821 16.3236 10.1002 16.3236C9.61837 16.3236 9.1563 16.1313 8.81561 15.7906C8.47491 15.45 8.28352 14.9879 8.28352 14.5061M11.9177 14.5061C11.9177 14.0241 11.7258 13.5624 11.3851 13.2216C11.0443 12.8808 10.5821 12.6894 10.1002 12.6894C9.61837 12.6894 9.1563 12.8808 8.81561 13.2215C8.47491 13.5622 8.28352 14.0243 8.28352 14.5061M8.28352 14.5061H1.29102M16.7077 3.4944H14.1202M10.486 3.4944H1.29102M10.486 3.4944C10.486 3.01259 10.6774 2.55051 11.0181 2.20982C11.3588 1.86913 11.8209 1.67773 12.3027 1.67773C12.5413 1.67773 12.7775 1.72472 12.9979 1.81602C13.2183 1.90732 13.4186 2.04113 13.5873 2.20982C13.756 2.37852 13.8898 2.57878 13.9811 2.79919C14.0724 3.0196 14.1193 3.25583 14.1193 3.4944C14.1193 3.73297 14.0724 3.9692 13.9811 4.18961C13.8898 4.41002 13.756 4.61028 13.5873 4.77898C13.4186 4.94767 13.2183 5.08149 12.9979 5.17278C12.7775 5.26408 12.5413 5.31107 12.3027 5.31107C11.8209 5.31107 11.3588 5.11967 11.0181 4.77898C10.6774 4.43829 10.486 3.97621 10.486 3.4944Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"></path>
                </svg>
                Filters
            </button>
            
            <select class="sort-dropdown">
                <option <?php if (isset($_GET['sort'])) {
                    if ($_GET['sort'] == "newest") {
                        echo "selected";
                    }
                } ?> value="newest">Sort by Newest
                </option>
                <option <?php if (isset($_GET['sort'])) {
                    if ($_GET['sort'] == "fee_low") {
                        echo "selected";
                    }
                } ?> value="fee_low">Sort by Tuition Fee Low
                </option>
                <option <?php if (isset($_GET['sort'])) {
                    if ($_GET['sort'] == "fee_high") {
                        echo "selected";
                    }
                } ?> value="fee_high">Sort by Tuition Fee High
                </option>
                <option <?php if (isset($_GET['sort'])) {
                    if ($_GET['sort'] == "popular") {
                        echo "selected";
                    }
                } ?> value="popular">Sort by Popular
                </option>
            </select>
            
            <!-- View Toggle -->
            <div class="view-toggle" role="group" aria-label="View toggle">
                <button class="view-btn active" data-view="grid" aria-label="Grid view" aria-pressed="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Grid
                </button>
                <button class="view-btn" data-view="list" aria-label="List view" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    List
                </button>
            </div>

            <button onclick="openExportPopup()" id="openExportPopup" class="export-btn" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                    <path d="M10 9H8"></path>
                    <path d="M16 13H8"></path>
                    <path d="M16 17H8"></path>
                </svg> 
                Export PDF
            </button>
        </div>
    </div>
</div>

<!-- Main Container with Sidebar Layout -->
<div class="filter-results-container">
    <!-- Filter Sidebar -->
    <div class="filter-sidebar" id="filterSidebar">
        <div class="filter-sidebar-content">
            <!-- Sidebar Header -->
            <div class="filter-sidebar-header">
                <h3 class="filter-sidebar-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="21" x2="4" y2="14"></line>
                        <line x1="4" y1="10" x2="4" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12" y2="3"></line>
                        <line x1="20" y1="21" x2="20" y2="16"></line>
                        <line x1="20" y1="12" x2="20" y2="3"></line>
                        <line x1="1" y1="14" x2="7" y2="14"></line>
                        <line x1="9" y1="8" x2="15" y2="8"></line>
                        <line x1="17" y1="16" x2="23" y2="16"></line>
                    </svg>
                    Filters
                </h3>
                <button class="clear-all-filters">Clear All</button>
            </div>

            <!-- Applied Filters -->
            <div class="applied-filters-sidebar" id="appliedFiltersSidebar" style="display: none;">
                <h4 class="applied-filters-title">Applied Filters</h4>
                <div class="applied-filters-list filtersApplied">
                    <!-- Applied filters will be populated here by JavaScript -->
                </div>
            </div>

            <!-- Degree Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">🎓 Degree Level</h4>
                <div class="filter-options">
                    <?php
                    $selected_degrees = isset($_GET['level']) ? (is_array($_GET['level']) ? $_GET['level'] : [$_GET['level']]) : [];
                    
                    if (isset($all_degrees) && !empty($all_degrees)) {
                        foreach ($all_degrees as $degree_term) {
                            $is_checked = in_array($degree_term->term_id, $selected_degrees) ? 'checked' : '';
                            $active_class = in_array($degree_term->term_id, $selected_degrees) ? 'active' : '';
                            echo '<label class="filter-checkbox-label ' . $active_class . '">';
                            echo '<input type="checkbox" class="degree-checkbox" value="' . esc_attr($degree_term->term_id) . '" ' . $is_checked . '>';
                            echo '<span class="filter-checkbox-text">' . esc_html($degree_term->name) . '</span>';
                            echo '</label>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Duration Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">⏱️ Duration</h4>
                <div class="filter-button-group">
                    <?php
                    // Use durations from actual search results instead of hardcoded list
                    $current_duration = isset($_GET['duration']) ? $_GET['duration'] : '';
                    $durations = isset($all_durations_for_filter) && !empty($all_durations_for_filter) ? $all_durations_for_filter : [];
                    
                    foreach ($durations as $duration) {
                        // Format duration display
                        $duration_display = $duration;
                        if (is_numeric($duration)) {
                            $duration_display = $duration . ' year' . ($duration > 1 ? 's' : '');
                        }
                        
                        $active_class = ($current_duration == $duration) ? 'active' : '';
                        echo '<button class="filter-button ' . $active_class . '" data-filter="duration" data-value="' . esc_attr($duration) . '">' . esc_html($duration_display) . '</button>';
                    }
                    ?>
                </div>
            </div>

            <!-- Language Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">🌐 Language</h4>
                <div class="filter-options">
                    <?php
                    // Use available languages from search results instead of all languages
                    $current_language = isset($_GET['language']) ? $_GET['language'] : '';
                    
                    if (isset($available_languages) && !empty($available_languages)) {
                        foreach ($available_languages as $language) {
                            $language_name = str_replace('%', ' ', $language->name);
                            $is_checked = ($current_language == $language->term_id) ? 'checked' : '';
                            $active_class = ($current_language == $language->term_id) ? 'active' : '';
                            echo '<label class="filter-checkbox-label ' . $active_class . '">';
                            echo '<input type="checkbox" class="language-checkbox" value="' . esc_attr($language->term_id) . '" ' . $is_checked . '>';
                            echo '<span class="filter-checkbox-text">' . esc_html($language_name) . '</span>';
                            echo '</label>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Price Range Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">💰 Annual Fee (USD)</h4>
                <div class="price-range-inputs">
                    <?php 
                    $min_fee = '';
                    if (isset($_GET['min_fee']) && !is_array($_GET['min_fee'])) {
                        $min_fee = esc_attr($_GET['min_fee']);
                    }
                    $max_fee = '';
                    if (isset($_GET['max_fee']) && !is_array($_GET['max_fee'])) {
                        $max_fee = esc_attr($_GET['max_fee']);
                    }
                    ?>
                    <input type="number" class="price-input" placeholder="Min" id="minPrice" value="<?= $min_fee ?>">
                    <span class="price-separator">-</span>
                    <input type="number" class="price-input" placeholder="Max" id="maxPrice" value="<?= $max_fee ?>">
                </div>
            </div>

            <!-- University Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">🏫 University</h4>
                <div class="filter-options">
                    <?php
                    $current_universities = isset($all_universities_for_filter) ? $all_universities_for_filter : [];
                    $selected_universities = isset($_GET['university']) ? (is_array($_GET['university']) ? $_GET['university'] : [$_GET['university']]) : [];
                    
                    foreach ($current_universities as $uni_name) {
                        $is_checked = in_array($uni_name, $selected_universities) ? 'checked' : '';
                        $active_class = in_array($uni_name, $selected_universities) ? 'active' : '';
                        echo '<label class="filter-checkbox-label ' . $active_class . '">';
                        echo '<input type="checkbox" class="university-checkbox" value="' . esc_attr($uni_name) . '" ' . $is_checked . '>';
                        echo '<span class="filter-checkbox-text">' . esc_html($uni_name) . '</span>';
                        echo '</label>';
                    }
                    ?>
                </div>
            </div>

            <!-- Scholarships Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">🎯 Scholarships</h4>
                <div class="filter-button-group">
                    <?php
                    $current_scholarship = isset($_GET['isScholarShip']) ? $_GET['isScholarShip'] : '';
                    $active_class = ($current_scholarship == 'Yes') ? 'active' : '';
                    ?>
                    <button class="filter-button <?= $active_class ?>" data-filter="scholarship" data-value="Yes">Available</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Main Content -->
    <div class="results-main-content">
        <!-- GRID VIEW: Default view -->
        <div class="filter-results" id="programsGridContainer">
            <?php
            if (isset($programs) && !empty($programs)) {
                echo '<!-- Programs found: ' . count($programs) . ' -->';
                foreach ($programs as $program) {
                    \SIT\Search\Services\Template::render('shortcodes/program-box', ['program' => $program]);
                }
            } else {
                echo '<!-- No programs variable found or empty -->';
                echo '<div style="padding: 20px; text-align: center; color: #666;">No programs found or programs variable not set.</div>';
            }
            ?>
        </div>

<!-- LIST VIEW: Compact mobile-optimized view -->
<div class="all-faculties-program-list" id="programsListContainer" style="display: none;">
    <?php
    foreach ($programs as $program) {
        ?>
        <div class="program-list-item">
            <div class="program-list-image">
                <?php if (!empty($program['image_url'])): ?>
                    <img src="<?php echo $program['image_url']; ?>" alt="<?php echo $program['title']; ?>">
                <?php else: ?>
                    <div class="program-list-placeholder">🏫</div>
                <?php endif; ?>
            </div>
            
            <div class="program-list-content">
                <div class="program-list-info">
                    <h3 class="program-list-title"><?php echo $program['title']; ?></h3>
                    <p class="program-list-university"><?php echo $program['uni_title']; ?></p>
                    
                    <div class="program-list-details">
                        <span class="program-list-detail">
                            🕒 <?php echo $program['duration']; ?>
                        </span>
                        <span class="program-list-detail">
                            🌐 <?php 
                            // Extract language from title if it's in parentheses at the end
                            if (preg_match('/\(([^)]+)\)$/', $program['title'], $matches)) {
                                echo $matches[1];
                            } else {
                                // Fallback to a default or extract from other fields
                                echo 'English'; // or extract from other program data
                            }
                            ?>
                        </span>
                        <span class="program-list-detail">
                            📍 <?php echo $program['country']; ?>
                        </span>
                        <?php if (!empty($program['ranking'])): ?>
                        <span class="program-list-detail">
                            ⭐ Ranking: <?php echo $program['ranking']; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="program-list-right">
                    <div class="program-list-fee">
                        <?php if (!empty($program['discounted_fee'])): ?>
                            <span class="program-list-original-fee"><?php echo $program['fee']; ?> USD</span>
                            <span class="program-list-discounted-fee"><?php echo $program['discounted_fee']; ?> USD</span>
                        <?php else: ?>
                            <span class="program-list-current-fee"><?php echo $program['fee']; ?> USD</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="program-list-actions">
                        <?php 
                        // Simple and safe approach
                        $program_link = isset($program['link']) ? $program['link'] : '#';
                        
                        // Apply URL logic
                        $uni_id = $program['uni_id'] ?? '';
                        if ($uni_id) {
                            $apply_url = 'https://search.studyinturkiye.com/apply/?prog_id=' . $uni_id;
                        }
                        ?>
                        <a href="<?php echo esc_url($program_link); ?>" class="program-list-btn program-list-btn-primary">View Details</a>
                        <a href="<?php echo esc_url($apply_url); ?>" class="program-list-btn program-list-btn-outline" target="_blank">Apply</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
    </div>
</div>

<div class="filter-pagination">
    <?php
    global $wp;
    $big = 999999999;

    // Preserve existing query parameters
    $base_url = esc_url(add_query_arg($_GET, get_pagenum_link($big)));
    $clean_url = strstr($base_url, '#', true) ?: $base_url;
    $paginate_links = paginate_links(array(
        'base' => str_replace($big, '%#%', $clean_url),
        'format' => '&paged=%#%', // Use & instead of ? since params already exist
        'current' => max(1, get_query_var('paged')),
        'total' => $query->max_num_pages,
        'prev_text' => '<img src="https://search.studyinturkiye.com/wp-content/uploads/2025/03/reshot-icon-arrow-chevron-left-975UQXVKZF.svg" alt="Previous">',
        'next_text' => '<img src="https://search.studyinturkiye.com/wp-content/uploads/2025/03/reshot-icon-arrow-chevron-right-WDGHUKQ634.svg" alt="Next">',
    ));

    if ($paginate_links) {
        echo '<div class="pagination">' . $paginate_links . '</div>';
    }
    ?>
</div>

<div class="related-result" style="" bis_skin_checked="1">
    <h4 class="related-title">Related searches</h4>
    <?php
    if (!empty($degreeid) && $degreeid != '0' && !empty($countryid) && $countryid != '0') {
        $tax_query_rec = array('relation' => 'AND');
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-degree',
            'field' => 'term_id',
            'terms' => $degreeid,
        );
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-country',
            'field' => 'term_id',
            'terms' => $countryid,
        );
        $args_recom = array(
            'post_type' => 'sit-program',
            'posts_per_page' => 20,
            'post_status' => 'publish',
        );
        $args_recom['tax_query'] = $tax_query_rec;
        $recommended = new \WP_Query($args_recom);
        $rem_programs = $recommended->get_posts();
        if ($recommended->found_posts > 0) {
            ?>
            <div class="related-row" bis_skin_checked="1">
                <h3 class="">
                    <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/open-book-1.png"
                         alt="open-book open-book">
                    Recommended study areas</h3>
                <ul class="related-list studyarea">
                    <?php
                    foreach ($rem_programs as $program) {
                        ?>
                        <li><a href="<?= $program->guid ?>"><?= $program->post_title ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>
        <?php
    }
    ?>
    <?php
    if (!empty($degreeid) && $degreeid != '0' && !empty($specialityid) && $specialityid != '0') {
        $tax_query_rec = array('relation' => 'AND');
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-degree',
            'field' => 'term_id',
            'terms' => $degreeid,
        );
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-speciality',
            'field' => 'term_id',
            'terms' => $specialityid,
        );
        $args_recom = array(
            'post_type' => 'sit-program',
            'posts_per_page' => 20,
            'post_status' => 'publish',
        );
        $args_recom['tax_query'] = $tax_query_rec;
        $recommended = new \WP_Query($args_recom);
        $rem_programs = $recommended->get_posts();
        if ($recommended->found_posts > 0) {
            ?>
            <div class="related-row" bis_skin_checked="1">
                <h3 class="">
                    <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/open-book-1.png"
                         alt="open-book open-book">
                    Recommended destinations</h3>
                <ul class="related-list studydestination">
                    <?php
                    foreach ($rem_programs as $program) {
                        ?>
                        <li><a href="<?= $program->guid ?>"><?= $program->post_title ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>
        <?php
    }
    ?>
    <?php
    if (!empty($specialityid) && $specialityid != '0' && !empty($countryid) && $countryid != '0') {
        $tax_query_rec = array('relation' => 'AND');
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-speciality',
            'field' => 'term_id',
            'terms' => $specialityid,
        );
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-country',
            'field' => 'term_id',
            'terms' => $countryid,
        );
        $args_recom = array(
            'post_type' => 'sit-program',
            'posts_per_page' => 20,
            'post_status' => 'publish',
        );
        $args_recom['tax_query'] = $tax_query_rec;
        $recommended = new \WP_Query($args_recom);
        $rem_programs = $recommended->get_posts();
        if ($recommended->found_posts > 0) {
            ?>
            <div class="related-row" bis_skin_checked="1">
                <h3 class="">
                    <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/open-book-1.png"
                         alt="open-book open-book">
                    Recommended study levels</h3>
                <ul class="related-list studylevel">
                    <?php
                    foreach ($rem_programs as $program) {
                        ?>
                        <li><a href="<?= $program->guid ?>"><?= $program->post_title ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>
        <?php
    }
    ?>
</div>
<!-- Export Popup -->
<div class="export-overlay" id="exportModal">
    <div class="export-popup">
        <div class="export-header">
            <div class="headers-info">
                <h2>Academic Programs in Turkey</h2>
                <p class="generated-date">Generated on: <?php echo date('Y-m-d'); ?></p>
            </div>
            <div class="header-action">
                <button onclick="downloadPDF()" class="print-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer h-4 w-4"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path><rect x="6" y="14" width="12" height="8" rx="1"></rect></svg> Print/Save PDF</button>
                <p>Total Programs:<?= count($pdf_program); ?></p>
            </div>
            <button class="export-btn" onclick="exportToPDF()">Export PDF</button>
            <button class="close-export" onclick="closeExportPopup()">×</button>
        </div>

        <p><?= $disstr ?></p>

        <h3>Program Listing</h3>
        <table class="program-table" id="table-program">
            <thead>
            <tr>
                <th>Program</th>
                <th>University</th>
                <th>Duration</th>
                <th>Language</th>
                <th>Deadline</th>
                <th>Tuition</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($pdf_program as $program) {
                if(!empty($program['discounted_fee'])){
                    $fee='<span>'.$program['fee'].' USD</span>'.$program['discounted_fee'].' USD';
                }
                else{
                    $fee=$program['fee'].' USD';
                }
                ?>
                <tr>
                    <td><?= $program['title'] ?></td>
                    <td><?= $program['uni_title'] ?></td>
                    <td><?= $program['duration'] ?></td>
                    <td>English</td>
                    <td>May 15, 2024</td>
                    <td><?= $fee ?></td>
                </tr>
                <?php
            }
            ?>
            <!-- Add more rows as needed -->
            </tbody>
        </table>

        <h3>Program Details</h3>
        <?php
        foreach ($pdf_program as $program) {
        ?>
            <div class="program-card">
                <div class="university-image">
                    <img src="<?= $program['image_url'] ?>" alt="">
                </div>
                <div class="program-detail">
                    <div class="program-title"><?= $program['title'] ?></div>
                    <div class="uni-name"><?= $program['uni_title'] ?></div>
                    <div class="program-info-grid">
                        <div class="info-item">
                            <span class="icon">🕒</span>
                            <span><?= $program['duration'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="icon">🌐</span>
                            <span>English</span>
                        </div>
                        <div class="info-item">
                            <span class="icon">📍</span>
                            <span><?= $program['country'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="icon">💲</span>
                            <span><?= $program['fee'] ?> USD</span>
                        </div>
                        <div class="info-item">
                            <span class="icon">💳</span>
                            <span>Rankings: <?= $program['ranking'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="icon">📅</span>
                            <span>Students: <?= $program['students'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
        <!-- Repeat .program-card blocks for other programs -->

        <div class="contact-info">
            <h4>Contact Information</h4>
            <p>
                For more information about these programs or assistance with your application, please contact our support team.<br>
                Email: support@studyinturkey.com<br>
                Website: www.studyinturkey.com
            </p>
        </div>
        <div class="footer-popup">
            <p>© 2025 Study in Turkey. All rights reserved.</p>
            <p>This document was generated for informational purposes only.</p>
        </div>
    </div>
</div>
<div id="expoting-download" >
    <div class="export-popup">
        <div class="export-header">
            <div class="headers-info">
                <h2>Academic Programs in Turkey</h2>
                <p class="generated-date">Generated on: <?php echo date('Y-m-d'); ?></p>
            </div>
            <div class="header-action">
                <p>Total Programs:<?= count($pdf_program); ?></p>
            </div>
        </div>

        <p><?= $disstr ?></p>

        <h3>Program Listing</h3>
        <table class="program-table" id="table-program">
            <thead>
            <tr>
                <th>Program</th>
                <th>University</th>
                <th>Duration</th>
                <th>Language</th>
                <th>Deadline</th>
                <th>Tuition</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($pdf_program as $program) {
                if(!empty($program['discounted_fee'])){
                    $fee='<span style=" text-decoration: line-through;display: block;">'.$program['fee'].' USD</span>'.$program['discounted_fee'].' USD';
                }
                else{
                    $fee=$program['fee'].' USD';
                }
                ?>
                <tr>
                    <td><?= $program['title'] ?></td>
                    <td><?= $program['uni_title'] ?></td>
                    <td><?= $program['duration'] ?></td>
                    <td>English</td>
                    <td>May 15, 2024</td>
                    <td><?= $fee ?></td>
                </tr>
                <?php
            }
            ?>
            <!-- Add more rows as needed -->
            </tbody>
        </table>

        <div class="contact-info">
            <h4>Contact Information</h4>
            <p>
                For more information about these programs or assistance with your application, please contact our support team.<br>
                Email: support@studyinturkey.com<br>
                Website: www.studyinturkey.com
            </p>
        </div>
        <div class="footer-popup">
            <p>© 2025 Study in Turkey. All rights reserved.</p>
            <p>This document was generated for informational purposes only.</p>
        </div>
    </div>
</div>
<script>
    function closeExportPopup() {
        document.getElementById("exportModal").style.display = "none";
    }

    function openExportPopup() {
        document.getElementById("exportModal").style.display = "flex";
    }
</script>
<script>
    function downloadPDF() {
        const originalElement = document.getElementById("expoting-download");

        // Clone the original element
        const clone = originalElement.cloneNode(true);

        // Remove contact info and footer if present in the clone
        const contactInfo = clone.querySelector('.contact-info');
        const footer = clone.querySelector('.footer-popup');
        const table = clone.querySelector("table");

        if (contactInfo) contactInfo.remove();
        if (footer) footer.remove();

        if (!table) {
            alert("No table found!");
            return;
        }

        // Extract the table's thead and tbody rows
        const rows = Array.from(table.querySelectorAll("tbody tr"));
        const thead = table.querySelector("thead")?.cloneNode(true);

        // Remove the original table from the cloned content
        table.remove();

        // --- Get header content (before the table) ---
        const headerContent = document.createElement("div");
        let reachedTable = false;
        Array.from(clone.childNodes).forEach(node => {
            if (node.nodeType === 1 && node.tagName === "TABLE") {
                reachedTable = true;
            }
            if (!reachedTable) {
                headerContent.appendChild(node.cloneNode(true));
            }
        });

        // --- Add logo to top-right of headerContent ---
        const logoWrapper = document.createElement("div");
        logoWrapper.style.display = "flex";
        logoWrapper.style.justifyContent = "space-between";
        logoWrapper.style.alignItems = "center";

        const spacer = document.createElement("div");
        const logo = document.createElement("img");
        logo.src = "https://search.studyinturkiye.com/wp-content/uploads/2025/02/image-1-1-e1738931290741.png";
        logo.style.maxWidth = "120px";
        logo.style.marginBottom = "20px";

        logoWrapper.appendChild(spacer);
        logoWrapper.appendChild(logo);
        headerContent.insertBefore(logoWrapper, headerContent.firstChild);

        // Create wrapper for all pages
        const wrapper = document.createElement("div");

        let i = 0;
        let pageIndex = 0;

        while (i < rows.length) {
            const newTable = document.createElement("table");
            newTable.style.width = "100%";
            newTable.style.borderCollapse = "collapse";
            if (thead) newTable.appendChild(thead.cloneNode(true));

            const tbody = document.createElement("tbody");

            // Determine how many rows this page should have
            const rowsPerPage = pageIndex === 0 ? 4 : 8;

            for (let j = i; j < i + rowsPerPage && j < rows.length; j++) {
                tbody.appendChild(rows[j].cloneNode(true));
            }

            newTable.appendChild(tbody);

            const pageDiv = document.createElement("div");
            pageDiv.style.pageBreakAfter = "always";

            if (pageIndex === 0) {
                pageDiv.appendChild(headerContent.cloneNode(true));
            }

            pageDiv.appendChild(newTable);
            wrapper.appendChild(pageDiv);

            i += rowsPerPage;
            pageIndex++;
        }

        // Final page: contact info + footer
        const finalPage = document.createElement("div");
        finalPage.style.padding = "20px";
        finalPage.innerHTML = `
            <div class="contact-info">
                <h4>Contact Information</h4>
                <p>
                    For more information about these programs or assistance with your application, please contact our support team.<br>
                    Email: support@studyinturkey.com<br>
                    Website: www.studyinturkey.com
                </p>
            </div>
            <div class="footer-popup" style="margin-top: 40px; text-align: center;">
                <p>© 2025 Study in Turkey. All rights reserved.</p>
                <p style="font-style: italic; font-size: 12px;">This document was generated for informational purposes only.</p>
            </div>
        `;
        wrapper.appendChild(finalPage);

        // Generate the PDF
        const options = {
            margin: 10,
            filename: 'Academic_Programs_Turkey.pdf',
            image: { type: 'png', quality: 1 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: false,
                letterRendering: true
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().from(wrapper).set(options).save().catch(function (error) {
            console.error("Error generating PDF:", error);
        });
    }

</script>

<style>
/* View Toggle Styles */
.view-toggle {
    display: flex;
    
    background: #f8f9fa;
    overflow: hidden;
    margin-right: 12px;
}

.view-toggle button {
    padding: 10px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    transition: all 0.2s ease;
    color: #6c757d;
}

.view-toggle button.active {
    background: var(--apply-primary);
    color: white;
}

.view-toggle button:hover:not(.active) {
    background: #e9ecef;
    color: #495057;
}

/* Debug: Ensure List button is visible - VERY AGGRESSIVE */
.view-toggle button[data-view="list"] {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
    min-width: 80px !important;
    height: 40px !important;
    color: #000 !important;
    font-weight: bold !important;
    z-index: 9999 !important;
    position: relative !important;
}

/* Also make the container more visible */
.view-toggle {

    min-width: 150px !important;
}

/* List View Styles - Same as program-archive.html.php */
.all-faculties-program-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
}

.program-list-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.program-list-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: var(--apply-primary);
}

.program-list-image {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.program-list-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.program-list-placeholder {
    font-size: 24px;
    color: #6c757d;
}

.program-list-content {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.program-list-info {
    flex: 1;
}

.program-list-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.program-list-university {
    color: #6c757d;
    font-size: 14px;
    margin: 0 0 12px 0;
}

.program-list-details {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.program-list-detail {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    color: #6c757d;
}

.program-list-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 16px;
    text-align: right;
}

.program-list-fee {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.program-list-original-fee {
    font-size: 14px;
    color: #6c757d;
    text-decoration: line-through;
    margin-bottom: 2px;
}

.program-list-discounted-fee,
.program-list-current-fee {
    font-size: 18px;
    font-weight: 600;
    color: var(--apply-primary);
}

.program-list-actions {
    display: flex;
    gap: 8px;
}

.program-list-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 80px;
}

.program-list-btn-primary {
    background: var(--apply-primary);
    color: white;
    border: 1px solid var(--apply-primary);
}

.program-list-btn-primary:hover {
    background: var(--apply-primary-dark);
    border-color: var(--apply-primary-dark);
    transform: translateY(-1px);
}

.program-list-btn-outline {
    background: transparent;
    color: var(--apply-primary);
    border: 1px solid var(--apply-primary);
}

.program-list-btn-outline:hover {
    background: var(--apply-primary);
    color: white;
    transform: translateY(-1px);
}

/* Mobile Responsive - Same compact design */
@media (max-width: 768px) {
    .view-toggle {
        order: -1;
        width: 100%;
        justify-content: center;
        margin-right: 0;
        margin-bottom: 12px;
    }
    
    .all-faculties-program-list {
        gap: 12px;
        padding: 0 4px;
    }
    
    .program-list-item {
        flex-direction: row;
        align-items: center;
        text-align: left;
        gap: 0;
        padding: 12px 16px;
        min-height: auto;
    }
    
    .program-list-image {
        display: none;
    }
    
    .program-list-content {
        flex-direction: row;
        gap: 12px;
        width: 100%;
        justify-content: space-between;
        align-items: center;
    }
    
    .program-list-info {
        text-align: left;
        flex: 1;
        min-width: 0;
    }
    
    .program-list-title {
        font-size: 15px;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    
    .program-list-university {
        font-size: 12px;
        margin-bottom: 6px;
        color: #666;
    }
    
    .program-list-right {
        align-items: flex-end;
        text-align: right;
        gap: 8px;
        min-width: 120px;
        flex-shrink: 0;
    }
    
    .program-list-details {
        justify-content: flex-start;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 2px;
    }
    
    .program-list-detail {
        font-size: 11px;
        padding: 2px 6px;
        background: #f0f0f0;
        border-radius: 3px;
        white-space: nowrap;
    }
    
    .program-list-fee {
        margin-bottom: 8px;
    }
    
    .program-list-original-fee {
        font-size: 11px;
    }
    
    .program-list-discounted-fee,
    .program-list-current-fee {
        font-size: 14px;
        font-weight: 600;
    }
    
    .program-list-actions {
        width: 100%;
        flex-direction: column;
        gap: 4px;
    }
    
    .program-list-btn {
        width: 100%;
        padding: 6px 10px;
        font-size: 11px;
        min-width: 0;
        white-space: nowrap;
    }
}

@media (max-width: 480px) {
    .all-faculties-program-list {
        gap: 8px;
        padding: 0 2px;
    }
    
    .program-list-item {
        padding: 10px 12px;
        gap: 0;
    }
    
    .program-list-btn {
        padding: 5px 8px;
        font-size: 10px;
    }
}
</style>

<script>
// View toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Check if buttons exist
    console.log('=== VIEW TOGGLE DEBUG ===');
    const viewToggle = document.querySelector('.view-toggle');
    console.log('View toggle container:', viewToggle);
    
    const viewButtons = document.querySelectorAll('.view-btn');
    console.log('View buttons found:', viewButtons.length);
    
    const gridButton = document.querySelector('[data-view="grid"]');
    const listButton = document.querySelector('[data-view="list"]');
    console.log('Grid button:', gridButton);
    console.log('List button:', listButton);
    
    if (listButton) {
        console.log('List button styles:', window.getComputedStyle(listButton));
        console.log('List button display:', window.getComputedStyle(listButton).display);
        console.log('List button visibility:', window.getComputedStyle(listButton).visibility);
    }
    
    const gridContainer = document.getElementById('programsGridContainer');
    const listContainer = document.getElementById('programsListContainer');
    
    if (!gridContainer || !listContainer) {
        return;
    }
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active button and aria-pressed attributes
            viewButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-pressed', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            
            const currentView = this.dataset.view;
            
            if (currentView === 'grid') {
                gridContainer.style.display = '';
                listContainer.style.display = 'none';
            } else if (currentView === 'list') {
                gridContainer.style.display = 'none';
                listContainer.style.display = 'flex';
            }
            
            localStorage.setItem('programView', currentView);
        });
    });
    
    // Restore saved view preference
    const savedView = localStorage.getItem('programView');
    if (savedView && savedView === 'list') {
        const listButton = document.querySelector('[data-view="list"]');
        if (listButton) {
            listButton.click();
        }
    }
});

// Mobile filter toggle functionality
function toggleMobileFilters() {
    const sidebar = document.getElementById('filterSidebar');
    sidebar.classList.toggle('mobile-hidden');
}
</script>
