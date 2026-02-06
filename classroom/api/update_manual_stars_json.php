<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if(!$data || !isset($data['id']) || !isset($data['stars'])){
    echo json_encode(['ok'=>false,'msg'=>'داده‌ها ناقص هستند']); exit;
}

$file = __DIR__.'/manual_stars.json';
$manualStars = [];
if(file_exists($file)){
    $manualStars = json_decode(file_get_contents($file), true) ?? [];
}

$manualStars[$data['id']] = max(0,intval($data['stars']));
file_put_contents($file,json_encode($manualStars,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

echo json_encode(['ok'=>true]);
