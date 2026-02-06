<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

/*
⚠️ 正式项目应从数据库读菜单
这里为了作业 & 简化，先用数组
*/
$menu = [
    1 => ['food_name'=>'Fried Rice',     'price'=>8,  'img'=>'images/fried_rice.jpg'],
    2 => ['food_name'=>'Chicken Chop',   'price'=>15, 'img'=>'images/chicken_chop.jpg'],
    3 => ['food_name'=>'Burger',         'price'=>10, 'img'=>'images/burger.jpg'],
    4 => ['food_name'=>'Ice Lemon Tea',  'price'=>4,  'img'=>'images/ice_lemon_tea.jpg'],
];

/* ========== 处理提交数据 ========== */
$submittedBooking = false;
$submittedOrder = false;
$bookingReceiptItems = [];
$orderReceiptItems = [];
$bookingTotal = 0;
$orderTotal = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['booking_submit'])) {
        $submittedBooking = true;
        $food = $_POST['food'] ?? [];
        $qty = $_POST['qty'] ?? [];
        foreach ($food as $id => $value) {
            $q = intval($qty[$id] ?? 1);
            $price = $menu[$id]['price'];
            $name = $menu[$id]['food_name'];
            $bookingReceiptItems[] = [
                'name'=>$name,
                'qty'=>$q,
                'price'=>$price,
                'total'=>$price*$q
            ];
            $bookingTotal += $price*$q;
        }
    } elseif (isset($_POST['order_submit'])) {
        $submittedOrder = true;
        $food = $_POST['food'] ?? [];
        $qty = $_POST['qty'] ?? [];
        foreach ($food as $id => $value) {
            $q = intval($qty[$id] ?? 1);
            $price = $menu[$id]['price'];
            $name = $menu[$id]['food_name'];
            $orderReceiptItems[] = [
                'name'=>$name,
                'qty'=>$q,
                'price'=>$price,
                'total'=>$price*$q
            ];
            $orderTotal += $price*$q;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lulu Restaurant - Booking & Order</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{ background:#f8f9fa; }
.menu-card{
    background:#fff;
    padding:10px;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,.1);
    margin-bottom:20px;
}
.menu-card img{
    width:100%;
    border-radius:10px;
}
.qty-input{ width:60px; }
.total-price{ font-weight:bold; font-size:1.2rem; }
.submit-btn{ background:#27ae60; color:#fff; }
.submit-btn:hover{ background:#219150; }
.admin-login{ position:absolute; top:20px; right:20px; }
.receipt{ background:#fff; padding:10px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,.1); margin-top:20px; }
.receipt ul{ padding-left:0; margin-bottom:0; }
.receipt li{ margin-bottom:5px; }
</style>
</head>

<body>

<div class="admin-login">
    <a href="admin_login.php" class="btn btn-sm btn-secondary">Admin Login</a>
</div>

<div class="container my-5">
<h2 class="text-center">🍽️ Lulu Restaurant</h2>
<p class="text-center fw-bold">营业时间：10:00 – 22:00（最后点餐 21:45）</p>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
<li class="nav-item">
<button class="nav-link active" data-bs-toggle="tab" data-bs-target="#booking">预定 Booking</button>
</li>
<li class="nav-item">
<button class="nav-link" data-bs-toggle="tab" data-bs-target="#order">现点 Order Now</button>
</li>
</ul>

<div class="tab-content">

<!-- ================= Booking ================= -->
<div class="tab-pane fade show active" id="booking">
<div class="card p-4">

<form method="post" action="index.php" id="bookingForm">

<input class="form-control mb-2" name="customer_name" placeholder="Your Name" required>
<input class="form-control mb-2" name="phone" placeholder="Phone Number" required>

<div class="row mb-2">
<div class="col">
<input type="date" class="form-control" name="booking_date" required>
</div>
<div class="col">
<input type="time" class="form-control" name="booking_time" required>
</div>
</div>

<input type="number" class="form-control mb-3" name="number_of_people" min="1" max="6" placeholder="Number of People" required>

<p id="tableInfo" class="fw-bold text-info">
选择日期和时间以查看剩余桌数
</p>

<div class="row">
<?php foreach($menu as $id=>$row): ?>
<div class="col-6 col-md-3">
<div class="menu-card text-center">

<img src="<?=$row['img']?>" alt="<?=$row['food_name']?>">
<h6><?=$row['food_name']?></h6>
<p class="text-warning">RM <?=number_format($row['price'],2)?></p>

<input type="checkbox" class="food-checkbox" name="food[<?=$id?>]">
Qty
<input type="number" class="qty-input" name="qty[<?=$id?>]" value="1" min="1">

</div>
</div>
<?php endforeach; ?>
</div>

<p class="total-price text-end mt-3">Total: RM 0.00</p>
<button class="btn submit-btn w-100 mt-2" name="booking_submit">✅ Confirm Booking</button>

</form>

<?php if($submittedBooking && !empty($bookingReceiptItems)): ?>
<div class="receipt">
    <h5>🧾 Your Booking Receipt</h5>
    <ul class="receipt-items">
        <?php foreach($bookingReceiptItems as $item): ?>
        <li><?= $item['name'] ?> × <?= $item['qty'] ?> = RM <?= number_format($item['total'],2) ?></li>
        <?php endforeach; ?>
    </ul>
    <p class="receipt-total fw-bold text-end">Total: RM <?= number_format($bookingTotal,2) ?></p>
</div>
<?php endif; ?>

</div>
</div>

<!-- ================= Order Now ================= -->
<div class="tab-pane fade" id="order">
<div class="card p-4">

<form method="post" action="index.php" id="orderForm">

<input type="number" class="form-control mb-3" name="number_of_people" min="1" max="6" placeholder="Number of People" required>

<div class="row">
<?php foreach($menu as $id=>$row): ?>
<div class="col-6 col-md-3">
<div class="menu-card text-center">

<img src="<?=$row['img']?>" alt="<?=$row['food_name']?>">
<h6><?=$row['food_name']?></h6>
<p class="text-warning">RM <?=number_format($row['price'],2)?></p>

<input type="checkbox" class="food-checkbox" name="food[<?=$id?>]">
Qty
<input type="number" class="qty-input" name="qty[<?=$id?>]" value="1" min="1">

</div>
</div>
<?php endforeach; ?>
</div>

<p class="total-price text-end mt-3">Total: RM 0.00</p>
<button class="btn submit-btn w-100 mt-2" name="order_submit">✅ Order Now</button>

</form>

<?php if($submittedOrder && !empty($orderReceiptItems)): ?>
<div class="receipt">
    <h5>🧾 Your Order Receipt</h5>
    <ul class="receipt-items">
        <?php foreach($orderReceiptItems as $item): ?>
        <li><?= $item['name'] ?> × <?= $item['qty'] ?> = RM <?= number_format($item['total'],2) ?></li>
        <?php endforeach; ?>
    </ul>
    <p class="receipt-total fw-bold text-end">Total: RM <?= number_format($orderTotal,2) ?></p>
</div>
<?php endif; ?>

</div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', ()=>{

/* ========= Total Calculation ========= */
function calcTotal(form){
    let total = 0;
    form.querySelectorAll('.food-checkbox').forEach(cb=>{
        if(cb.checked){
            const card = cb.closest('.menu-card');
            const qty = parseInt(card.querySelector('.qty-input').value) || 1;
            const priceText = card.querySelector('p').innerText.replace('RM','').trim();
            const price = parseFloat(priceText);
            total += price*qty;
        }
    });
    form.querySelector('.total-price').innerText = "Total: RM " + total.toFixed(2);
}

// 自动计算总价
document.querySelectorAll('form').forEach(f=>{
    f.addEventListener('change', ()=>calcTotal(f));
    f.addEventListener('input', ()=>calcTotal(f));
    calcTotal(f);
});

});
</script>

</body>
</html>
