<?php
function myclass_students_page(){
    ?>
    <div class="wrap">
        <h1>مدیریت دانش‌آموزان</h1>
        <div id="myclass-students-root">
            <div class="students_card">
                <div class="upload-title">
                    <h2>وارد کردن دانش‌آموزان از CSV (جایگزین کامل)</h2>
                    <div class="info">فرمت CSV: <strong>name, tag, class, job, username, password, must_change_password</strong></div>
                </div>
                <div class="upload-area">
                    <label for="csvInput">انتخاب فایل CSV:</label>
                    <input id="csvInput" type="file" accept=".csv,text/csv">
                    <button id="uploadBtn" class="btn" disabled>ثبت و جایگزینی</button>
                    <div id="msgUpload" class="message"></div>
                </div>
            </div>

            <div class="students_card">
                <h2>لیست و ویرایش دانش‌آموزان</h2>
                <div id="msgEdit" class="message"></div>

                <div class="table-wrap">
                    <table id="studentsTable">
                        <thead>
                        <tr>
                            <th>ردیف</th>
                            <th>نام</th>
                            <th>کارت RFID</th>
                            <th>شغل</th>
                            <th>نام کاربری</th>
                            <th>رمز (خالی=بدون تغییر)</th>
                            <th>نیاز به تغییر رمز</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="export-buttons">
        <button id="exportCsvBtn" class="btn-export csv">📄 خروجی CSV</button>
        <button id="exportSqlBtn" class="btn-export sql">🗃️ خروجی SQL</button>
    </div>

    <?php
}