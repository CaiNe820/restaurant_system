<?php
include 'db.php';

// ✅ 获取 POST 数据并验证
$booking_date = $_POST['booking_date'] ?? '';
$booking_time = $_POST['booking_time'] ?? '';
$number_of_people = intval($_POST['number_of_people'] ?? 0);

// 验证日期、时间、人数
if (!$booking_date || !$booking_time) {
    die("<h2>❌ 请选择预订日期和时间</h2><a href='index.php' class='btn btn-warning'>Back to Menu</a>");
}

if ($number_of_people < 1 || $number_of_people > 6) {
    die("<h2>❌ 每桌只能预订 1-6 人</h2><p>您输入的人数：$number_of_people</p><a href='index.php' class='btn btn-warning'>Back to Menu</a>");
}

$customer_name = "Guest";
$phone = "0000000000";
$total_tables = 50;
$order_time_db = date('Y-m-d H:i:s');
$order_time_display = date('H:i', strtotime($order_time_db));

// 使用事务，防止并发导致桌号重复
mysqli_begin_transaction($conn);

try {
    // 统计当前时间段已订桌数（锁定行）
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_date=? AND booking_time=? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, 'ss', $booking_date, $booking_time);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $booked_tables = $row['count'];

    if ($booked_tables >= $total_tables) {
        throw new Exception("<h2>❌ 当前时间段已满桌</h2><a href='index.php' class='btn btn-warning'>Back to Menu</a>");
    }

    $table_number = $booked_tables + 1;

    // 插入 bookings
    $stmt = mysqli_prepare($conn, "INSERT INTO bookings (customer_name, phone, booking_date, booking_time, number_of_people) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssi', $customer_name, $phone, $booking_date, $booking_time, $number_of_people);
    mysqli_stmt_execute($stmt);
    $booking_id = mysqli_insert_id($conn);

    // 插入 food_orders
    $stmt = mysqli_prepare($conn, "INSERT INTO food_orders (booking_id, order_time) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'is', $booking_id, $order_time_db);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);

    // 插入 food_order_items 并计算总价
    $total_price = 0;
    $order_summary = [];

    if (isset($_POST['food']) && is_array($_POST['food'])) {
        foreach ($_POST['food'] as $food_id => $value) {
            $food_id = intval($food_id);
            $qty = intval($_POST['qty'][$food_id] ?? 0);
            if ($qty <= 0) continue;

            // 安全查询 food_menu
            $stmt = mysqli_prepare($conn, "SELECT food_name, price FROM food_menu WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'i', $food_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($res);
            if (!$row) continue;

            $name = $row['food_name'];
            $price = $row['price'];
            $subtotal = $price * $qty;
            $total_price += $subtotal;

            // 插入 food_order_items
            $stmt_item = mysqli_prepare($conn, "INSERT INTO food_order_items (order_id, food_id, quantity) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_item, 'iii', $order_id, $food_id, $qty);
            mysqli_stmt_execute($stmt_item);

            $order_summary[] = [
                'name' => $name,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $subtotal
            ];
        }
    }

    // 提交事务
    mysqli_commit($conn);

} catch (Exception $e) {
    mysqli_rollback($conn);
    die($e->getMessage());
}

// ✅ 显示订单明细
echo "<!DOCTYPE html>";
echo "<html><head><title>Order Success</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body><div class='container my-5'>";
echo "<div class='card p-4 shadow-sm'>";
echo "<h2 class='text-success'>✅ Booking and Order Successful!</h2>";
echo "<p><strong>Booking Date:</strong> $booking_date</p>";
echo "<p><strong>Booking Time:</strong> $booking_time</p>";
echo "<p><strong>Number of People:</strong> $number_of_people</p>";
echo "<p><strong>桌号:</strong> $table_number</p>";
echo "<p><strong>下单时间:</strong> $order_time_display</p>";

echo "<h3>Order Details:</h3>";
echo "<table class='table table-bordered'>";
echo "<thead><tr><th>Dish</th><th>Quantity</th><th>Unit Price (RM)</th><th>Subtotal (RM)</th></tr></thead><tbody>";
foreach ($order_summary as $item) {
    echo "<tr>";
    echo "<td>{$item['name']}</td>";
    echo "<td>{$item['qty']}</td>";
    echo "<td>".number_format($item['price'],2)."</td>";
    echo "<td>".number_format($item['subtotal'],2)."</td>";
    echo "</tr>";
}
echo "<tr><td colspan='3' class='text-end'><strong>Total Price:</strong></td><td><strong>RM ".number_format($total_price,2)."</strong></td></tr>";
echo "</tbody></table>";
echo "<a href='index.php' class='btn btn-primary mt-3'>Back to Menu</a>";
echo "</div></div></body></html>";
?>
