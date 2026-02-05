<?php
include 'db.php';

$booking_date = $_POST['booking_date'];
$booking_time = $_POST['booking_time'];
$number_of_people = $_POST['number_of_people'];

// 限制每桌人数 1-6
if($number_of_people < 1 || $number_of_people > 6){
    echo "<h2>❌ 每桌只能预订 1-6 人</h2>";
    echo "<p>您输入的人数：$number_of_people</p>";
    echo "<a href='index.php' class='btn btn-warning'>Back to Menu</a>";
    exit;
}

$customer_name = "Guest";
$phone = "0000000000";
$total_tables = 50;
$order_time_db = date('Y-m-d H:i:s');
$order_time_display = date('H:i');

// 统计当前时间段已订桌数
$result = mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM bookings 
    WHERE booking_date='$booking_date' 
      AND booking_time='$booking_time'
");
$row = mysqli_fetch_assoc($result);
$booked_tables = $row['count'];

// 检查是否超出总桌数
if($booked_tables >= $total_tables){
    echo "<h2>❌ 当前时间段已满桌</h2>";
    echo "<a href='index.php' class='btn btn-warning'>Back to Menu</a>";
    exit;
}

// 当前桌号
$table_number = $booked_tables + 1;

// 插入 bookings
$stmt = mysqli_prepare($conn, "INSERT INTO bookings (customer_name, phone, booking_date, booking_time, number_of_people) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'ssssi', $customer_name, $phone, $booking_date, $booking_time, $number_of_people);
mysqli_stmt_execute($stmt);
$booking_id = mysqli_insert_id($conn);

// 插入 food_orders
mysqli_query($conn, "INSERT INTO food_orders (booking_id, order_time) VALUES ($booking_id, '$order_time_db')");
$order_id = mysqli_insert_id($conn);

// 插入 food_order_items 并计算总价
$total_price = 0;
$order_summary = [];
if(isset($_POST['food'])){
    foreach($_POST['food'] as $food_id => $value){
        $qty = $_POST['qty'][$food_id];
        $res = mysqli_query($conn, "SELECT food_name, price FROM food_menu WHERE id=$food_id");
        $row = mysqli_fetch_assoc($res);
        $name = $row['food_name'];
        $price = $row['price'];
        $subtotal = $price * $qty;
        $total_price += $subtotal;

        mysqli_query($conn, "INSERT INTO food_order_items (order_id, food_id, quantity) VALUES ($order_id, $food_id, $qty)");

        $order_summary[] = [
            'name' => $name,
            'qty' => $qty,
            'price' => $price,
            'subtotal' => $subtotal
        ];
    }
}

// 显示订单明细
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
foreach($order_summary as $item){
    echo "<tr>";
    echo "<td>{$item['name']}</td>";
    echo "<td>{$item['qty']}</td>";
    echo "<td>".number_format($item['price'],2)."</td>";
    echo "<td>".number_format($item['subtotal'],2)."</td>";
    echo "</tr>";
}
echo "<tr><td colspan='3' class='text-end'><strong>Total Price:</strong></td><td><strong>RM ".number_format($total_price,2)."</strong></td></tr>";
echo "</tbody></table>";
echo "<a href='index.php' class='btn btn-primary'>Back to Menu</a>";
echo "</div></div></body></html>";
?>
