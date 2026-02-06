<?php
if (!defined('ABSPATH')) exit;


    $upload_dir = myClass_DIR . 'uploads/paints/';
    $upload_url = myClass_UPLOADS . 'paints/';

    $images = glob($upload_dir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

if (!$images) return '<p>تصویری برای نمایش موجود نیست.</p>';

 ?>


<div class="swiper mySwiper">
    <div class="swiper-wrapper">
        <?php foreach ($images as $img): ?>
            <div class="swiper-slide">
                <img src="<?php echo esc_url($upload_url . '/' . basename($img)); ?>" alt="" style="width:100%; height:auto;">
            </div>
        <?php endforeach; ?>
    </div>
    <!-- دکمه‌های ناوبری -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <!-- Pagination -->
    <div class="swiper-pagination"></div>
</div>


