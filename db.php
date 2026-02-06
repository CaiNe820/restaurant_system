<?php
$host = 'localhost';
$user = 'root';
$pass = '820729';
$db   = 'restaurant_system';

// 建立连接
$conn = mysqli_connect($host, $user, $pass, $db);

// 检查连接
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// 设置字符集，防止中文乱码
mysqli_set_charset($conn, 'utf8mb4');
?>
