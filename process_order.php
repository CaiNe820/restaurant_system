<?php
include 'db.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

// ✅ 获取 POST 数据并验证
$people = intval($_POST['number_of_people'] ?? 0);
$foods  = $_POST['food'] ?? [];
$qtys   = $_POST['qty'] ?? [];

// 当前时间（精确到分钟）
$order_datetime = date('Y-m-d H:i:00');

// 营业时间限制
$now   = strtotime($order_datetime);
$open  = strtotime(date('Y-m-d').' 10:00');
$close = strtotime(date('Y-m-d').' 21:45');
if ($now < $open || $now > $close) {
    die("❌ 不在营业 / 点餐时间内");
}

// 人数限制
if ($people < 1 || $people > 6) {
    die("❌ Invalid number of people");
}

// 开启事务，防止并发
mysqli_begin_transaction($conn);
try {
    // 分配桌号（50张桌）
    $total_tables = 50;
    $tables = range(1, $total_tables);

    $stmt = mysqli_prepare($conn, "SELECT table_number FROM orders WHERE order_datetime=? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "s", $order_datetime);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $used_tables = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $used_tables[] = intval($row['table_number']);
    }

    $table_number = null;
    foreach ($tables as $t) {
        if (!in_array($t, $used_tables)) {
            $table_number = $t;
            break;
        }
    }

    if (!$table_number) {
        throw new Exception("❌ 当前时间段已满桌，请稍后再来");
    }

    // 计算总价并准备订单菜品
    $total = 0;
    $items = [];
    foreach ($foods as $id => $v) {
        $id = intval($id);
        $q = intval($qtys[$id] ?? 0);
        if ($q <= 0) continue; // 忽略数量为 0 或负数

        // 查询菜品信息
        $stmt_food = mysqli_prepare($conn, "SELECT food_name, price FROM food_menu WHERE id=?");
        mysqli_stmt_bind_param($stmt_food, "i", $id);
        mysqli_stmt_execute($stmt_food);
        $res_food = mysqli_stmt_get_result($stmt_food);
        $food = mysqli_fetch_assoc($res_food);
        if (!$food) continue;

        $subtotal = $food['price'] * $q;
        $total += $subtotal;
        $items[] = [
            'name' => $food['food_name'],
            'price' => $food['price'],
            'qty' => $q
        ];
    }

    // 插入订单（同时插入 order_datetime 和 order_time）

    $stmt_order = mysqli_prepare($conn, "
    INSERT INTO orders (order_type, table_number, number_of_people, order_datetime, order_time, total_price)
    VALUES ('walkin', ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_order, "iissd", $table_number, $people, $order_datetime, $order_datetime, $total);
    mysqli_stmt_execute($stmt_order);
    $order_id = mysqli_insert_id($conn);

    // 插入订单菜品
    $stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, food_name, price, qty) VALUES (?, ?, ?, ?)");
    foreach ($items as $it) {
        mysqli_stmt_bind_param($stmt_item, "isdi", $order_id, $it['name'], $it['price'], $it['qty']);
        mysqli_stmt_execute($stmt_item);
    }

    // 提交事务
    mysqli_commit($conn);

} catch (Exception $e) {
    mysqli_rollback($conn);
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h3 class="text-success">✅ Order Successful</h3>
        <p><b>People:</b> <?=$people?></p>
        <p><b>Time:</b> <?=$order_datetime?></p>
        <p><b>Table:</b> <?=$table_number?></p>

        <h5 class="mt-3">🍽 Ordered Food</h5>
        <?php if(count($items) > 0): ?>
            <ul class="list-group">
                <?php foreach($items as $it): ?>
                    <li class="list-group-item">
                        <?=$it['name']?> × <?=$it['qty']?> — RM <?=number_format($it['price']*$it['qty'],2)?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">No food selected</p>
        <?php endif; ?>

        <h4 class="mt-3 text-end">Total: RM <?=number_format($total,2)?></h4>
        <a href="index.php" class="btn btn-primary mt-3">Back</a>
    </div>
</div>
</body>
</html>