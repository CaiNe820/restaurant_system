<?php
session_start();
include 'db.php';

$error = '';

if(isset($_POST['username']) && isset($_POST['password'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']); // 如果数据库是 MD5

    // 使用 prepared statement 防 SQL 注入
    $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE username=? AND password=?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($res) > 0){
        $_SESSION['admin'] = $username;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100" style="background:#f8f9fa;">
<div class="card p-4 shadow-sm" style="width:350px;">
    <h3 class="text-center mb-3">Admin Login</h3>
    <?php if($error) echo "<p class='text-danger'>$error</p>"; ?>
    <form method="post">
        <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
        <button class="btn btn-success w-100">Login</button>
    </form>
</div>
</body>
</html>
