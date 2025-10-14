<div class="university-box-wrapper university-box-wrapper-du uni-box-hei">
    <div class="university-image">
        <img src="<?= $university['image_url'] ?>" alt="University Image">
    </div>
    <div class="university-content">
        <h3><?= $university['title'] ?></h3>
        <p class="country"><img src="<?= get_site_url() ?>/wp-content/uploads/2025/02/gps-2.png" alt=""><?= $university['country'] ?></p>
        <a class="unilink" href="<?= get_permalink($university['uni_id']) ?>">See course</a>
    </div>
</div>

