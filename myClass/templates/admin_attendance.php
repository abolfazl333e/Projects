<?php
require_once myClass_DIR.'inc/jdf.php';


?>

<div class="wrap">
    <h1>حضور و غیاب امروز (<?= jdate('Y/m/d') ?>)</h1>

    <button class="button button-primary" id="refreshAttendance">بروزرسانی</button>

    <div id="attendanceList" style="margin-top:20px;">
        در حال بارگذاری...
    </div>
</div>

<style>
#attendanceList {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.student-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-radius: 8px;
    color: #000;
    transition: background 0.3s, color 0.3s;
}

/* رنگ‌بندی ردیف‌ها بر اساس وضعیت */
.student-row.present {
    background-color: #d4edda; /* سبز روشن */
    border: 1px solid #28a745;
}

.student-row.leave {
    background-color: #ffffff; /* سفید */
    border: 1px solid #ccc;
}

.student-row.absent {
    background-color: #f8d7da; /* قرمز روشن */
    border: 1px solid #dc3545;
}

.student-name {
    font-weight: bold;
    font-size: 16px;
    width: 200px;
}

.student-status {
    font-size: 14px;
    margin-left: 20px;
}

.manual-controls {
    display: flex;
    gap: 6px;
}

.manual-controls .button {
    font-size: 13px;
    padding: 4px 8px;
}
</style>

<script>
jQuery(document).ready(function($){

    const restBase = typeof myClass_object !== 'undefined' ? myClass_object.rest_url : '';

    function loadAttendance(){
        $.ajax({
            url: restBase + 'get_today_status',
            method: 'GET',
            success: function(data){
                console.log(data);

                let html = '';

                data.forEach(function(s){
                    const statusClass = s.status === 'حاضر' ? 'present' : (s.status === 'مرخصی' ? 'leave' : 'absent');
                
                    html += `
                        <div class="student-row ${statusClass}">
                            <div class="student-name">${s.name}</div>
                            <div class="student-status">${s.status} ${s.time ? '(' + s.time + ')' : ''}</div>
                
                            <div class="manual-controls">
                                <button class="button setStatus" data-id="${s.id}" data-status="حاضر">حاضر</button>
                                <button class="button setStatus" data-id="${s.id}" data-status="مرخصی">مرخصی</button>
                                <button class="button setStatus" data-id="${s.id}" data-status="غایب">غایب</button>
                            </div>
                        </div>
                    `;
                });

                $('#attendanceList').html(html);
            }
        });
    }

    // ثبت وضعیت دستی
    $('#attendanceList').on('click', '.setStatus', function(){
        const id = $(this).data('id');
        const status = $(this).data('status');

        $.ajax({
            url: restBase + 'set_status',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id, status }),
            success: function(){
                loadAttendance();
            }
        });
    });

    $('#refreshAttendance').click(loadAttendance);

    loadAttendance();
});
</script>
