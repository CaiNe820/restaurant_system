<?php
include 'db.php';

$id = intval($_GET['id'] ?? 0);
if($id <= 0){
    die("Invalid booking ID");
}

// 使用 prepared statement 安全删除
$stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: admin_bookings.php");
exit;
