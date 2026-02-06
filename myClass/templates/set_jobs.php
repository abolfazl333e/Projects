<?php
if (!defined('ABSPATH')) exit;

function myclass_render_students_job_page(): void {
    global $wpdb;

    // گرفتن کلاس فعال
    $active_class = get_option('myClass_active_class');
    if(!$active_class){
        echo '<div class="wrap"><p style="color:red;">کلاس فعال مشخص نشده است.</p></div>';
        return;
    }

    // فقط دانش‌آموزان کلاس فعال
    $students = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, name, job FROM {$wpdb->prefix}students WHERE TRIM(`class`) = %s ORDER BY id ASC",
            $active_class
        ),
        ARRAY_A
    );
    ?>
    <div class="wrap">
        <h1 style="text-align:center;margin-bottom:20px;color:#264653;">ثبت شغل دانش‌آموزان (<?= esc_html($active_class) ?>)</h1>

        <div id="studentsJobContainer">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#007bff;color:#fff;">
                    <th style="padding:10px;text-align:center;">نام دانش‌آموز</th>
                    <th style="padding:10px;text-align:center;">شغل</th>
                    <th style="padding:10px;text-align:center;">اقدامات</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($students as $s): ?>
                    <tr data-student-id="<?= $s['id'] ?>">
                        <td style="text-align:center;"><?= esc_html($s['name']) ?></td>
                        <td style="text-align:center;">
                            <input type="text" value="<?= esc_attr($s['job']) ?>" class="student-job-input" style="width:90%;padding:4px;">
                        </td>
                        <td style="text-align:center;">
                            <button class="button button-primary btn-save-job">ثبت</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal یکتا برای ثبت جمعی -->
        <div id="studentsJobModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:1000;">
            <div style="background:#fff;border-radius:12px;padding:20px;width:90%;max-width:500px;">
                <h3>ثبت شغل دانش‌آموزان</h3>
                <form id="studentsJobForm">
                    <div style="margin-bottom:10px;">
                        <label>شغل جدید برای همه:</label>
                        <input type="text" id="bulkJobInput" class="widefat" placeholder="مثلاً ورزشکار">
                    </div>
                    <div style="text-align:right;">
                        <button type="button" class="button" id="modalCancelBtn">انصراف</button>
                        <button type="button" class="button button-primary" id="modalSaveBtn">ثبت جمعی</button>
                    </div>
                </form>
            </div>
        </div>

        <div style="text-align:center;margin-top:20px;">
            <button class="button button-secondary" id="openModalBtn">➕ ثبت جمعی شغل‌ها</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){

            const restUrl = "<?php echo esc_url(rest_url('myClass/v1/save_jobs')); ?>";
            const nonce = "<?php echo wp_create_nonce('wp_rest'); ?>";

            // ثبت تک دانش‌آموز
            document.querySelectorAll('.btn-save-job').forEach(btn=>{
                btn.addEventListener('click', function(){
                    const tr = this.closest('tr');
                    const studentId = tr.getAttribute('data-student-id');
                    const job = tr.querySelector('.student-job-input').value;

                    const formData = new FormData();
                    formData.append('job['+studentId+']', job);

                    fetch(restUrl, {
                        method:'POST',
                        headers:{ 'X-WP-Nonce': nonce },
                        body: formData
                    }).then(res=>res.json())
                        .then(data=>{
                            alert(data.msg || 'ثبت شد');
                        }).catch(err=>{
                        alert('خطا در ثبت: '+err);
                    });
                });
            });

            // باز/بستن Modal
            const modal = document.getElementById('studentsJobModal');
            const openModalBtn = document.getElementById('openModalBtn');
            const modalCancelBtn = document.getElementById('modalCancelBtn');
            const modalSaveBtn = document.getElementById('modalSaveBtn');

            openModalBtn.addEventListener('click', ()=> modal.style.display='flex');
            modalCancelBtn.addEventListener('click', ()=> modal.style.display='none');

            // ثبت جمعی
            modalSaveBtn.addEventListener('click', ()=>{
                const job = document.getElementById('bulkJobInput').value;
                if(!job) return alert('لطفاً مقدار شغل را وارد کنید');

                const formData = new FormData();
                document.querySelectorAll('.student-job-input').forEach(input=>{
                    const tr = input.closest('tr');
                    const studentId = tr.getAttribute('data-student-id');
                    formData.append('job['+studentId+']', job);
                });

                fetch(restUrl, {
                    method:'POST',
                    headers:{ 'X-WP-Nonce': nonce },
                    body: formData
                }).then(res=>res.json())
                    .then(data=>{
                        alert(data.msg || 'ثبت شد');
                        modal.style.display='none';
                        // آپدیت جدول با مقدار جدید
                        document.querySelectorAll('.student-job-input').forEach(input=>input.value=job);
                    }).catch(err=>{
                    alert('خطا در ثبت جمعی: '+err);
                });
            });

        });
    </script>
    <?php
}
