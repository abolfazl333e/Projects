<?php
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

// گرفتن درس‌ها
$lessons = [];
$res = $conn->query("SELECT id, name FROM lessons ORDER BY name ASC");
while($row = $res->fetch_assoc()){
    $lessons[] = $row;
}

// گرفتن همه آزمایش‌ها
$experiments = [];
$expRes = $conn->query("SELECT * FROM experiments ORDER BY created_at DESC");
while($exp = $expRes->fetch_assoc()){
    $exp['images'] = [];
    $imgRes = $conn->query("SELECT filename FROM experiment_images WHERE experiment_id=".$exp['id']);
    while($img = $imgRes->fetch_assoc()){
        $exp['images'][] = $img['filename'];
    }
    $experiments[] = $exp;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مدیریت آزمایش‌ها</title>
<style>
body { font-family: 'B Nazanin', sans-serif !important; margin:0; padding:20px; background: linear-gradient(120deg,#f6d365,#fda085);}
h2 { text-align:center; margin-bottom:20px; color:#264653;}
.top-controls { font-family: 'B Nazanin', sans-serif !important; text-align:center; margin-bottom:20px; }
.btn-create { font-family: 'B Nazanin', sans-serif !important; padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:8px; cursor:pointer; }
.btn-create:hover { background:#0056b3; }
.experiment-cards { display:grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap:20px; }
.card { background:#fff; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); padding:15px; display:flex; flex-direction:column; }
.card h3 { margin:0 0 10px 0; }
.images { display:flex; gap:5px; overflow-x:auto; margin-bottom:10px; }
.images img { height:80px; border-radius:6px; }
.actions { margin-top:auto; display:flex; justify-content:space-between; }
.actions button { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; }
.btn-edit { font-family: 'B Nazanin', sans-serif !important; background:#ffc107; color:#000; }
.btn-edit:hover { background:#e0a800; }
.btn-delete { font-family: 'B Nazanin', sans-serif !important; background:#dc3545; color:#fff; }
.btn-delete:hover { background:#bd2130; }

/* Modal */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:#fff; border-radius:12px; padding:20px; width:90%; max-width:500px; }
.modal-content h3 { margin-top:0; }
.modal-content input, .modal-content select, .modal-content textarea { width:100%; padding:8px; margin-bottom:10px; border-radius:6px; border:1px solid #ccc; }
.modal-content input[type=file] { padding:3px; }
.modal-actions { text-align:right; }
.modal-actions button { margin-left:10px; }
</style>
</head>
<body>

<h2>مدیریت آزمایش‌ها</h2>

<div class="top-controls">
    <button class="btn-create" onclick="openModal()">➕ ایجاد آزمایش جدید</button>
</div>

<div class="experiment-cards" id="experimentCards">
    <?php foreach($experiments as $exp): ?>
    <div class="card" data-id="<?= $exp['id'] ?>">
        <h3><?= htmlspecialchars($exp['title']) ?></h3>
        <p>درس: <?= htmlspecialchars($lessons[array_search($exp['lesson_id'], array_column($lessons,'id'))]['name'] ?? '-') ?></p>
        <div class="images">
            <?php foreach($exp['images'] as $img): ?>
                <img src="experiments_images/<?= htmlspecialchars($img) ?>" alt="">
            <?php endforeach; ?>
        </div>
        <p>توضیح : <?= htmlspecialchars($exp['results']) ?></p>
        <div class="actions">
            <button class="btn-edit" onclick="editExperiment(<?= $exp['id'] ?>)">ویرایش</button>
            <button class="btn-delete" onclick="deleteExperiment(<?= $exp['id'] ?>)">حذف</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div class="modal" id="experimentModal">
    <div class="modal-content">
        <h3 id="modalTitle">ایجاد آزمایش جدید</h3>
        <form id="experimentForm">
            <input type="hidden" name="id" id="expId">
            <label>عنوان آزمایش</label>
            <input type="text" name="title" id="expTitle" required>
            <label>درس</label>
            <select name="lesson_id" id="expLesson" required>
                <option value="">انتخاب درس</option>
                <?php foreach($lessons as $lesson): ?>
                    <option value="<?= $lesson['id'] ?>"><?= htmlspecialchars($lesson['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>توضیح </label>
            <textarea name="results" id="expResults"></textarea>
            <label>تصاویر (چندگانه)</label>
            <input type="file" name="images[]" id="expImages" multiple>
            <div class="modal-actions">
                <button type="button" onclick="closeModal()">انصراف</button>
                <button type="button" onclick="saveExperiment()">ثبت</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(){
    document.getElementById('modalTitle').innerText = 'ایجاد آزمایش جدید';
    document.getElementById('experimentForm').reset();
    document.getElementById('expId').value='';
    document.getElementById('experimentModal').style.display='flex';
}

function closeModal(){
    document.getElementById('experimentModal').style.display='none';
}

function editExperiment(id){
    fetch('api/get_experiment.php?id='+id)
    .then(res=>res.json())
    .then(data=>{
        if(data.ok){
            document.getElementById('modalTitle').innerText='ویرایش آزمایش';
            document.getElementById('expId').value=data.exp.id;
            document.getElementById('expTitle').value=data.exp.title;
            document.getElementById('expLesson').value=data.exp.lesson_id;
            document.getElementById('expResults').value=data.exp.results;
            document.getElementById('experimentModal').style.display='flex';
        }
    });
}

function saveExperiment(){
    const form = document.getElementById('experimentForm');
    const formData = new FormData(form);
    fetch('api/save_experiment.php',{
        method:'POST',
        body: formData
    }).then(res=>res.json())
      .then(data=>{
          if(data.ok){
              alert('عملیات موفقیت آمیز بود!');
              location.reload();
          }else{
              alert('خطا: '+data.msg);
          }
      });
}

function deleteExperiment(id){
    if(confirm('آیا مطمئن هستید می‌خواهید این آزمایش حذف شود؟')){
        fetch('api/delete_experiment.php?id='+id)
        .then(res=>res.json())
        .then(data=>{
            if(data.ok){
                alert('حذف شد');
                location.reload();
            } else{
                alert('خطا: '+data.msg);
            }
        });
    }
}
</script>

</body>
</html>
