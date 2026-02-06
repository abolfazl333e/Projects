<?php
require_once '../inc/db.php';
$conn->set_charset('utf8mb4');

$id = $_GET['id'] ?? 0;
$res = $conn->query("SELECT * FROM experiments WHERE id=".$id);
if($exp = $res->fetch_assoc()){
    echo json_encode(['ok'=>true,'exp'=>$exp]);
}else{
    echo json_encode(['ok'=>false,'msg'=>'آزمایش پیدا نشد']);
}
