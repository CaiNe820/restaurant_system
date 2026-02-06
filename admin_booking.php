<?php
session_start();
include 'db.php';

// 登录验证
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit;
}

// ========== 删除功能 ==========
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    if($id > 0){
        $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }
    header("Location: admin_bookings.php");
    exit;
}

// ========== 分页参数 ==========
$page = intval($_GET['page'] ?? 1);
$per_page = 10; // 每页显示 10 条
$start = ($page - 1) * $per_page;

// ========== 获取数据 ==========
$bookings_res = mysqli_query($conn, "SELECT * FROM bookings ORDER BY booking_date DESC, booking_time DESC LIMIT $start,$per_page");

// 总记录数
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM bookings"))['c'];
$total_pages = ceil($total_bookings / $per_page);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <h2>Admin - Bookings</h2>
    <p>Logged in as: <?=htmlspecialchars($_SESSION['admin'])?> | <a href="admin_logout.php">Logout</a> | <a href="admin_dashboard.php">⬅ Back Dashboard</a></p>

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Date</th>
                <th>Time</th>
                <th>People</th>
                <th>Table</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($b = mysqli_fetch_assoc($bookings_res)): ?>
            <tr>
                <td><?=$b['id']?></td>
                <td><?=htmlspecialchars($b['customer_name'])?></td>
                <td><?=htmlspecialchars($b['phone'])?></td>
                <td><?=$b['booking_date']?></td>
                <td><?=$b['booking_time']?></td>
                <td><?=$b['number_of_people']?></td>
                <td><?=$b['table_no']?></td>
                <td>
                    <a href="?delete=<?=$b['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('确定删除该预约吗?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
      <ul class="pagination">
        <?php for($i=1;$i<=$total_pages;$i++): ?>
        <li class="page-item <?=$i==$page?'active':''?>">
            <a class="page-link" href="?page=<?=$i?>"><?=$i?></a>
        </li>
        <?php endfor; ?>
      </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
