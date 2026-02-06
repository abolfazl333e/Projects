<?php
require_once '../inc/db.php';
$conn->set_charset('utf8mb4');

$id = $_POST['id'] ?? null;
$title = $_POST['title'] ?? '';
$lesson_id = $_POST['lesson_id'] ?? '';
$results = $_POST['results'] ?? '';

if(!$title || !$lesson_id){
    echo json_encode(['ok'=>false,'msg'=>'عنوان و درس اجباری است']);
    exit;
}

if($id){ // ویرایش
    $stmt = $conn->prepare("UPDATE experiments SET title=?, lesson_id=?, results=? WHERE id=?");
    $stmt->bind_param("sisi",$title,$lesson_id,$results,$id);
    $stmt->execute();
    $exp_id = $id;
}else{ // ایجاد جدید
    $stmt = $conn->prepare("INSERT INTO experiments(title, lesson_id, results) VALUES(?,?,?)");
    $stmt->bind_param("sis",$title,$lesson_id,$results);
    $stmt->execute();
    $exp_id = $conn->insert_id;
}

// آپلود تصاویر
if(!empty($_FILES['images']['name'][0])){
    $uploadDir = '../experiments_images/';
    foreach($_FILES['images']['tmp_name'] as $index=>$tmpName){
        $filename = time().'_'.basename($_FILES['images']['name'][$index]);
        if(move_uploaded_file($tmpName, $uploadDir.$filename)){
            $stmt2 = $conn->prepare("INSERT INTO experiment_images(experiment_id, filename) VALUES(?,?)");
            $stmt2->bind_param("is",$exp_id,$filename);
            $stmt2->execute();
        }
    }
}

echo json_encode(['ok'=>true]);
