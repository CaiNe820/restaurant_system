<?php

include 'db.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

$menu = [
    ['id'=>1, 'food_name'=>'Fried Rice', 'price'=>8, 'img'=>'images/fried_rice.jpg'],
    ['id'=>2, 'food_name'=>'Chicken Chop', 'price'=>15, 'img'=>'images/chicken_chop.jpg'],
    ['id'=>3, 'food_name'=>'Burger', 'price'=>10, 'img'=>'images/burger.jpg'],
    ['id'=>4, 'food_name'=>'Ice Lemon Tea', 'price'=>4, 'img'=>'images/ice_lemon_tea.jpg'],
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Lulu Restaurant - Booking & Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .menu-card img { width: 100%; height: auto; border-radius: 10px; }
        .menu-card { margin-bottom: 20px; padding: 10px; background: #fff; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .submit-btn { background-color: #27ae60; color: white; }
        .submit-btn:hover { background-color: #219150; }
        .qty-input { width: 60px; display: inline-block; }
        .time-info { font-weight: bold; margin-bottom: 15px; }
        .total-price { font-size: 1.2rem; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container my-5">
    <h2 class="text-center mb-4">🍽️ Lulu Restaurant</h2>
    <p class="text-center time-info">营业时间: 10:00 – 22:00</p>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs mb-3" id="tabMenu" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking" type="button">预定 Booking</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="order-tab" data-bs-toggle="tab" data-bs-target="#order" type="button">现点 Order Now</button>
      </li>
    </ul>

    <div class="tab-content">
        <!-- Booking Tab -->
        <div class="tab-pane fade show active" id="booking">
            <div class="card p-4 shadow-sm">
                <form action="process_booking.php" method="post" id="bookingForm">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="customer_name" placeholder="Your Name" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="phone" placeholder="Phone Number" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <input type="date" class="form-control" name="booking_date" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="time" class="form-control" name="booking_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="number" class="form-control" name="number_of_people" placeholder="Number of People (1-6)" required min="1" max="6">
                    </div>

                    <!-- 剩余桌数 & 下一个桌号显示 -->
                    <div class="mb-3">
                        <p id="tableInfo" class="text-info fw-bold">选择日期和时间以查看剩余桌数</p>
                    </div>

                    <p class="time-info">点单时间: <?php echo date('H:i'); ?></p>

                    <h4 class="text-center mt-3 mb-3">📖 Food Menu</h4>
                    <div class="row" id="bookingMenu">
                        <?php
                        foreach($menu as $row){
                            echo '<div class="col-6 col-md-3">';
                            echo '<div class="menu-card text-center" data-price="'.$row['price'].'">';
                            echo '<img src="'.$row['img'].'" alt="'.$row['food_name'].'">';
                            echo '<h5>'.$row['food_name'].'</h5>';
                            echo '<p class="text-warning fw-bold">RM '.number_format($row['price'],2).'</p>';
                            echo '<div class="form-check">';
                            echo '<input class="form-check-input food-checkbox" type="checkbox" name="food['.$row['id'].']" id="booking_food'.$row['id'].'" data-price="'.$row['price'].'">';
                            echo '<label class="form-check-label" for="booking_food'.$row['id'].'">Order</label>';
                            echo '</div>';
                            echo 'Qty: <input type="number" class="form-control qty-input" name="qty['.$row['id'].']" value="1" min="1">';
                            echo '</div></div>';
                        }
                        ?>
                    </div>

                    <p class="total-price text-end">Total: RM 0.00</p>
                    <button type="submit" class="btn submit-btn mt-3 w-100">✅ Confirm Booking</button>
                </form>
            </div>
        </div>

        <!-- Order Now Tab -->
        <div class="tab-pane fade" id="order">
            <div class="card p-4 shadow-sm">
                <form action="process_order.php" method="post" id="orderForm">
                    <div class="mb-3">
                        <input type="number" class="form-control" name="number_of_people" placeholder="Number of People (1-6)" required min="1" max="6">
                    </div>

                    <p class="time-info">点单时间: <?php echo date('H:i'); ?></p>

                    <h4 class="text-center mt-3 mb-3">📖 Food Menu</h4>
                    <div class="row" id="orderMenu">
                        <?php
                        foreach($menu as $row){
                            echo '<div class="col-6 col-md-3">';
                            echo '<div class="menu-card text-center" data-price="'.$row['price'].'">';
                            echo '<img src="'.$row['img'].'" alt="'.$row['food_name'].'">';
                            echo '<h5>'.$row['food_name'].'</h5>';
                            echo '<p class="text-warning fw-bold">RM '.number_format($row['price'],2).'</p>';
                            echo '<div class="form-check">';
                            echo '<input class="form-check-input food-checkbox" type="checkbox" name="food['.$row['id'].']" id="order_food'.$row['id'].'" data-price="'.$row['price'].'">';
                            echo '<label class="form-check-label" for="order_food'.$row['id'].'">Order</label>';
                            echo '</div>';
                            echo 'Qty: <input type="number" class="form-control qty-input" name="qty['.$row['id'].']" value="1" min="1">';
                            echo '</div></div>';
                        }
                        ?>
                    </div>

                    <p class="total-price text-end">Total: RM 0.00</p>
                    <button type="submit" class="btn submit-btn mt-3 w-100">✅ Order Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function calculateTotal(form){
    const checkboxes = form.querySelectorAll('.food-checkbox');
    const qtyInputs = form.querySelectorAll('.qty-input');
    const totalPriceEl = form.querySelector('.total-price');
    let total = 0;
    checkboxes.forEach(cb=>{
        const id = cb.id.replace(/\D/g,'');
        const qty = form.querySelector(`input[name="qty[${id}]"]`).value;
        if(cb.checked) total += parseFloat(cb.dataset.price)*parseInt(qty);
    });
    totalPriceEl.textContent = 'Total: RM '+total.toFixed(2);
}

document.querySelectorAll('form').forEach(f=>{
    f.addEventListener('change', ()=>calculateTotal(f));
    calculateTotal(f);
});

// Ajax 更新剩余桌数 & 下一个桌号
function updateTableInfo(){
    const date = document.querySelector('input[name="booking_date"]').value;
    const time = document.querySelector('input[name="booking_time"]').value;
    const tableInfoEl = document.getElementById('tableInfo');

    if(date && time){
        fetch(`get_table_info.php?booking_date=${date}&booking_time=${time}`)
        .then(res => res.json())
        .then(data => {
            if(data.remaining_tables <= 0){
                tableInfoEl.textContent = "❌ 当前时间段已满桌";
                tableInfoEl.classList.remove('text-info');
                tableInfoEl.classList.add('text-danger');
            } else {
                tableInfoEl.textContent = `✅ 下一个桌号: ${data.next_table} | 剩余桌数: ${data.remaining_tables}`;
                tableInfoEl.classList.remove('text-danger');
                tableInfoEl.classList.add('text-info');
            }
        });
    }
}

document.querySelector('input[name="booking_date"]').addEventListener('change', updateTableInfo);
document.querySelector('input[name="booking_time"]').addEventListener('change', updateTableInfo);
</script>
</body>
</html>
