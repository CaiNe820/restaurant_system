<?php
include 'db.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

// 获取表单数据
$customer_name = $_POST['customer_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$people = $_POST['number_of_people'] ?? 0;
$booking_date = $_POST['booking_date'] ?? '';
$booking_time = $_POST['booking_time'] ?? '';
$foods = $_POST['food'] ?? [];
$qtys  = $_POST['qty'] ?? [];

if($people < 1 || $people > 6){
    die("Invalid number of people");
}

$order_datetime = date('Y-m-d H:i:s');
$table_no = rand(1,50);
$total = 0;
$items = [];

// 统计总价 & 准备菜品数据
foreach($foods as $id => $v){
    $q = intval($qtys[$id]);
    $food = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM food_menu WHERE id=$id"));
    $subtotal = $food['price'] * $q;
    $total += $subtotal;
    $items[] = [$food['food_name'],$food['price'],$q];
}

// 插入订单表（booking类型）
mysqli_query($conn,"
INSERT INTO orders
(order_type, table_no, number_of_people, customer_name, phone, order_datetime, booking_date, booking_time, total_price)
VALUES
('booking', $table_no, $people, '$customer_name', '$phone', '$order_datetime', '$booking_date', '$booking_time', $total)
");

$order_id = mysqli_insert_id($conn);

// 插入订单明细
foreach($items as $it){
    mysqli_query($conn,"
    INSERT INTO order_items (order_id, food_name, price, qty)
    VALUES ($order_id,'$it[0]',$it[1],$it[2])
    ");
}
?>
<!DOCTYPE html>
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Booking Success</title>
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="card p-4 shadow">
<h3 class="text-success">✅ Booking Successful</h3>

<p><b>Customer:</b> <?=$customer_name?></p>
<p><b>Phone:</b> <?=$phone?></p>
<p><b>Table:</b> <?=$table_no?></p>
<p><b>Number of People:</b> <?=$people?></p>
<p><b>Booking Date:</b> <?=$booking_date?> <?=$booking_time?></p>
<p><b>Order Time:</b> <?=$order_datetime?></p>

<h5 class="mt-3">🍽 Ordered Food</h5>
<ul class="list-group">
<?php foreach($items as $it): ?>
<li class="list-group-item">
<?=$it[0]?> × <?=$it[2]?> — RM <?=number_format($it[1]*$it[2],2)?>
</li>
<?php endforeach; ?>
</ul>

<h4 class="mt-3 text-end">Total: RM <?=number_format($total,2)?></h4>
<a href="index.php" class="btn btn-primary mt-3">Back</a>
</div>
</div>
</body>
</html>
