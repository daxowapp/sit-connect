<?php
// Complete file with list view integration
// Configure which filters to show for this page (Program Archive - all filters)
$filter_config = [
    'degree' => true,      // Show degree filter
    'duration' => true,    // Show duration filter
    'language' => true,    // Show language filter
    'price' => true,       // Show price filter
    'university' => true,  // Show university filter
    'scholarship' => true  // Show scholarship filter
];

// Prepare filter data
$filter_data = [
    'degrees' => isset($all_degrees) ? $all_degrees : [],
    'universities' => isset($all_universities_for_filter) ? $all_universities_for_filter : []
];

// Set page-specific variables
$results_count = isset($query) ? $query->found_posts : 0;
$search_value = isset($_GET['search']) && !is_array($_GET['search']) ? $_GET['search'] : '';
?>

<style>
/* Essential Filter Sidebar Styles - Inline to ensure loading */
.filter-results-container {
    display: flex !important;
    gap: 24px !important;
    max-width: 1400px !important;
    margin: 0 auto !important;
    padding: 20px !important;
}

.filter-sidebar {
    width: 320px !important;
    min-width: 320px !important;
    background: #fff !important;
    border-radius: 12px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    padding: 0 !important;
    height: calc(100vh - 40px) !important;
    position: sticky !important;
    top: 20px !important;
    overflow-y: auto !important;
    display: flex !important;
    flex-direction: column !important;
    visibility: visible !important;
}

.filter-sidebar-content {
    padding: 24px !important;
    flex: 1 !important;
    overflow-y: auto !important;
    display: block !important;
    visibility: visible !important;
}

.filter-section {
    margin-bottom: 32px !important;
    display: block !important;
    visibility: visible !important;
}

.filter-section-title {
    font-size: 16px !important;
    font-weight: 600 !important;
    color: #1a1a1a !important;
    margin: 0 0 16px 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    visibility: visible !important;
}

.filter-options {
    display: flex !important;
    flex-direction: column !important;
    gap: 12px !important;
    visibility: visible !important;
}

.filter-checkbox-label {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 12px 16px !important;
    border: 1px solid #e9ecef !important;
    border-radius: 8px !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    background: white !important;
    visibility: visible !important;
}

.filter-button-group {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    margin-top: 8px !important;
    visibility: visible !important;
}

.filter-button {
    padding: 8px 16px !important;
    border: 1px solid #e9ecef !important;
    border-radius: 20px !important;
    background: white !important;
    color: #495057 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    display: inline-block !important;
    visibility: visible !important;
}

.filter-button:hover {
    border-color: var(--apply-primary) !important;
    background: #fef7f7 !important;
}

.filter-button.active {
    background: var(--apply-primary) !important;
    color: white !important;
    border-color: var(--apply-primary) !important;
}

.results-main-content {
    flex: 1 !important;
    min-width: 0 !important;
    display: block !important;
    visibility: visible !important;
}

.filter-sidebar-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    margin-bottom: 24px !important;
    padding-bottom: 16px !important;
    border-bottom: 1px solid #e9ecef !important;
    visibility: visible !important;
}

.filter-sidebar-title {
    font-size: 18px !important;
    font-weight: 600 !important;
    color: #1a1a1a !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    visibility: visible !important;
}

.clear-all-filters {
    background: none !important;
    border: none !important;
    color: var(--apply-primary) !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    padding: 4px 8px !important;
    border-radius: 4px !important;
    transition: background 0.2s !important;
    display: inline-block !important;
    visibility: visible !important;
}

.filter-checkbox-text {
    font-size: 14px !important;
    color: #495057 !important;
    font-weight: 500 !important;
    flex: 1 !important;
    display: block !important;
    visibility: visible !important;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .filter-results-container {
        flex-direction: column !important;
        padding: 16px !important;
    }
    
    .filter-sidebar {
        width: 100% !important;
        min-width: auto !important;
        position: static !important;
        order: 2 !important;
    }
    
    .results-main-content {
        order: 1 !important;
    }
    
    .mobile-filter-toggle {
        display: block !important;
        width: 100% !important;
        padding: 12px 20px !important;
        background: var(--apply-primary) !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        font-size: 16px !important;
        font-weight: 500 !important;
        margin-bottom: 16px !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
    }
    
    .filter-sidebar.mobile-hidden {
        display: none !important;
    }
}

@media (min-width: 1025px) {
    .mobile-filter-toggle {
        display: none !important;
    }
}

/* View Toggle Styles */
.view-toggle {
    display: flex;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    overflow: hidden;
    margin-right: 12px;
}

.view-toggle button {
    padding: 10px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 500;
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

.view-toggle svg {
    flex-shrink: 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .view-toggle {
        order: -1;
        width: 100%;
        justify-content: center;
        margin-right: 0;
        margin-bottom: 12px;
    }
}

/* List View Styles */
.all-faculties-program-list {
    width: 100%;
}

.program-list-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    margin-bottom: 16px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
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
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
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
    font-size: 14px;
    color: #6c757d;
    margin: 0 0 12px 0;
}

.program-list-details {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.program-list-detail {
    font-size: 13px;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 4px;
}

.program-list-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
}

.program-list-fee {
    text-align: right;
}

.program-list-current-fee {
    font-size: 18px;
    font-weight: 600;
    color: var(--apply-primary);
}

.program-list-original-fee {
    font-size: 14px;
    color: #6c757d;
    text-decoration: line-through;
    display: block;
}

.program-list-discounted-fee {
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
}

.program-list-btn-primary {
    background: var(--apply-primary);
    color: white;
    border-color: var(--apply-primary);
}

.program-list-btn-primary:hover {
    background: #c8090f;
    border-color: #c8090f;
    color: white;
    text-decoration: none;
}

.program-list-btn-outline {
    background: transparent;
    color: var(--apply-primary);
    border-color: var(--apply-primary);
}

.program-list-btn-outline:hover {
    background: var(--apply-primary);
    color: white;
    text-decoration: none;
}

/* Mobile Responsive for List View */
@media (max-width: 768px) {
    .program-list-item {
        flex-direction: row;
        align-items: center;
        text-align: left;
        padding: 16px;
        gap: 12px;
    }
    
    .program-list-image {
        width: 60px;
        height: 60px;
    }
    
    .program-list-content {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    
    .program-list-right {
        align-items: stretch;
        gap: 8px;
    }
    
    .program-list-actions {
        justify-content: stretch;
    }
    
    .program-list-btn {
        flex: 1;
        text-align: center;
        padding: 0 4px;
    }
}

@media (max-width: 480px) {
    .program-list-item {
        padding: 10px 12px;
        gap: 0;
    }
    
    .program-list-image {
        width: 50px;
        height: 50px;
    }
    
    .program-list-title {
        font-size: 16px;
    }
    
    .program-list-details {
        gap: 8px;
    }
    
    .program-list-detail {
        font-size: 12px;
    }
    
    .program-list-btn {
        padding: 0 2px;
    }
}
</style>

<!-- Cities Navigation Section -->
<div class="ProgramArchivePage-cities-section">
    <div class="ProgramArchivePage-container">
        <div class="ProgramArchivePage-cities-grid">
            <?php
            if (!empty($cities) && !is_wp_error($cities)) {
                foreach ($cities as $term) {
                    ?>
                    <a href="<?php echo get_term_link($term->term_id); ?>" class="ProgramArchivePage-city-card">
                      <div class="ProgramArchivePage-city-content">
                        <h3><?php echo $term->name; ?></h3>
                        <span class="ProgramArchivePage-city-arrow">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </span>
                      </div>
                    </a>
                    <?php
                }
            }
            ?>
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
            <h4 class="filter-section-title">
                🎓 Degree Level
            </h4>
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
                } else {
                    $fallback_degrees = get_terms(['taxonomy' => 'sit-degree', 'hide_empty' => false]);
                    if (!empty($fallback_degrees)) {
                        foreach ($fallback_degrees as $degree_term) {
                            $is_checked = in_array($degree_term->term_id, $selected_degrees) ? 'checked' : '';
                            $active_class = in_array($degree_term->term_id, $selected_degrees) ? 'active' : '';
                            echo '<label class="filter-checkbox-label ' . $active_class . '">';
                            echo '<input type="checkbox" class="degree-checkbox" value="' . esc_attr($degree_term->term_id) . '" ' . $is_checked . '>';
                            echo '<span class="filter-checkbox-text">' . esc_html($degree_term->name) . '</span>';
                            echo '</label>';
                        }
                    }
                }
                ?>
            </div>
        </div>

        <!-- Duration Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">
                ⏱️ Duration
            </h4>
            <div class="filter-button-group">
                <?php
                // Use durations from ALL results, not just current page
                $current_durations = isset($all_durations_for_filter) ? $all_durations_for_filter : [];
                $selected_duration = isset($_GET['duration']) ? $_GET['duration'] : '';
                
                foreach ($current_durations as $duration) {
                    $duration_text = $duration . ' year' . ($duration > 1 ? 's' : '');
                    $active_class = ($selected_duration == $duration_text) ? 'active' : '';
                    echo '<button class="filter-button ' . $active_class . '" data-filter="duration" data-value="' . esc_attr($duration_text) . '">';
                    echo esc_html($duration_text);
                    echo '</button>';
                }
                ?>
            </div>
        </div>

        <!-- Language Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">
                🌐 Language
            </h4>
            <div class="filter-options">
                <?php
                $selected_languages = isset($_GET['language']) ? (is_array($_GET['language']) ? $_GET['language'] : [$_GET['language']]) : [];
                
                if (isset($available_languages) && !empty($available_languages)) {
                    foreach ($available_languages as $lang_term) {
                        $is_checked = in_array($lang_term->name, $selected_languages) ? 'checked' : '';
                        $active_class = in_array($lang_term->name, $selected_languages) ? 'active' : '';
                        echo '<label class="filter-checkbox-label ' . $active_class . '">';
                        echo '<input type="checkbox" class="language-checkbox" value="' . esc_attr($lang_term->name) . '" ' . $is_checked . '>';
                        echo '<span class="filter-checkbox-text">' . esc_html($lang_term->name) . '</span>';
                        echo '</label>';
                    }
                } else {
                    // Fallback: If available_languages is not set, show message
                    echo '<p style="color: #666; font-size: 14px; font-style: italic;">No languages found in current results.</p>';
                }
                ?>
            </div>
        </div>

        <!-- Price Range Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">
                💰 Annual Fee (USD)
            </h4>
            <div class="price-range-inputs">
                <div class="price-input-group">
                    <label class="price-input-label">Min</label>
                    <input type="number" class="price-input min-range" placeholder="0" value="">
                </div>
                <div class="price-input-group">
                    <label class="price-input-label">Max</label>
                    <input type="number" class="price-input max-range" placeholder="50000" value="">
                </div>
            </div>
        </div>

        <!-- University Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">
                🏫 University
            </h4>
            <div class="filter-options">
                <?php
                // Use universities from ALL results, not just current page
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
            <h4 class="filter-section-title">
                🎯 Scholarships
            </h4>
            <div class="filter-button-group">
                <button class="filter-button" data-filter="isScholarShip" data-value="Yes">
                    Available
                </button>
            </div>
        </div>
        </div> <!-- End filter-sidebar-content -->
    </div>

    <!-- Results Main Content -->
    <div class="results-main-content">
        <!-- Mobile Filter Toggle -->
        <button class="mobile-filter-toggle" onclick="toggleMobileFilters()">
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
        </button>

        <!-- Program Results Grid -->
        <div class="all-faculties-program" id="programsGridContainer">
            <?php
            foreach ($programs as $university) {
                \SIT\Search\Services\Template::render('shortcodes/program-box-uni', ['program' => $university]);
            }
            ?>
        </div>
        
    </div> <!-- End results-main-content -->
</div> <!-- End filter-results-container -->

    <!-- New Header Section -->
    <div class="header-container">
        <div class="header-title">
            <div class="filter-header">
                <h4>All Programs in <?= $archiveitle ?></h4>
            </div>
        </div>
    <div class="header-info">
        <span class="courses-found"><?= $query->found_posts ?> Programs found</span>

        <div class="ProgramArchivePage-header-controls">
            <!-- Search Input -->
            <div class="ProgramArchivePage-search-wrapper">
                <div class="ProgramArchivePage-search-input">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="search-university" value="<?= $_GET['search'] ?>" placeholder="Search by name..." />
                    <button class="ProgramArchivePage-search-button">Go</button>
                </div>
            </div>
            
            <!-- Action Controls -->
            <div class="ProgramArchivePage-actions-wrapper">
                <!-- NEW: View Toggle -->
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

                <!-- Sort Dropdown -->
                <div class="ProgramArchivePage-sort-control">
                    <select class="sort-dropdown ProgramArchivePage-sort-select">
                        <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "newest") echo "selected"; ?> value="newest">
                            Sort by Newest
                        </option>
                        <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "fee_low") echo "selected"; ?> value="fee_low">
                            Sort by Tuition Fee Low
                        </option>
                        <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "fee_high") echo "selected"; ?> value="fee_high">
                            Sort by Tuition Fee High
                        </option>
                        <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "popular") echo "selected"; ?> value="popular">
                            Sort by Popular
                        </option>
                    </select>
                    <svg class="ProgramArchivePage-sort-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                
                <!-- Filter Button & Dropdown (Hidden - using sidebar now) -->
                <div class="ProgramArchivePage-filter-control filter-export" style="display: none;">
                    <div class="filters">
                        <!-- Mobile Filter Toggle -->
                        <button class="mobile-filter-toggle" onclick="toggleMobileFilters()">
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
                        </button>
                        
                        <!-- Hidden for now, will be moved to sidebar -->
                        <div class="sr-filter new-sr-filter d-none" style="display: none;">
                            <div class="filter-list" bis_skin_checked="1">
                                <div class="filter-head" bis_skin_checked="1">
                                    <h3 class="">
                                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 21V14" stroke="#646669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M4 10V3" stroke="#646669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M12 21V12" stroke="#646669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M12 8V3" stroke="#646669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M20 21V16" stroke="#646669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M20 12V3" stroke="#646669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M1 14H7" stroke="#646669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M9 8H15" stroke="#646669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M17 16H23" stroke="#646669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                        Filters
                                    </h3>
                                </div>
                                <!-- Selected Filters Display -->
                                <div class="selected-filters-display" bis_skin_checked="1" style="margin-bottom: 15px;">
                                    <div class="selected-filters-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <h4 style="margin: 0; font-size: 14px; color: #333;">Applied Filters</h4>
                                        <button class="clear-all-filters" style="background: none; border: none; color: #007cba; font-size: 12px; cursor: pointer; text-decoration: underline;">Clear All</button>
                                    </div>
                                    <div class="filtersApplied" style="display: flex; flex-wrap: wrap; gap: 8px; min-height: 20px;">
                                        <!-- Applied filters will be populated here by JavaScript -->
                                    </div>
                               

                                </div>

                                <div class="accordion" id="accordionPanelsStayOpenExample" bis_skin_checked="1">
                                    <div class="accordion-item" bis_skin_checked="1">
                                        <h2 class="accordion-header" id="panelsStayOpen-headingDegree" value="degree">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseDegree" aria-expanded="false"
                                                    aria-controls="panelsStayOpen-collapseDegree">
                                                Degree
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapseDegree" class="accordion-collapse collapse"
                                             aria-labelledby="panelsStayOpen-headingDegree"
                                             data-bs-parent="#accordionPanelsStayOpenExample" bis_skin_checked="1" style="">
                                            <div class="accordion-body" bis_skin_checked="1">
                                                <?php
                                                $selected_degrees = isset($_GET['level']) ? (is_array($_GET['level']) ? $_GET['level'] : [$_GET['level']]) : [];
                                                
                                                // Debug: Check if variables exist
                                                if (isset($all_degrees) && !empty($all_degrees)) {
                                                    foreach ($all_degrees as $degree_term) {
                                                        $is_checked = in_array($degree_term->term_id, $selected_degrees) ? 'checked' : '';
                                                        $active_class = in_array($degree_term->term_id, $selected_degrees) ? 'active' : '';
                                                        echo '<label class="filter-checkbox-label ' . $active_class . '">';
                                                        echo '<input type="checkbox" class="degree-checkbox" value="' . esc_attr($degree_term->term_id) . '" ' . $is_checked . '>';
                                                        echo '<span class="filter-checkbox-text">' . esc_html($degree_term->name) . '</span>';
                                                        echo '</label>';
                                                    }
                                                } else {
                                                    // Fallback: Get degrees directly if not passed from backend
                                                    $fallback_degrees = get_terms(['taxonomy' => 'sit-degree', 'hide_empty' => false]);
                                                    if (!empty($fallback_degrees)) {
                                                        foreach ($fallback_degrees as $degree_term) {
                                                            $is_checked = in_array($degree_term->term_id, $selected_degrees) ? 'checked' : '';
                                                            $active_class = in_array($degree_term->term_id, $selected_degrees) ? 'active' : '';
                                                            echo '<label class="filter-checkbox-label ' . $active_class . '">';
                                                            echo '<input type="checkbox" class="degree-checkbox" value="' . esc_attr($degree_term->term_id) . '" ' . $is_checked . '>';
                                                            echo '<span class="filter-checkbox-text">' . esc_html($degree_term->name) . '</span>';
                                                            echo '</label>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item" bis_skin_checked="1">
                                        <h2 class="accordion-header" id="panelsStayOpen-headingOne" value="duration">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="false"
                                                    aria-controls="panelsStayOpen-collapseOne">
                                                Duration
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse"
                                             aria-labelledby="panelsStayOpen-headingOne"
                                             data-bs-parent="#accordionPanelsStayOpenExample" bis_skin_checked="1" style="">
                                            <div class="accordion-body" bis_skin_checked="1">
                                                <?php
                                                // Get durations dynamically from current search results
                                                $current_durations = array();
                                                foreach ($programs as $program) {
                                                    if (!empty($program['duration']) && !in_array($program['duration'], $current_durations)) {
                                                        $current_durations[] = $program['duration'];
                                                    }
                                                }
                                                sort($current_durations);
                                                
                                                $selected_duration = isset($_GET['duration']) ? $_GET['duration'] : '';
                                                
                                                foreach ($current_durations as $duration) {
                                                    $duration_text = $duration . ' year' . ($duration > 1 ? 's' : '');
                                                    $active_class = ($selected_duration == $duration_text) ? 'active' : '';
                                                    echo '<a class="filter-btn ' . $active_class . '" href="javascript:void(0)" data-value="' . esc_attr($duration_text) . '">' . esc_html($duration_text);
                                                    echo '<div class="ui mini loader" bis_skin_checked="1"></div>';
                                                    echo '</a>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item" bis_skin_checked="1">
                                        <h2 class="accordion-header" id="panelsStayOpen-headingLanguage" value="language">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseLanguage" aria-expanded="false"
                                                    aria-controls="panelsStayOpen-collapseLanguage">
                                                Language
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapseLanguage" class="accordion-collapse collapse"
                                             aria-labelledby="panelsStayOpen-headingLanguage"
                                             data-bs-parent="#accordionPanelsStayOpenExample" bis_skin_checked="1" style="">
                                            <div class="accordion-body" bis_skin_checked="1">
                                                <?php
                                                $selected_languages = isset($_GET['language']) ? (is_array($_GET['language']) ? $_GET['language'] : [$_GET['language']]) : [];
                                                
                                                if (isset($available_languages) && !empty($available_languages)) {
                                                    foreach ($available_languages as $lang_term) {
                                                        $is_checked = in_array($lang_term->name, $selected_languages) ? 'checked' : '';
                                                        $active_class = in_array($lang_term->name, $selected_languages) ? 'active' : '';
                                                        echo '<label class="filter-checkbox-label ' . $active_class . '">';
                                                        echo '<input type="checkbox" class="language-checkbox" value="' . esc_attr($lang_term->name) . '" ' . $is_checked . '>';
                                                        echo '<span class="filter-checkbox-text">' . esc_html($lang_term->name) . '</span>';
                                                        echo '</label>';
                                                    }
                                                } else {
                                                    // Fallback: Get languages dynamically from current search results
                                                    $current_languages = array();
                                                    if (isset($programs) && !empty($programs)) {
                                                        foreach ($programs as $program) {
                                                            if (!empty($program['language']) && !in_array($program['language'], $current_languages)) {
                                                                $current_languages[] = $program['language'];
                                                            }
                                                        }
                                                        sort($current_languages);
                                                        
                                                        foreach ($current_languages as $lang_name) {
                                                            $is_checked = in_array($lang_name, $selected_languages) ? 'checked' : '';
                                                            $active_class = in_array($lang_name, $selected_languages) ? 'active' : '';
                                                            echo '<label class="filter-checkbox-label ' . $active_class . '">';
                                                            echo '<input type="checkbox" class="language-checkbox" value="' . esc_attr($lang_name) . '" ' . $is_checked . '>';
                                                            echo '<span class="filter-checkbox-text">' . esc_html($lang_name) . '</span>';
                                                            echo '</label>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item" bis_skin_checked="1">
                                        <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false"
                                                    aria-controls="panelsStayOpen-collapseThree">
                                                Annual course fee
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse"
                                             aria-labelledby="panelsStayOpen-headingThree"
                                             data-bs-parent="#accordionPanelsStayOpenExample" bis_skin_checked="1" style="">
                                            <div class="accordion-body" bis_skin_checked="1">
                                                <div class="range-input" bis_skin_checked="1">
                                                    <div class="range-input-flex" bis_skin_checked="1">
                                                        <div class="field" bis_skin_checked="1">
                                                            <label for="">Min. (USD)</label>
                                                            <input type="number" class="min-range mob-input"
                                                                   placeholder="Min. course fee"
                                                                   onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode >= 48 && event.charCode <= 57"
                                                                   value="">
                                                        </div>
                                                        <div class="field" bis_skin_checked="1">
                                                            <label for="">Max. (USD)</label>
                                                            <input type="number" class="max-range mob-input"
                                                                   placeholder="Max. course fee"
                                                                   onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode >= 48 && event.charCode <= 57"
                                                                   value="">
                                                        </div>
                                                        <div class="range-error" bis_skin_checked="1">
                                                            <svg style="margin:-4px 2px 0 0;display:inline-block;" width="16"
                                                                 height="16" viewBox="0 0 16 16" fill="none"
                                                                 xmlns="http://www.w3.org/2000/svg">
                                                                <g clip-path="url(#clip0_2581_14024)">
                                                                    <path d="M8.90063 2.51375L14.3669 12.0056C14.75 12.6744 14.255 13.5 13.4663 13.5H2.53375C1.745 13.5 1.25 12.6744 1.63313 12.0056L7.09938 2.51375C7.49313 1.82875 8.50688 1.82875 8.90063 2.51375Z"
                                                                          fill="#FF5DAD"></path>
                                                                    <path d="M8 8.5V6.5" stroke="white" stroke-linecap="round"
                                                                          stroke-linejoin="round"></path>
                                                                    <path d="M8 12C8.55228 12 9 11.5523 9 11C9 10.4477 8.55228 10 8 10C7.44772 10 7 10.4477 7 11C7 11.5523 7.44772 12 8 12Z"
                                                                          fill="white"></path>
                                                                </g>
                                                                <defs>
                                                                    <clipPath id="clip0_2581_14024">
                                                                        <rect width="16" height="16" fill="white"></rect>
                                                                    </clipPath>
                                                                </defs>
                                                            </svg>
                                                            <span>Maximum course fee should be greater than minimum course fee</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item" bis_skin_checked="1">
                                        <h2 class="accordion-header" id="panelsStayOpen-headingFour" value="university">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseFour" aria-expanded="false"
                                                    aria-controls="panelsStayOpen-collapseFour">
                                                University
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapseFour" class="accordion-collapse collapse"
                                             aria-labelledby="panelsStayOpen-headingFour"
                                             data-bs-parent="#accordionPanelsStayOpenExample" bis_skin_checked="1" style="">
                                            <div class="accordion-body" bis_skin_checked="1">
                                                <?php
                                                // Get all universities from current results
                                                $current_universities = array();
                                                foreach ($programs as $program) {
                                                    if (!in_array($program['uni_title'], $current_universities)) {
                                                        $current_universities[] = $program['uni_title'];
                                                    }
                                                }
                                                sort($current_universities);
                                                
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
                                    </div>
                                    <div class="accordion-item" bis_skin_checked="1">
                                        <h2 class="accordion-header" id="panelsStayOpen-headingSix" value="isScholarShip">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseSix" aria-expanded="false"
                                                    aria-controls="panelsStayOpen-collapseSix">
                                                Scholarships available
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapseSix" class="accordion-collapse collapse"
                                             aria-labelledby="panelsStayOpen-headingSix"
                                             data-bs-parent="#accordionPanelsStayOpenExample" bis_skin_checked="1" style="">
                                            <div class="accordion-body" bis_skin_checked="1">
                                                <a class="filter-btn " href="javascript:void(0)" data-value="Yes">Yes
                                                    <div class="ui mini loader" bis_skin_checked="1"></div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mobile-filter-btn" bis_skin_checked="1">
                                    <button class="btn btn-primary-ghoast mob-clear-filter" href="javascript:void(0)">Clear
                                        filters
                                    </button>
                                    <button class="btn btn-primary ms-2 mobileShowResult new-filter-apply"
                                            href="javascript:void(0)">Apply filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Export Button -->
                <button onclick="openExportPopup()" id="openExportPopup" class="ProgramArchivePage-export-button export-btn" type="button" aria-haspopup="dialog" aria-expanded="false" aria-controls="radix-:r1l:" data-state="closed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><line x1="10" y1="9" x2="8" y2="9"></line></svg>
                    Export PDF
                </button>
            </div>
        </div>
    </div>
</div>

<div class="single-campus-content">
    <!-- Loading indicator - moved here for better UX -->
    <div class="pagination-loading" id="paginationLoading" style="display: none;">
        <div class="loading-spinner"></div>
        <span>Loading programs...</span>
    </div>
    
    <!-- GRID VIEW: Your existing programs -->
    <!-- Programs now rendered in sidebar section above -->
    
    <!-- LIST VIEW: New container (hidden by default) -->
    <div class="all-faculties-program-list" id="programsListContainer" style="display: none;">
        <?php
        foreach ($programs as $university) {
            // Use the same program data structure as your grid view
            $program = $university;
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
                                    echo $matches[1]; // Shows "English", "Turkish", etc.
                                } elseif (!empty($program['language'])) {
                                    echo $program['language'];
                                } elseif (!empty($program['lang'])) {
                                    echo $program['lang'];
                                } elseif (!empty($program['program_language'])) {
                                    echo $program['program_language'];
                                } else {
                                    echo 'Not specified';
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
                            $uni_id = isset($program['uni_id']) ? $program['uni_id'] : '';
                            
                            // Construct apply URL
                            $apply_url = '#';
                            if (!empty($uni_id)) {
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

<div class="filter-pagination">
    <div class="pagination-container">
        <div class="pagination-info">
            <span>Showing <span id="currentRange">1-<?php echo min(count($programs), 20); ?></span> of <span id="totalPrograms"><?= $query->found_posts ?></span> programs</span>
        </div>
        
        <div class="pagination-controls">
            <button class="pagination-btn pagination-prev" id="prevPage" <?php echo (get_query_var('paged') <= 1) ? 'disabled' : ''; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                Previous
            </button>
            
            <div class="pagination-pages" id="paginationPages">
                <?php
                $current_page = max(1, get_query_var('paged'));
                $total_pages = $query->max_num_pages;
                $range = 2;
                
                // Show first page
                if ($current_page > $range + 1) {
                    echo '<button class="pagination-page" data-page="1">1</button>';
                    if ($current_page > $range + 2) {
                        echo '<span class="pagination-dots">...</span>';
                    }
                }
                
                // Show range around current page
                for ($i = max(1, $current_page - $range); $i <= min($total_pages, $current_page + $range); $i++) {
                    $active_class = ($i == $current_page) ? 'active' : '';
                    echo '<button class="pagination-page ' . $active_class . '" data-page="' . $i . '">' . $i . '</button>';
                }
                
                // Show last page
                if ($current_page < $total_pages - $range) {
                    if ($current_page < $total_pages - $range - 1) {
                        echo '<span class="pagination-dots">...</span>';
                    }
                    echo '<button class="pagination-page" data-page="' . $total_pages . '">' . $total_pages . '</button>';
                }
                ?>
            </div>
            
            <button class="pagination-btn pagination-next" id="nextPage" <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>>
                Next
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>
        </div>
        
        <div class="pagination-size">
            <label for="pageSize">Show:</label>
            <select id="pageSize" class="pagination-select">
                <option value="20" <?php echo (isset($_GET['per_page']) && $_GET['per_page'] == '20') ? 'selected' : ''; ?>>20</option>
                <option value="50" <?php echo (isset($_GET['per_page']) && $_GET['per_page'] == '50') ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo (isset($_GET['per_page']) && $_GET['per_page'] == '100') ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
    </div>
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
                    <td><?php 
                    // Extract language from title if it's in parentheses at the end
                    if (preg_match('/\(([^)]+)\)$/', $program['title'], $matches)) {
                        echo $matches[1]; // Shows "English", "Turkish", etc.
                    } elseif (!empty($program['language'])) {
                        echo $program['language'];
                    } elseif (!empty($program['lang'])) {
                        echo $program['lang'];
                    } elseif (!empty($program['program_language'])) {
                        echo $program['program_language'];
                    } else {
                        echo 'Not specified';
                    }
                    ?></td>
                    <td>May 15, 2024</td>
                    <td><?= $fee ?></td>
                </tr>
                <?php
            }
            ?>
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
                    <td><?php 
                    // Extract language from title if it's in parentheses at the end
                    if (preg_match('/\(([^)]+)\)$/', $program['title'], $matches)) {
                        echo $matches[1]; // Shows "English", "Turkish", etc.
                    } elseif (!empty($program['language'])) {
                        echo $program['language'];
                    } elseif (!empty($program['lang'])) {
                        echo $program['lang'];
                    } elseif (!empty($program['program_language'])) {
                        echo $program['program_language'];
                    } else {
                        echo 'Not specified';
                    }
                    ?></td>
                    <td>May 15, 2024</td>
                    <td><?= $fee ?></td>
                </tr>
                <?php
            }
            ?>
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

<!-- NEW: AJAX Pagination JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality (existing)
    const viewButtons = document.querySelectorAll('.view-btn');
    const gridContainer = document.getElementById('programsGridContainer');
    const listContainer = document.getElementById('programsListContainer');
    
    if (!gridContainer || !listContainer) {
        console.error('View containers not found');
        return;
    }
    
    // Current pagination state
    let currentPage = <?php echo max(1, get_query_var('paged')); ?>;
    let currentView = 'grid';
    let isLoading = false;
    
    // View toggle functionality
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (isLoading) return;
            
            // Update active button and aria-pressed attributes
            viewButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-pressed', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            
            // Get the selected view
            currentView = this.dataset.view;
            
            // Show/hide appropriate containers
            if (currentView === 'grid') {
                gridContainer.style.display = '';
                listContainer.style.display = 'none';
            } else if (currentView === 'list') {
                gridContainer.style.display = 'none';
                listContainer.style.display = '';
            }
            
            // Save preference
            localStorage.setItem('programView', currentView);
        });
    });
    
    // Restore saved view preference
    const savedView = localStorage.getItem('programView');
    if (savedView && savedView === 'list') {
        currentView = 'list';
        const listButton = document.querySelector('[data-view="list"]');
        if (listButton) {
            listButton.click();
        }
    }
    
    // AJAX Pagination functionality
    function loadPage(page, pageSize = null) {
        if (isLoading) return;
        
        isLoading = true;
        
        // Scroll to top immediately when page change starts
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Show loading indicator
        const loadingElement = document.getElementById('paginationLoading');
        const contentElement = document.querySelector('.single-campus-content');
        
        loadingElement.style.display = 'flex';
        contentElement.style.opacity = '0.6';
        
        // Get current filters and search parameters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('paged', page);
        urlParams.set('ajax', '1'); // Flag for AJAX request
        
        if (pageSize) {
            urlParams.set('per_page', pageSize);
        }
        
        // Make AJAX request
        fetch(window.location.pathname + '?' + urlParams.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse the response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update grid view content
            const newGridContent = doc.querySelector('#programsGridContainer');
            if (newGridContent && gridContainer) {
                gridContainer.innerHTML = newGridContent.innerHTML;
            }
            
            // Update list view content
            const newListContent = doc.querySelector('#programsListContainer');
            if (newListContent && listContainer) {
                listContainer.innerHTML = newListContent.innerHTML;
            }
            
            // Update pagination
            const newPagination = doc.querySelector('.pagination-container');
            const currentPagination = document.querySelector('.pagination-container');
            if (newPagination && currentPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
                rebindPaginationEvents();
            }
            
            // Update URL without page reload
            const newUrl = window.location.pathname + '?' + urlParams.toString().replace('&ajax=1', '').replace('ajax=1&', '').replace('ajax=1', '');
            history.pushState(null, '', newUrl);
            
            // Update current page
            currentPage = page;
            
            // Ensure we're at the top after content loads
            setTimeout(() => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);
        })
        .catch(error => {
            console.error('Error loading page:', error);
            // Fallback to regular page load
            window.location.href = window.location.pathname + '?' + urlParams.toString().replace('&ajax=1', '').replace('ajax=1&', '').replace('ajax=1', '');
        })
        .finally(() => {
            isLoading = false;
            loadingElement.style.display = 'none';
            contentElement.style.opacity = '1';
        });
    }
    
    function rebindPaginationEvents() {
        // Previous button
        const prevButton = document.getElementById('prevPage');
        if (prevButton) {
            prevButton.addEventListener('click', function() {
                if (currentPage > 1) {
                    loadPage(currentPage - 1);
                }
            });
        }
        
        // Next button
        const nextButton = document.getElementById('nextPage');
        if (nextButton) {
            nextButton.addEventListener('click', function() {
                loadPage(currentPage + 1);
            });
        }
        
        // Page number buttons
        const pageButtons = document.querySelectorAll('.pagination-page');
        pageButtons.forEach(button => {
            button.addEventListener('click', function() {
                const page = parseInt(this.dataset.page);
                loadPage(page);
            });
        });
        
        // Page size selector
        const pageSizeSelect = document.getElementById('pageSize');
        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function() {
                loadPage(1, this.value); // Reset to page 1 when changing page size
            });
        }
    }
    
    // Initial binding
    rebindPaginationEvents();
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        location.reload(); // Simple reload for back/forward navigation
    });
});
</script>


    <!-- Removed duplicate closing tags - moved up to line 1002-1003 -->

<script>
// Mobile filter toggle functionality
function toggleMobileFilters() {
    const sidebar = document.getElementById('filterSidebar');
    sidebar.classList.toggle('mobile-hidden');
}

// Update applied filters display in sidebar
function updateAppliedFiltersSidebar() {
    const appliedFiltersContainer = document.getElementById('appliedFiltersSidebar');
    const sidebarFiltersApplied = document.querySelector('.applied-filters-list.filtersApplied');
    const mainFiltersApplied = document.querySelector('.selected-filters-display .filtersApplied');
    
    // Copy filters from main container to sidebar, avoiding duplicates
    if (sidebarFiltersApplied && mainFiltersApplied) {
        sidebarFiltersApplied.innerHTML = mainFiltersApplied.innerHTML;
    }
    
    // Show/hide sidebar based on whether there are filters
    if (mainFiltersApplied && mainFiltersApplied.children.length > 0) {
        appliedFiltersContainer.style.display = 'block';
    } else {
        appliedFiltersContainer.style.display = 'none';
    }
}

// Initialize applied filters display
document.addEventListener('DOMContentLoaded', function() {
    updateAppliedFiltersSidebar();
    
    // Update when filters change - watch the main container
    const observer = new MutationObserver(updateAppliedFiltersSidebar);
    const mainFiltersApplied = document.querySelector('.selected-filters-display .filtersApplied');
    if (mainFiltersApplied) {
        observer.observe(mainFiltersApplied, { childList: true });
    }
    
    // Handle button filters (duration, scholarships)
    document.querySelectorAll('.filter-button').forEach(button => {
        button.addEventListener('click', function() {
            const filterType = this.dataset.filter;
            const filterValue = this.dataset.value;
            
            // Toggle active state
            this.classList.toggle('active');
            
            // Create URL with filter
            const url = new URL(window.location);
            if (this.classList.contains('active')) {
                url.searchParams.set(filterType, filterValue);
            } else {
                url.searchParams.delete(filterType);
            }
            
            // Navigate to new URL
            window.location.href = url.toString();
        });
    });
    
    // Disable old filter system JavaScript from main.js
    if (typeof jQuery !== 'undefined') {
        // Remove any old filter elements that might be generated by main.js
        jQuery('.selected-filters-display, .filter-head, .filter-body, .sr-filter, .new-sr-filter').remove();
        
        // Hide any dynamically created filter elements
        setTimeout(function() {
            jQuery('.selected-filters-display, .filter-head, .filter-body, .sr-filter, .new-sr-filter').hide();
        }, 100);
    }
    
    // Override the old updateAppliedFiltersDisplay function to prevent it from running
    if (typeof window.updateAppliedFiltersDisplay === 'function') {
        window.updateAppliedFiltersDisplay = function() {
            // Do nothing - disable the old filter system
            return;
        };
    }
});
</script>