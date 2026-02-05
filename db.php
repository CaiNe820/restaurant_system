<?php

$host = 'localhost';
$user = 'root';
$pass = '820729';
$db = 'restaurant_system';

$conn = mysqli_connect($host,$user,$pass,$db);

if (!$conn) {
    die('Database connection failed');
}

?>