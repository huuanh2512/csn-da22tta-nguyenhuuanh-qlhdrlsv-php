<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';
$conn = new mysqli($mayChu, $tenNguoiDung, $matKhauCSDL, $tenCSDL);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $maLopHoc = $_POST['id'];            // Mã lớp học (VARCHAR)
    $tenLopHoc = $_POST['tenLopHoc'];    // Tên lớp
    $soLuongSinhVien = $_POST['soLuongSinhVien'];  // Số lượng sinh viên
    $maCoVan = $_POST['maCoVan'];        // Mã cố vấn

    // Câu lệnh SQL để cập nhật thông tin lớp học
    $sqlUpdate = "UPDATE lophoc SET TenLop = ?, SoLuongSinhVien = ?, MaCoVan = ? WHERE MaLop = ?";
    
    // Sử dụng bind_param() với kiểu 's' cho string và 'i' cho integer
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("siss", $tenLopHoc, $soLuongSinhVien, $maCoVan, $maLopHoc);
    $stmt->execute();

    // Sau khi sửa, chuyển hướng về trang quản lý lớp học
    header("Location: quanLyLopHoc.php");
    exit();
}
?>
