<?php
require_once 'inc/db.php';

// ---------- افزودن گروه ----------
if(isset($_POST['add_group'])){
    $name = $_POST['name'];
    $type = $_POST['type'];
    $group_number = $_POST['group_number'];
    $image_path = null;

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = "group_" . time() . "." . $ext;
        $upload_dir = "uploads/groups/";
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$image_name);
        $image_path = $upload_dir.$image_name;
    }

    $stmt = $conn->prepare("INSERT INTO groups (name,type,group_number,image) VALUES (?,?,?,?)");
    $stmt->bind_param("ssis",$name,$type,$group_number,$image_path);
    $stmt->execute();
    header("Location: manage_groups.php");
    exit();
}




// ---------- ویرایش گروه ----------
if(isset($_POST['edit_group'])){
    $id = $_POST['group_id'];
    $name = $_POST['name'];
    $type = $_POST['type'];
    $group_number = $_POST['group_number'];
    $image_path = $_POST['current_image'];
    
    if(empty($_POST) && empty($_FILES)){
    die("⚠️ هیچ داده‌ای از فرم دریافت نشد. احتمالاً فایل یا فرم از حد مجاز PHP بزرگ‌تر بوده است.");
}


    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = "group_" . time() . "." . $ext;
        $upload_dir = "uploads/groups/";
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$image_name);
        $image_path = $upload_dir.$image_name;
    }

    $stmt = $conn->prepare("UPDATE groups SET name=?, type=?, group_number=?, image=? WHERE id=?");
    $stmt->bind_param("ssisi",$name,$type,$group_number,$image_path,$id);
    $stmt->execute();
    header("Location: manage_groups.php?edit_group=$id");
    exit();
}

// ---------- حذف گروه ----------
if(isset($_GET['delete_group'])){
    $id = $_GET['delete_group'];
    $conn->query("DELETE FROM groups WHERE id=$id");
    header("Location: manage_groups.php");
    exit();
}

// ---------- افزودن اعضای جدید ----------
if(isset($_POST['assign_multiple'])){
    $student_ids = $_POST['student_ids'];
    $group_id = $_POST['group_id'];
    foreach($student_ids as $student_id){
        $check = $conn->query("SELECT * FROM student_group WHERE student_id=$student_id AND group_id=$group_id");
        if($check->num_rows == 0){
            $stmt = $conn->prepare("INSERT INTO student_group (student_id, group_id) VALUES (?,?)");
            $stmt->bind_param("ii",$student_id,$group_id);
            $stmt->execute();
        }
    }
    header("Location: manage_groups.php?edit_group=$group_id");
    exit();
}

// ---------- حذف اعضای چندتایی ----------
if(isset($_POST['remove_members'])){
    $ids = $_POST['sg_ids'];
    if(!empty($ids)){
        $id_list = implode(',', array_map('intval',$ids));
        $conn->query("DELETE FROM student_group WHERE id IN ($id_list)");
    }
    exit('ok');
}

// ---------- دریافت اطلاعات گروه‌ها ----------
$edit_group = null;
if(isset($_GET['edit_group'])){
    $id = $_GET['edit_group'];
    $res = $conn->query("SELECT * FROM groups WHERE id=$id");
    $edit_group = $res->fetch_assoc();
}

$groups = $conn->query("SELECT * FROM groups ORDER BY group_number ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name");
?>

<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<title>مدیریت گروه‌ها و اعضا</title>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Vazirmatn', sans-serif; background: linear-gradient(135deg,#e0eafc,#cfdef3); margin:0; padding:0; }
.container { width:95%; max-width:1200px; margin:30px auto; padding:20px; }
h2,h3,h4 { text-align:center; color:#333; margin-bottom:15px; }
.card { background:#fff; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.15); padding:20px; margin-bottom:20px; transition:0.3s; position:relative; }
.card:hover { transform:translateY(-3px); box-shadow:0 15px 30px rgba(0,0,0,0.2); }
form { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; align-items:center; margin-bottom:15px; }
input, select, button, input[type=file] { padding:10px 15px; border-radius:8px; border:1px solid #ccc; font-size:14px; }
button { background: linear-gradient(45deg,#007bff,#00c6ff); color:#fff; border:none; font-weight:bold; cursor:pointer; transition:0.3s; }
button:hover { opacity:0.85; }
table { width:100%; border-collapse:collapse; margin-top:20px; box-shadow:0 5px 15px rgba(0,0,0,0.1); border-radius:8px; overflow:hidden; }
th { background: linear-gradient(45deg,#007bff,#00c6ff); color:#fff; padding:12px; }
td { padding:12px; text-align:center; vertical-align:middle;border:1px solid #ccc; }
tr:nth-child(even) { background:#f9f9f9; }
a { text-decoration:none; color:#007bff; font-weight:bold; }
a:hover { color:#0056b3; }
img.group-img { width:80px; height:80px; object-fit:cover; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.2);}
ul { margin:0; padding-left:15px; text-align:right;direction:rtl;}
.select-multi { min-width:200px; }
.close-btn { position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold; color:#ff4d4f; }
.close-btn:hover { color:#d60000; }
@media (max-width:768px) { form { flex-direction:column; } table { font-size:12px; } }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container">

<h2>مدیریت گروه‌ها و اعضا</h2>

<!-- فرم افزودن گروه -->
<div class="card">
<h3>افزودن گروه جدید</h3>
<form method="post" enctype="multipart/form-data">
<input type="text" name="name" placeholder="نام گروه" required>
<input type="number" name="group_number" placeholder="شماره گروه" min="1" required>
<select name="type">
<option value="ورزشی">ورزشی</option>
<option value="درسی">درسی</option>
</select>
<input type="file" name="image" accept="image/*">
<button type="submit" name="add_group">افزودن گروه</button>
</form>
</div>

<!-- فرم ویرایش گروه -->
<?php if($edit_group): ?>
<div class="card" id="edit-card">
<span class="close-btn" id="close-edit">×</span>
<h3>ویرایش گروه: <?php echo $edit_group['name']; ?></h3>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="group_id" value="<?php echo $edit_group['id']; ?>">
<input type="text" name="name" value="<?php echo $edit_group['name']; ?>" required>
<input type="number" name="group_number" value="<?php echo $edit_group['group_number']; ?>" min="1" required>
<select name="type">
<option value="ورزشی" <?php if($edit_group['type']=='ورزشی') echo 'selected'; ?>>ورزشی</option>
<option value="درسی" <?php if($edit_group['type']=='درسی') echo 'selected'; ?>>درسی</option>
</select>
<?php if($edit_group['image']): ?>
<img src="<?php echo $edit_group['image']; ?>" class="group-img">
<?php endif; ?>
<input type="file" name="image" accept="image/*">
<input type="hidden" name="current_image" value="<?php echo $edit_group['image']; ?>">
<button type="submit" name="edit_group">ذخیره تغییرات</button>
</form>

<h4>اعضای گروه</h4>
<button id="remove-selected">حذف اعضای انتخاب شده</button>
<ul id="member-list">
<?php
$members_res = $conn->query("SELECT sg.id as sg_id, s.name FROM student_group sg JOIN students s ON sg.student_id=s.id WHERE sg.group_id=".$edit_group['id']." ORDER BY s.name");
while($m = $members_res->fetch_assoc()){
    echo "<li data-sg-id='{$m['sg_id']}'><input type='checkbox' class='member-checkbox'> {$m['name']}</li>";
}
?>
</ul>

<h4>افزودن دانش‌آموز به گروه</h4>
<form method="post">
<input type="hidden" name="group_id" value="<?php echo $edit_group['id']; ?>">
<select name="student_ids[]" multiple class="select-multi" required size="5">
<?php
$all_students = $conn->query("SELECT * FROM students ORDER BY name");
while($s = $all_students->fetch_assoc()){
    echo "<option value='{$s['id']}'>{$s['name']}</option>";
}
?>
</select>
<button type="submit" name="assign_multiple">افزودن</button>
</form>
</div>
<?php endif; ?>

<!-- جدول گروه‌ها -->
<h3>لیست گروه‌ها</h3>
<table>
<tr>
<th>شماره گروه</th>
<th>نام گروه</th>
<th>نوع گروه</th>
<th>عکس گروه</th>
<th>اعضا</th>
<th>عملیات</th>
</tr>
<?php while($g = $groups->fetch_assoc()):
    $members_res = $conn->query("SELECT s.name FROM student_group sg JOIN students s ON sg.student_id=s.id WHERE sg.group_id=".$g['id']." ORDER BY s.name");
    $members = [];
    while($m = $members_res->fetch_assoc()) $members[] = $m['name'];
?>
<tr>
<td><?php echo $g['group_number']; ?></td>
<td><?php echo $g['name']; ?></td>
<td><?php echo $g['type']; ?></td>
<td><?php if($g['image']): ?><img src="<?php echo $g['image']; ?>" class="group-img"><?php endif; ?></td>
<td><ul><?php foreach($members as $m) echo "<li>".$m."</li>"; ?></ul></td>
<td>
<a href="?edit_group=<?php echo $g['id']; ?>">ویرایش</a> |
<a href="?delete_group=<?php echo $g['id']; ?>" onclick="return confirm('آیا مطمئن هستید؟')">حذف</a>
</td>
</tr>
<?php endwhile; ?>
</table>

</div>

<script>
// بستن فرم ویرایش با ضربدر و پاک کردن edit_group از URL
$('#close-edit').click(function(){
    $('#edit-card').slideUp();
    const url = new URL(window.location.href);
    url.searchParams.delete('edit_group');
    window.history.replaceState({}, document.title, url.toString());
});

// حذف چندتایی اعضای گروه با AJAX
$('#remove-selected').click(function(){
    if(!confirm('آیا مطمئن هستید که می‌خواهید این اعضا را حذف کنید؟')) return;
    var ids = [];
    $('#member-list li').each(function(){
        var checkbox = $(this).find('.member-checkbox');
        if(checkbox.is(':checked')){
            ids.push($(this).data('sg-id'));
        }
    });
    if(ids.length === 0) return;
    $.ajax({
        url:'manage_groups.php',
        type:'POST',
        data:{ remove_members:1, sg_ids: ids },
        success:function(){
            $('#member-list li').each(function(){
                var checkbox = $(this).find('.member-checkbox');
                if(checkbox.is(':checked')) $(this).remove();
            });
        }
    });
});
</script>

</body>
</html>
