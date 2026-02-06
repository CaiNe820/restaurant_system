<?php
include 'db.php';

/* 删除订单 */
if(isset($_GET['delete'])){
    $order_id = intval($_GET['delete']);
    if($order_id > 0){
        // 删除 order_items
        $stmt = mysqli_prepare($conn, "DELETE FROM order_items WHERE order_id=?");
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);

        // 删除 orders
        $stmt2 = mysqli_prepare($conn, "DELETE FROM orders WHERE id=?");
        mysqli_stmt_bind_param($stmt2, "i", $order_id);
        mysqli_stmt_execute($stmt2);
    }
    header("Location: admin_orders.php");
    exit;
}

// 获取所有 walk-in orders
$orders_res = mysqli_query($conn,"
    SELECT * FROM orders WHERE order_type='walkin' ORDER BY order_datetime DESC
");

// 获取所有 order_items，一次查询
$all_items_res = mysqli_query($conn,"
    SELECT * FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE order_type='walkin')
");

// 按 order_id 分组 items
$order_items = [];
while($item = mysqli_fetch_assoc($all_items_res)){
    $order_items[$item['order_id']][] = $item;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Walk-in Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">🧾 Walk-in Orders</h2>

    <?php
    if(mysqli_num_rows($orders_res) == 0){
        echo '<div class="alert alert-info">No walk-in orders found.</div>';
    }

    while($order = mysqli_fetch_assoc($orders_res)){
        $order_id = $order['id'];
        $items = $order_items[$order_id] ?? [];
    ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5>Order #<?=$order_id?> | Table <?=$order['table_no']?></h5>
                <p class="mb-1"><b>People:</b> <?=$order['number_of_people']?></p>
                <p class="mb-1"><b>Time:</b> <?=$order['order_datetime']?></p>
                <p><b>Total:</b> RM <?=number_format($order['total_price'],2)?></p>

                <h6>🍽 Ordered Items</h6>
                <ul class="list-group mb-3">
                    <?php foreach($items as $item): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <?=htmlspecialchars($item['food_name'])?> × <?=$item['qty']?>
                            <span>RM <?=number_format($item['price']*$item['qty'],2)?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <a href="?delete=<?=$order_id?>" 
                   onclick="return confirm('Delete this order?')" 
                   class="btn btn-danger btn-sm">
                    🗑 Delete Order
                </a>
            </div>
        </div>
    <?php } ?>

    <a href="admin_dashboard.php" class="btn btn-secondary">⬅ Back</a>
</div>

</body>
</html>
