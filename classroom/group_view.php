<?php
require_once 'inc/db.php';

$groups = $conn->query("SELECT * FROM groups ORDER BY group_number ASC");
?>

<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>نمایش گروه‌ها</title>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'B Nazanin', sans-serif;
    font-weight:bold;
    background: linear-gradient(120deg,#ffecd2,#fcb69f);
    margin:0; padding:0;
    user-select:none;
}
.container {
    width:95%; max-width:1200px; margin:30px auto; padding:20px;
}
h2 { text-align:center; color:#333; margin-bottom:30px; font-size:32px; }
.group-grid {
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}
.card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    padding: 20px;
    transition: 0.3s;
    text-align:center;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 25px rgba(0,0,0,0.25);
}
.group-img {
    width:100%;
    height:220px;
    object-fit:cover;
    border-radius:15px;
    margin-bottom:15px;
}
.card h3 {
    margin:0 0 10px 0;
    font-size:22px;
    color:#ff6f61;
    text-align:center;
}
.card p {
    margin:5px 0;
    font-size:16px;
    text-align:center;
    color:#333;
}
.members {
    background:#f9f9f9;
    border-radius:12px;
    padding:10px;
    margin-top:10px;
    text-align: right;
}
.members ul {
    list-style: disc inside;
    padding-left:0;
    margin:0;
    direction:rtl;
    text-align: right;
}
.members li {
    font-size:14px;
    padding:2px 0;
}

/* ریسپانسیو موبایل */
@media(max-width:1024px){
    .card h3 { font-size:20px; }
    .card p { font-size:15px; }
    .members li { font-size:13px; }
}
@media(max-width:768px){
    .group-grid {
        grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
    }
    .card { padding:15px; }
    .card h3 { font-size:18px; }
    .card p { font-size:14px; }
    .members li { font-size:12px; }
}
@media(max-width:480px){
    .group-grid {
        grid-template-columns: 1fr;
    }
    .card { padding:12px; }
    .card h3 { font-size:16px; }
    .card p { font-size:13px; }
    .members li { font-size:12px; }
}
</style>
</head>
<body>

<div class="container">
<h2> گروه های دانش آموزی </h2>
<div class="group-grid">
<?php while($g = $groups->fetch_assoc()):
    $members_res = $conn->query("SELECT s.name FROM student_group sg JOIN students s ON sg.student_id=s.id WHERE sg.group_id=".$g['id']." ORDER BY s.name");
    $members = [];
    while($m = $members_res->fetch_assoc()) $members[] = $m['name'];
?>
<div class="card">
    <?php if($g['image']): ?>
    <img src="<?php echo $g['image']; ?>" class="group-img">
    <?php endif; ?>
    <h3><?php echo $g['name']; ?></h3>
    <p>شماره گروه: <?php echo $g['group_number']; ?></p>
    <p>نوع گروه: <?php echo $g['type']; ?></p>
    <?php if(count($members) > 0): ?>
    <div class="members">
        <strong> :اعضا</strong>
        <ul>
        <?php foreach($members as $m) echo "<li>".$m."</li>"; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
<?php endwhile; ?>
</div>
</div>

</body>
</html>
