<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

try {
    $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Thêm sinh viên
    // Kiểm tra yêu cầu thêm sinh viên
    if (isset($_POST['maSinhVien']) && isset($_POST['HoTen'])&& isset($_POST['Ten']) && isset($_POST['email']) && isset($_POST['soDienThoai']) && isset($_POST['MaLop'])) {
        $maSinhVien = $_POST['maSinhVien'];
        $tenSinhVien = $_POST['HoTen'];
        $tenRieng = $_POST['Ten'];
        $email = $_POST['email'];
        $soDienThoai = $_POST['soDienThoai'];
        $lop = $_POST['MaLop'];
        $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
        // Bước 1: Tạo tài khoản người dùng với tài khoản là mã sinh viên, mật khẩu mặc định là 123456, phân quyền là 1 (Sinh viên)
        $sqlInsertUser = "INSERT INTO nguoidung (TaiKhoan, MatKhau, Email, MaPhanQuyen) VALUES (?, ?, ?, ?)";
        $stmtInsertUser = $pdo->prepare($sqlInsertUser);
        $stmtInsertUser->execute([$maSinhVien, $hashedPassword, $email, 1]);  // Phân quyền 1 cho sinh viên

        // Bước 2: Lấy mã người dùng vừa tạo
        $newMaNguoiDung = $pdo->lastInsertId();  // Lấy ID của người dùng vừa thêm

        // Bước 3: Thêm sinh viên vào bảng `sinhvien`, gán MaNguoiDung là mã người dùng mới
        $sqlInsertStudent = "INSERT INTO sinhvien (MaSinhVien, HoTen, Ten, SoDienThoai, Email, MaLop, MaNguoiDung) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtInsertStudent = $pdo->prepare($sqlInsertStudent);
        $stmtInsertStudent->execute([$maSinhVien, $tenSinhVien, $tenRieng, $soDienThoai, $email, $lop, $newMaNguoiDung]);

        // Sau khi thêm thành công, chuyển hướng về trang quản lý sinh viên
        header("Location: quanLySinhVien.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Lỗi kết nối CSDL: " . $e->getMessage();
}
?>
