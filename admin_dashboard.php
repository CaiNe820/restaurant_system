<?php
session_start();
include 'db.php';
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit;
}

// ========== 删除功能 ==========
if(isset($_GET['delete_booking'])){
    $id = intval($_GET['delete_booking']);
    if($id > 0){
        $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }
    header("Location: admin_dashboard.php");
    exit;
}

if(isset($_GET['delete_order'])){
    $id = intval($_GET['delete_order']);
    if($id > 0){
        $stmt = mysqli_prepare($conn, "DELETE FROM orders WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }
    header("Location: admin_dashboard.php");
    exit;
}

// ========== 分页参数 ==========
$booking_page = intval($_GET['booking_page'] ?? 1);
$order_page   = intval($_GET['order_page'] ?? 1);
$per_page = 10; // 每页显示 10 条

$booking_start = ($booking_page - 1) * $per_page;
$order_start   = ($order_page - 1) * $per_page;

// ========== 获取数据 ==========
$bookings_res = mysqli_query($conn, "SELECT * FROM bookings ORDER BY booking_date DESC, booking_time DESC LIMIT $booking_start,$per_page");
$orders_res   = mysqli_query($conn, "SELECT * FROM orders ORDER BY order_time DESC LIMIT $order_start,$per_page");

// 获取总条数用于分页
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM bookings"))['c'];
$total_orders   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM orders"))['c'];
$total_booking_pages = ceil($total_bookings / $per_page);
$total_order_pages   = ceil($total_orders / $per_page);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <h2>Admin Dashboard</h2>
    <p>Logged in as: <?=htmlspecialchars($_SESSION['admin'])?> | <a href="admin_logout.php">Logout</a></p>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#bookingTab">📋 Bookings</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#orderTab">🧾 Orders</button></li>
    </ul>

    <div class="tab-content">

        <!-- Booking Tab -->
        <div class="tab-pane fade show active" id="bookingTab">
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
                            <a href="?delete_booking=<?=$b['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('确定删除该预约吗?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Booking Pagination -->
            <nav>
              <ul class="pagination">
                <?php for($i=1;$i<=$total_booking_pages;$i++): ?>
                <li class="page-item <?=$i==$booking_page?'active':''?>">
                  <a class="page-link" href="?booking_page=<?=$i?>"><?=$i?></a>
                </li>
                <?php endfor; ?>
              </ul>
            </nav>
        </div>

        <!-- Order Tab -->
        <div class="tab-pane fade" id="orderTab">
            <table class="table table-bordered bg-white">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>People</th>
                        <th>Table</th>
                        <th>Time</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($o = mysqli_fetch_assoc($orders_res)): ?>
                    <tr>
                        <td><?=$o['id']?></td>
                        <td><?=$o['number_of_people']?></td>
                        <td><?=$o['table_no']?></td>
                        <td><?=$o['order_datetime'] ?? $o['order_time']?></td>
                        <td>RM <?=number_format($o['total_price'],2)?></td>
                        <td>
                            <a href="?delete_order=<?=$o['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('确定删除该订单吗?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Order Pagination -->
            <nav>
              <ul class="pagination">
                <?php for($i=1;$i<=$total_order_pages;$i++): ?>
                <li class="page-item <?=$i==$order_page?'active':''?>">
                  <a class="page-link" href="?order_page=<?=$i?>"><?=$i?></a>
                </li>
                <?php endfor; ?>
              </ul>
            </nav>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
