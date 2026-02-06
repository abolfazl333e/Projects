<?php
if (!defined('ABSPATH')) exit;

function myclass_render_behavior_page(): void {
    global $wpdb;

    // گرفتن درس‌ها
    $lessons = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}lessons ORDER BY name ASC", ARRAY_A);

    // کلاس فعال
    $active_class = get_option('myClass_active_class');

    ?>
    <div class="wrap">
        <h1 style="text-align:center;margin-bottom:20px;color:#264653;">ثبت رفتار دانش‌آموزان</h1>

        <div style="text-align:center;margin-bottom:20px;">
            <select id="behaviorLessonSelect" style="padding:8px 12px;border-radius:6px;font-size:16px;">
                <option value="">-- انتخاب درس --</option>
                <?php foreach($lessons as $l): ?>
                    <option value="<?= $l['id'] ?>"><?= esc_html($l['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="msgBehavior" class="message" style="text-align:center;color:#d9534f;margin-bottom:10px;"></div>

        <div class="table-wrap" style="display:none;overflow-x:auto;">
            <table id="behaviorTable" style="width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 5px 15px rgba(0,0,0,0.1);">
                <thead>
                <tr style="background:#007bff;color:#fff;">
                    <th style="padding:12px 10px;">نام دانش‌آموز</th>
                    <th style="padding:12px 10px;">مثبت‌ها</th>
                    <th style="padding:12px 10px;">منفی‌ها</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="controls" style="text-align:center;margin-top:15px;">
                <button type="button" class="button button-primary" id="btnSaveBehavior">ثبت تغییرات</button>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const lessonSelect = document.getElementById('behaviorLessonSelect');
            const tbody = document.querySelector('#behaviorTable tbody');
            const tableWrap = document.querySelector('.table-wrap');
            const msgBehavior = document.getElementById('msgBehavior');
            const btnSave = document.getElementById('btnSaveBehavior');

            // کلاس فعال از PHP
            const activeClass = "<?= esc_js($active_class) ?>";

            lessonSelect.addEventListener('change', loadStudents);

            async function loadStudents(){
                const lesson_id = lessonSelect.value;
                if(!lesson_id) return tableWrap.style.display='none';

                msgBehavior.textContent = 'در حال بارگذاری...';
                tableWrap.style.display='block';

                try {
                    // اضافه کردن کلاس فعال به کوئری REST
                    const res = await fetch('<?= esc_url(get_rest_url(null,"myClass/v1/behaviors")); ?>?lesson_id='+lesson_id+'&class='+encodeURIComponent(activeClass));
                    const data = await res.json();
                    tbody.innerHTML = '';

                    if(data.ok){
                        data.students.forEach(s=>{
                            const tr = document.createElement('tr');
                            tr.dataset.id = s.id;
                            tr.innerHTML = `
                                <td>${s.name}</td>
                                <td><input type="number" class="fld-positive" value="${s.positive_count}" style="width:60px;padding:6px;border-radius:6px;border:1px solid #ccc;text-align:center;"></td>
                                <td><input type="number" class="fld-negative" value="${s.negative_count}" style="width:60px;padding:6px;border-radius:6px;border:1px solid #ccc;text-align:center;"></td>
                            `;
                            tbody.appendChild(tr);
                        });
                        msgBehavior.textContent = '';
                    } else {
                        msgBehavior.textContent = data.msg || 'خطا در بارگذاری دانش‌آموزان';
                    }
                } catch(e){
                    msgBehavior.textContent = 'خطا در ارتباط با سرور';
                    console.error(e);
                }
            }

            btnSave.addEventListener('click', async ()=>{
                const lesson_id = lessonSelect.value;
                if(!lesson_id) return alert('ابتدا درس را انتخاب کنید');

                let positive={}, negative={};
                tbody.querySelectorAll('tr').forEach(tr=>{
                    const id = tr.dataset.id;
                    positive[id] = parseInt(tr.querySelector('.fld-positive').value || 0);
                    negative[id] = parseInt(tr.querySelector('.fld-negative').value || 0);
                });

                try{
                    const res = await fetch('<?= esc_url(get_rest_url(null,"myClass/v1/behaviors/update")); ?>',{
                        method:'POST',
                        headers:{'Content-Type':'application/json'},
                        body: JSON.stringify({lesson_id, class: activeClass, positive, negative})
                    });
                    const data = await res.json();
                    alert(data.msg || (data.ok?'ثبت شد':'خطا در ثبت'));
                }catch(e){
                    alert('خطا در ارتباط با سرور');
                    console.error(e);
                }
            });
        })();
    </script>
    <?php
}

