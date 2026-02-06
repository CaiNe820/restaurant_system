<?php
include 'db.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

// ====== 获取 POST 数据并验证 ======
$name   = trim($_POST['customer_name'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
$date   = $_POST['booking_date'] ?? '';
$time   = $_POST['booking_time'] ?? '';
$people = intval($_POST['number_of_people'] ?? 0);

if(!$name || !$phone || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}$/', $time) || $people < 1){
    die("❌ 数据不完整或格式错误");
}

// ====== 时间验证 ======
$booking_ts = strtotime("$date $time");
$open  = strtotime("$date 10:00");
$close = strtotime("$date 21:45");
if($booking_ts < $open || $booking_ts > $close){
    die("❌ 预约时间不在营业时间内");
}

// ====== 开启事务 ======
mysqli_begin_transaction($conn);
try {
    // ====== 桌号分配（10桌） ======
    $total_tables = 10;

    $stmt = mysqli_prepare($conn, "
        SELECT table_number FROM bookings 
        WHERE booking_date=? AND booking_time=? FOR UPDATE
    ");
    mysqli_stmt_bind_param($stmt, "ss", $date, $time);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $used = [];
    while($r = mysqli_fetch_assoc($res)){
        $used[] = intval($r['table_number']);
    }

    $table_number = null;
    for($i=1; $i<=$total_tables; $i++){
        if(!in_array($i, $used)){
            $table_number = $i;
            break;
        }
    }

    if(!$table_number){
        throw new Exception("❌ 该时间段已满桌");
    }

    // ====== 插入预约 ======
    $stmt_insert = mysqli_prepare($conn, "
        INSERT INTO bookings 
        (customer_name, phone, booking_date, booking_time, number_of_people, table_number)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt_insert, "sssiii", $name, $phone, $date, $time, $people, $table_number);
    mysqli_stmt_execute($stmt_insert);

    // ====== 提交事务 ======
    mysqli_commit($conn);

} catch (Exception $e){
    mysqli_rollback($conn);
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Booking Success</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="card p-4 text-center shadow">
<h2 class="text-success">✅ 预约成功</h2>
<p>👤 姓名：<?=htmlspecialchars($name)?></p>
<p>👥 人数：<?=$people?> 人</p>
<p>🕒 时间：<?=$date?> <?=$time?></p>
<p>🍽️ 桌号：<strong><?=$table_number?></strong></p>
<a href="index.php" class="btn btn-primary mt-3">返回首页</a>
</div>
</div>
</body>
</html>