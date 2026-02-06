<?php
session_start();
require_once '../inc/db.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: teacher_login.php");
    exit;
}

// گرفتن لیست دانش‌آموزان بر اساس id
$students = [];
$res = $conn->query("SELECT id, name, rfid_tag, class, job, username, (password_hash IS NOT NULL AND password_hash <> '') AS has_password, must_change_password FROM students ORDER BY id ASC");
while($row = $res->fetch_assoc()) $students[] = $row;
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>مدیریت دانش‌آموزان</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
<style>
:root{
  --card-bg:#fff;
  --accent:#28a745;
  --muted:#6c757d;
  --primary:#007bff;
}
body{
  font-family:"B Nazanin",sans-serif;
  background: linear-gradient(180deg,#eef7f2 0%, #f0f2f5 100%);
  margin:0; padding:18px;
  -webkit-font-smoothing:antialiased;
}
.container{ max-width:1200px; margin:0 auto; }

/* cards */
.card{
  background:var(--card-bg);
  border-radius:12px;
  padding:18px;
  margin-bottom:18px;
  box-shadow:0 8px 30px rgba(0,0,0,0.06);
}

/* upload section */
.upload-title{ display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.upload-title h2{ margin:0; font-size:1.05rem; color:#222; }
.upload-area{
  border:2px dashed rgba(0,0,0,0.06);
  padding:14px;
  border-radius:10px;
  background:#f8fffa;
  display:flex; gap:12px; align-items:center; flex-wrap:wrap;
}
.upload-area label{ font-size:0.95rem; color:var(--muted); min-width:120px; }
.upload-area input[type="file"]{ padding:6px; }
.btn{
  display:inline-block;
  background:var(--accent);
  color:#fff;
  border:none;
  padding:9px 14px;
  border-radius:8px;
  cursor:pointer;
  font-weight:600;
}
.btn:disabled{ opacity:.6; cursor:not-allowed; }
.message{ margin-top:8px; color:var(--muted); font-size:0.95rem; }

/* table responsive */
.table-wrap{ overflow:auto; }
table{ width:100%; border-collapse:collapse; min-width:900px; }
th,td{ padding:10px 8px; border-bottom:1px solid #eee; text-align:center; vertical-align:middle; }
th{ background:var(--primary); color:#fff; position:sticky; top:0; z-index:2; font-weight:600; }
td input[type="text"], td input[type="number"], td input[type="password"]{
  width:100%; padding:6px 8px; box-sizing:border-box; border:1px solid #e2e6ea; border-radius:6px; font-size:0.95rem;
}
.small{ width:70px; }

/* action buttons */
.updateBtn{ background:#17a2b8; color:#fff; border:none; padding:7px 12px; border-radius:6px; cursor:pointer; }
.showPwd { color:var(--primary); cursor:pointer; display:inline-block; margin-right:6px; font-size:0.85rem; }

/* info row */
.info { font-size:0.95rem; color:var(--muted); margin-bottom:8px; }

/* responsive adjustments */
@media (max-width:900px){
  table { min-width:700px; }
  th,td{ padding:8px; font-size:0.92rem; }
}
@media (max-width:600px){
  .upload-area{ flex-direction:column; align-items:stretch; }
  table{ min-width:600px; }
  .small{ width:56px; }
}
</style>
</head>
<body>
<div class="container">

  <div class="card">
    <div class="upload-title">
      <h2>وارد کردن دانش‌آموزان از CSV (جایگزین کامل)</h2>
      <div class="info">فرمت CSV: <strong>name, tag, class, job, username, password, must_change_password</strong> — ستون رمز اگر خالی باشد، بعداً می‌توانید دستی ست کنید.</div>
    </div>

    <div class="upload-area" role="region" aria-label="Upload CSV">
      <label for="csvInput">انتخاب فایل CSV:</label>
      <input id="csvInput" type="file" accept=".csv,text/csv">
      <button id="uploadBtn" class="btn" disabled>ثبت و جایگزینی</button>
      <div id="msgUpload" class="message"></div>
    </div>
  </div>

  <div class="card">
    <h2>لیست و ویرایش دانش‌آموزان</h2>
    <div id="msgEdit" class="message"></div>

    <div class="table-wrap">
      <table id="studentsTable" role="table" aria-label="Students table">
        <thead>
          <tr>
            <th>ردیف</th>
            <th>نام</th>
            <th>کلاس</th>
            <th>کارت RFID</th>
            <th>شغل</th>
            <th>نام کاربری</th>
            <th>رمز (خالی=بدون تغییر)</th>
            <th>نیاز به تغییر رمز</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($students as $s): ?>
          <tr data-id="<?= $s['id'] ?>">
            <td><?= htmlspecialchars($s['id']) ?></td>
            <td><input type="text" class="fld-name" value="<?= htmlspecialchars($s['name']) ?>"></td>
            <td><input type="text" class="fld-class" value="<?= htmlspecialchars($s['class']) ?>"></td>
            <td><input type="text" class="fld-rfid" dir="ltr" style="font-family:monospace" value="<?= htmlspecialchars($s['rfid_tag']) ?>"></td>
            <td><input type="text" class="fld-job" value="<?= htmlspecialchars($s['job']) ?>"></td>
            <td><input type="text" class="fld-username" dir="ltr" value="<?= htmlspecialchars($s['username']) ?>"></td>
            <td>
              <!-- نمایش رمز: برای امنیت، فیلد خالی گذاشته می‌شود. اگر مدیر رمز جدید وارد کند، ذخیره و هش خواهد شد. -->
              <input type="text" class="fld-password" placeholder="<?= $s['has_password'] ? 'رمز تنظیم شده — برای نمایش وارد کنید' : 'رمز ندارد — اگر می‌خواهید ست کنید اینجا وارد کنید' ?>">
              <span class="showPwd" title="نمایش/مخفی">نمایش</span>
            </td>
            <td><input type="number" class="fld-must" min="0" max="1" value="<?= (int)$s['must_change_password'] ?>" class="small"></td>
            <td><button class="updateBtn">ثبت تغییرات</button></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
// ---------- helpers ----------
const csvInput = document.getElementById('csvInput');
const uploadBtn = document.getElementById('uploadBtn');
const msgUpload = document.getElementById('msgUpload');
const msgEdit = document.getElementById('msgEdit');

let csvFile = null;
csvInput.addEventListener('change', e=>{
  csvFile = e.target.files[0];
  uploadBtn.disabled = !csvFile;
});

uploadBtn.addEventListener('click', ()=>{
  if(!csvFile) return;
  const form = new FormData();
  form.append('csv_file', csvFile);

  msgUpload.textContent = 'در حال آپلود و پردازش...';
  uploadBtn.disabled = true;

  fetch('api/students_import_ajax.php', {
    method: 'POST',
    body: form
  }).then(r=>r.json())
    .then(data=>{
      if(data.ok){
        msgUpload.textContent = `افزوده شد: ${data.added} — رد شده: ${data.skipped}`;
        buildTable(data.students);
        csvInput.value = '';
        csvFile = null;
      } else {
        msgUpload.textContent = data.msg || 'خطا در پردازش';
      }
      uploadBtn.disabled = false;
    }).catch(err=>{
      console.error(err);
      msgUpload.textContent = 'خطا در ارتباط با سرور';
      uploadBtn.disabled = false;
    });
});

// بازسازی جدول بر اساس آرایه students (که از سرور دریافت شده)
function buildTable(students){
  const tbody = document.querySelector('#studentsTable tbody');
  tbody.innerHTML = '';
  students.forEach(s=>{
    const tr = document.createElement('tr');
    tr.dataset.id = s.id;
    tr.innerHTML = `
      <td>${escapeHtml(s.id)}</td>
      <td><input type="text" class="fld-name" value="${escapeHtml(s.name)}"></td>
      <td><input type="text" class="fld-class" value="${escapeHtml(s.class)}"></td>
      <td><input type="text" class="fld-rfid" dir="ltr" style="font-family:monospace" value="${escapeHtml(s.rfid_tag)}"></td>
      <td><input type="text" class="fld-job" value="${escapeHtml(s.job||'')}"></td>
      <td><input type="text" class="fld-username" dir="ltr" value="${escapeHtml(s.username||'')}"></td>
      <td><input type="text" class="fld-password" placeholder="${s.has_password ? 'رمز تنظیم شده — برای نمایش وارد کنید' : 'رمز ندارد — اگر می‌خواهید ست کنید اینجا وارد کنید'}"><span class="showPwd">نمایش</span></td>
      <td><input type="number" class="fld-must small" min="0" max="1" value="${escapeHtml(s.must_change_password||1)}"></td>
      <td><button class="updateBtn">ثبت تغییرات</button></td>
    `;
    tbody.appendChild(tr);
  });
  attachEvents();
}

// حفره سازی برای کاراکترهای خاص
function escapeHtml(str){
  if(str === null || typeof str === 'undefined') return '';
  return String(str).replace(/[&<>"'`=\/]/g, function(s){ return ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'
  })[s]; });
}

// اضافه کردن event ها به دکمه‌های update و show password
function attachEvents(){
  document.querySelectorAll('.updateBtn').forEach(btn=>{
    btn.onclick = async ()=>{
      if(!confirm('آیا مطمئن هستید می‌خواهید تغییرات این دانش‌آموز ثبت شود؟')) return;
      const tr = btn.closest('tr');
      const id = parseInt(tr.dataset.id || 0, 10);
      const name = tr.querySelector('.fld-name').value.trim();
      const cls  = tr.querySelector('.fld-class').value.trim();
      const rfid = tr.querySelector('.fld-rfid').value.trim();
      const job  = tr.querySelector('.fld-job').value.trim();
      const username = tr.querySelector('.fld-username').value.trim();
      const password = tr.querySelector('.fld-password').value; // اگر خالی بود تغییری نده
      const must = parseInt(tr.querySelector('.fld-must').value||1,10);

      if(!name || !rfid || !username){
        msgEdit.textContent = 'نام، کارت RFID و نام کاربری نمیتوانند خالی باشند.';
        return;
      }

      msgEdit.textContent = 'در حال ثبت تغییرات...';

      try{
        const res = await fetch('students_update_ajax.php', {
          method:'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({
            id, name, class:cls, rfid_tag:rfid, job, username, password, must_change_password: must
          })
        });
        const j = await res.json();
        if(j.ok){
          msgEdit.textContent = 'تغییرات با موفقیت ثبت شد.';
          // اگر password ست شده بود، پاکش کن (برای امنیت) و نشان بده که اکنون رمز وجود دارد
          if(password) {
            tr.querySelector('.fld-password').value = '';
            tr.querySelector('.fld-password').placeholder = 'رمز تنظیم شده';
          }
        } else {
          msgEdit.textContent = 'خطا: ' + (j.msg||'خطا در ذخیره');
        }
      }catch(e){
        console.error(e);
        msgEdit.textContent = 'خطا در ارتباط با سرور';
      }
    };
  });

  document.querySelectorAll('.showPwd').forEach(span=>{
    span.onclick = ()=>{
      const input = span.previousElementSibling;
      if(!input) return;
      if(input.type === 'password'){ input.type='text'; span.textContent='مخفی'; }
      else { input.type='password'; span.textContent='نمایش'; }
    };
  });
}

// attach for initial table
attachEvents();
</script>
</body>
</html>
