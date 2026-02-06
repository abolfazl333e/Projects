<?php
require_once '../inc/db.php';
$conn->set_charset("utf8mb4");
header('Content-Type: application/json');

function fa_to_en($str){
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($fa, $en, $str);
}

$lesson_id = intval($_POST['lesson_id'] ?? 0);
$examDate = $_POST['examDate'] ?? '';
$examWeekday = $_POST['examWeekday'] ?? '';
$examWeekday = '-';

if(!$examDate){
    echo json_encode(['success'=>false,'message'=>'تاریخ یا روز هفته وارد نشده است']); exit;
}

$examDate = fa_to_en($examDate);

if(!$lesson_id){
    echo json_encode(['success'=>false,'message'=>'درس وارد نشده است']); exit;
}

// 🔹 ذخیره عکس‌ها
$exam_image_paths = [];

if(!empty($_FILES['exam_image']['name'][0])){
    $upload_dir = "../images/exams/";
    if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    foreach($_FILES['exam_image']['tmp_name'] as $i => $tmp){
        if($_FILES['exam_image']['error'][$i] === 0){
            $ext = pathinfo($_FILES['exam_image']['name'][$i], PATHINFO_EXTENSION);
            $file = uniqid('exam_') . '.' . $ext;

            if(move_uploaded_file($tmp, $upload_dir . $file)){
                $exam_image_paths[] = "images/exams/" . $file;
            }
        }
    }
}

$exam_images_str = implode(',', $exam_image_paths);


// 🔹 ثبت نمرات دانش‌آموزان
$success = true;
foreach($_POST as $key=>$value){
    if(strpos($key,'score_')===0){
        $student_id = intval(str_replace('score_','',$key));
        $score = $value;

        $stmt = $conn->prepare("INSERT INTO exams (student_id, lesson_id, score, exam_date, weekday, exam_image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $student_id, $lesson_id, $score, $examDate, $examWeekday, $exam_images_str);
        if(!$stmt->execute()) $success = false;
    }
}

if($success){
    echo json_encode(['success'=>true,'message'=>'امتحان با موفقیت ثبت شد','redirect'=>'admin_exams.php']);
}else{
    echo json_encode(['success'=>false,'message'=>'خطا در ثبت برخی از نمرات']);
}
