<?php
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

// گرفتن لیست دانش‌آموزان بر اساس حروف الفبا
$students = [];
$res = $conn->query("SELECT id, name, job FROM students ORDER BY name ASC");
while($row = $res->fetch_assoc()){
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ثبت شغل دانش‌آموزان</title>
<style>
@font-face {
    font-family: 'B Nazanin';
    src: url('fonts/B_Nazanin.woff2') format('woff2'),
         url('fonts/B_Nazanin.woff') format('woff');
}

body {
    font-family: 'B Nazanin', sans-serif;
    font-weight: bold;
    background: #f0f2f5;
    margin: 0;
    padding: 20px;
}

h2 {
    text-align: center;
    color: #264653;
    margin-bottom: 20px;
}

.table-container {
    max-width: 900px;
    margin: auto;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

th, td {
    padding: 12px 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

th {
    background: #007bff;
    color: #fff;
    font-size: 16px;
}

tr:nth-child(even) {
    background: #f9f9f9;
}

input[type=text] {
    width: 90%;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-family: 'B Nazanin', sans-serif;
}

.controls {
    text-align: center;
    margin-top: 20px;
}

.btn-save {
    padding: 10px 20px;
    background: #28a745;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
    font-family: 'B Nazanin', sans-serif;
}
.btn-save:hover {
    background: #1e7e34;
}

@media (max-width: 600px) {
    th, td {
        padding: 10px 6px;
        font-size: 14px;
    }
    .btn-save {
        width: 100%;
    }
}
</style>
</head>
<body>

<h2>ثبت شغل دانش‌آموزان</h2>

<div class="table-container">
<form id="jobForm">
    <table>
        <thead>
            <tr>
                <th>نام دانش‌آموز</th>
                <th>شغل</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><input type="text" name="job[<?= $s['id'] ?>]" value="<?= htmlspecialchars($s['job']) ?>"></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="controls">
        <button type="button" class="btn-save" onclick="saveJobs()">ثبت شغل‌ها</button>
    </div>
</form>
</div>

<script>
function saveJobs(){
    const form = document.getElementById('jobForm');
    const formData = new FormData(form);

    fetch('api/save_jobs.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
      .then(data => {
        alert(data.msg || 'ثبت شد');
      }).catch(err => {
        alert('خطا در ثبت شغل‌ها: ' + err);
      });
}
</script>

</body>
</html>
