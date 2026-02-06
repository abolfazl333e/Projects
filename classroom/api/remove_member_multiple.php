<?php
require_once '../inc/db.php';

if(isset($_POST['sg_ids'])){
    $sg_ids = $_POST['sg_ids']; // آرایه
    $ids = implode(',', array_map('intval', $sg_ids));
    $conn->query("DELETE FROM student_group WHERE id IN ($ids)");
    echo "success";
}
?>
