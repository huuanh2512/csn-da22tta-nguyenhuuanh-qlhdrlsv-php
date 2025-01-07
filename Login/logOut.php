<?php
$host = 'localhost';
$dbname = 'doancsn'; // Thay bằng tên cơ sở dữ liệu của bạn
$username = 'root';
$password = '';

session_start(); // Bắt đầu session

// Hủy session
session_unset(); // Xóa tất cả biến session
session_destroy(); // Hủy session hiện tại

// Chuyển hướng về trang đăng nhập
header("Location: http://localhost/CSN/Login/index.php");
exit;
?>
