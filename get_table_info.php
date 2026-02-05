<?php
include 'db.php';

$booking_date = $_GET['booking_date'] ?? '';
$booking_time = $_GET['booking_time'] ?? '';

$total_tables = 50;

/* 同一天同一时间 已被占用的桌数 */
$sql = "
SELECT COUNT(DISTINCT table_no) AS used_tables
FROM orders
WHERE DATE(order_datetime) = '$booking_date'
AND TIME(order_datetime) = '$booking_time'
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$used = $row['used_tables'] ?? 0;
$remaining = $total_tables - $used;
$next_table = $used + 1;

if ($remaining < 0) $remaining = 0;

echo json_encode([
    'remaining_tables' => $remaining,
    'next_table' => $remaining > 0 ? $next_table : null
]);
