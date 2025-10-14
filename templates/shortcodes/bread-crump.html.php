<?php
$degree_term = get_term($degreeid, 'sit-degree');
$country_term = get_term($countryid, 'sit-country');
$speciality_term = get_term($specialityid, 'sit-speciality');

// Check if terms are valid (not WP_Error)
$degree_valid = $degree_term && !is_wp_error($degree_term);
$country_valid = $country_term && !is_wp_error($country_term);
$speciality_valid = $speciality_term && !is_wp_error($speciality_term);

if($speciality_valid && $degree_valid && $country_valid){
    $heading = $degree_term->name .' '.$speciality_term->name.' Courses In '.$country_term->name;
} elseif($speciality_valid && $country_valid) {
    $heading = $speciality_term->name.' Courses In '.$country_term->name;
} elseif($speciality_valid && $degree_valid) {
    $heading = $degree_term->name .' '.$speciality_term->name.' Courses';
} elseif($speciality_valid) {
    $heading = $speciality_term->name.' Courses';
} else {
    $heading = "Search For Course";
}
?>
<div class="bread-crump">
    <a href="<?php echo esc_url(\SIT\Search\Config::getHomeUrl()); ?>"><img class="b-home" src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/Vector-9.png" alt="alt"></a>
    <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/weui_arrow-outlined.png" alt="alt">
    <p><?= $heading ?></p>
</div>
