CREATE DATABASE IF NOT EXISTS restaurant_system;
USE restaurant_system;

-- 订单表
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_type ENUM('walkin','booking') NOT NULL,
    table_no INT NOT NULL,
    number_of_people INT NOT NULL,
    customer_name VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    order_datetime DATETIME NOT NULL,
    booking_date DATE DEFAULT NULL,
    booking_time TIME DEFAULT NULL,
    total_price DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 订单菜品明细表
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    qty INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 食品菜单表
CREATE TABLE IF NOT EXISTS food_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    food_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 示例菜品
INSERT INTO food_menu (food_name, price) VALUES
('Fried Rice', 8.00),
('Chicken Chop', 15.00),
('Burger', 10.00),
('Ice Lemon Tea', 4.00);
