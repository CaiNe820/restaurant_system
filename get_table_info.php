<?php
include 'db.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';

$total_tables = 10;

header('Content-Type: application/json');

if(!$date || !$time){
    echo json_encode([
        'remaining' => 0,
        'next_table' => null
    ]);
    exit;
}

// 查已使用桌号（安全 prepared statement）
$stmt = mysqli_prepare($conn, "SELECT table_no FROM bookings WHERE booking_date=? AND booking_time=?");
mysqli_stmt_bind_param($stmt, "ss", $date, $time);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$used = [];
while($r = mysqli_fetch_assoc($res)){
    $used[] = intval($r['table_no']);
}

// 找下一个可用桌号
$next_table = null;
for($i=1; $i<=$total_tables; $i++){
    if(!in_array($i, $used)){
        $next_table = $i;
        break;
    }
}

$remaining = $total_tables - count($used);

echo json_encode([
    'remaining' => $remaining,
    'next_table' => $next_table
]);
