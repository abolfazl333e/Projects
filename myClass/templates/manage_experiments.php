<?php
if (!defined('ABSPATH')) exit;

function myclass_render_experiments_page():void {
    $active_class = get_option('myClass_active_class');
    ?>
    <div class="wrap">
        <h1 style="text-align:center;margin-bottom:20px;color:#264653;">مدیریت آزمایش‌ها</h1>

        <div style="text-align:center;margin-bottom:20px;">
            <button id="addExperimentBtn" class="button button-primary">➕ ایجاد آزمایش جدید</button>
        </div>

        <div id="experimentsContainer" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
            <!-- کارت‌ها با JS پر می‌شوند -->
        </div>

        <!-- Modal یکتا -->
        <div id="experimentModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:1000;">
            <div style="background:#fff;border-radius:12px;padding:20px;width:90%;max-width:500px;">
                <h3 id="modalTitle">ایجاد آزمایش جدید</h3>
                <form id="experimentForm">

                    <div class="form_row">
                        <label for="modalExpTitle">عنوان آزمایش</label>
                        <input type="text" name="title" id="modalExpTitle" class="widefat marg" required>
                    </div>

                    <div class="form_row">
                        <label for="modalExpLesson">درس</label>
                        <select name="lesson_id" id="modalExpLesson" class="widefat marg" required></select>
                    </div>

                    <div class="form_row">
                        <label for="modalExpResults">توضیح</label>
                        <textarea name="results" id="modalExpResults" class="widefat marg"></textarea>
                    </div>

                    <div class="form_row">
                        <label>تصاویر</label>
                        <div id="modalCurrentImages"></div>
                    </div>

                    <input type="hidden" name="id" id="modalExpId" class="marg">
                    <input type="hidden" name="class" id="class" value="<?= esc_attr($active_class) ?>" class="marg">

                    <div class="form_row">
                        <input type="file" name="images[]" id="modalExpImages" multiple class="widefat">
                    </div>

                    <div style="text-align:right;margin-top:10px;">
                        <button type="button" class="button" id="modalCancelBtn">انصراف</button>
                        <button type="button" class="button button-primary" id="modalSaveBtn">ثبت</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
}
