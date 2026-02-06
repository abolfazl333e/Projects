
<div class="myClass">
    <?php
    $buttonsMap   = myClass_header_buttons_map();
    $activeButtons = get_option(
        'myClass_header_buttons',
        myClass_default_header_buttons()
    );
    ?>

    <div class="myClass_header">
        <?php foreach ($buttonsMap as $key => $btn): ?>
            <?php if (in_array($key, $activeButtons)): ?>
                <button class="myClass-header-btn"
                        onclick="location.href='<?php echo esc_url(site_url($btn['url'])); ?>'">
                    <?php echo esc_html($btn['label']); ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

<!--    <h1>لطفاً نقش خود را انتخاب کنید</h1>-->

    <!-- دکمه‌ها -->
    <div class="myClass-role-container">
        <div class="role-card" id="studentLoginBtn">
            <i class="fas fa-user-graduate"></i>
            <span>دانش‌آموز</span>
        </div>
<!--        <div class="role-card" onclick="goToLogin('teacher')">-->
<!--            <i class="fas fa-chalkboard-teacher"></i>-->
<!--            <span>معلم</span>-->
<!--        </div>-->
    </div>

    <!-- مدال ورود دانش‌آموز -->
    <div class="students_login_modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div class="login-box" style="position:relative;background: wheat;padding: 30px 40px;border-radius: 15px;">
            <span id="closeModal" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px;">×</span>
            <h2>ورود دانش‌آموز 👦</h2>
            <div class="error" id="loginError" style="display:none;"></div>
            <form id="studentLoginForm" style="display:flex; flex-direction:column; align-items:center; width:100%;">
                <input type="text" name="username" placeholder="نام کاربری شما" required>
                <input type="password" name="password" placeholder="رمز عبور" required>
                <button type="submit" class="btn " id="students_login_modal_btn">ورود 🚀</button>
            </form>
        </div>
    </div>
</div>





