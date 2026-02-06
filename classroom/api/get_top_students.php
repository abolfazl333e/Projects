<?php
require_once '../inc/db.php';
date_default_timezone_set('Asia/Tehran');

// گرفتن لیست دانش‌آموزان
$all_students = [];
$res = $conn->query("SELECT id, name FROM students");
while ($row = $res->fetch_assoc()) {
    $all_students[$row['id']] = $row['name'];
}

// گرفتن رفتارها
$students = [];
$res = $conn->query("SELECT * FROM student_behavior");
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}

// توابع تاریخ شمسی
function gregorian_to_jalali($gy, $gm, $gd) {
    $g_days_in_month = [31,28,31,30,31,30,31,31,30,31,30,31];
    $j_days_in_month = [31,31,31,31,31,31,30,30,30,30,30,29];
    if(($gy%4==0 && $gy%100!=0) || ($gy%400==0)) $g_days_in_month[1]=29;
    $gy -= 1600; $gm -= 1; $gd -= 1;
    $g_day_no = 365*$gy + intval(($gy+3)/4) - intval(($gy+99)/100) + intval(($gy+399)/400);
    for ($i=0; $i<$gm; $i++) $g_day_no += $g_days_in_month[$i];
    $g_day_no += $gd;
    $j_day_no = $g_day_no - 79;
    $j_np = intval($j_day_no / 12053);
    $j_day_no %= 12053;
    $jy = 979 + 33*$j_np + 4*intval($j_day_no/1461);
    $j_day_no %= 1461;
    if ($j_day_no >= 366) {
        $jy += intval(($j_day_no-1)/365);
        $j_day_no = ($j_day_no-1)%365;
    }
    for ($i=0; $i<11 && $j_day_no >= $j_days_in_month[$i]; $i++) $j_day_no -= $j_days_in_month[$i];
    $jm = $i+1; $jd = $j_day_no+1;
    return [$jy,$jm,$jd];
}

list($current_year, $current_month) = gregorian_to_jalali(date('Y'), date('n'), date('j'));

// آرشیو ماه‌ها
$months_order = range(7, 12); // مثال: مهر تا اسفند
$result = [];

foreach ($months_order as $m) {

    // بررسی آرشیو
    $archive_res = $conn->query("SELECT * FROM top_students_archive WHERE year=$current_year AND month=$m");
    if($archive_res->num_rows){
        $month_top = [];
        while($row = $archive_res->fetch_assoc()){
            $month_top[] = [
                'id'=>$row['student_id'],
                'name'=>$all_students[$row['student_id']] ?? 'بدون نام',
                'balance'=>$row['balance'],
                'calculated_stars'=>$row['calculated_stars'],
                'manual_stars'=>$row['manual_stars'],
                'total_stars'=>$row['total_stars']
            ];
        }
        $result[] = ['month'=>$m, 'students'=>$month_top, 'archived'=>true];
        continue;
    }

    // محاسبه جدید
    $month_students = [];
    foreach ($students as $s) {
        $ts = strtotime($s['date']);
        if (!$ts) continue;
        list($jy,$jm,$jd) = gregorian_to_jalali(date('Y',$ts), date('n',$ts), date('j',$ts));
        if ($jy==$current_year && $jm==$m) $month_students[] = $s;
    }

    $month_top = [];
    $max_stars = 0;

    foreach ($month_students as $b) {
        $id = $b['student_id'];
        if(!isset($month_top[$id])){
            $month_top[$id] = [
                'id' => $id, 
                'balance' => 0,
                'calculated_stars' => 0,
                'manual_stars' => 0,
                'total_stars' => 0,
                'name' => $all_students[$id] ?? 'بدون نام'
            ];
        }
        $p = (int)($b['positive_count'] ?? 0);
        $n = (int)($b['negative_count'] ?? 0);
        if($b['lesson_id'] != 9){
            $month_top[$id]['balance'] += $p - $n;
            $month_top[$id]['calculated_stars'] = max(0, floor($month_top[$id]['balance']/10));
        } else {
            $month_top[$id]['manual_stars'] += $p;
        }
        $month_top[$id]['total_stars'] = $month_top[$id]['calculated_stars'] + $month_top[$id]['manual_stars'];
    }

    foreach($month_top as $s) if($s['total_stars']>$max_stars) $max_stars=$s['total_stars'];

    $top = [];
    foreach($month_top as $id=>$s){
        if($s['total_stars']==$max_stars && $s['total_stars']>0) $top[$id]=$s;
    }

    uasort($top, fn($a,$b)=>$b['balance'] <=> $a['balance']);
    $result[] = ['month'=>$m, 'students'=>array_values($top), 'archived'=>false];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['year'=>$current_year, 'data'=>$result], JSON_UNESCAPED_UNICODE);
