<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

try {
    $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['maCovan']) && isset($_POST['tenCovan']) && isset($_POST['email']) && isset($_POST['soDienThoai']) && isset($_POST['DonViQuanLy'])) {
        $maCovan = $_POST['maCovan'];
        $tenCovan = $_POST['tenCovan'];
        $email = $_POST['email'];
        $soDienThoai = $_POST['soDienThoai'];
        $donViQuanLy = $_POST['DonViQuanLy'];

        // Hash mật khẩu mặc định
        $hashedPassword = password_hash('123456', PASSWORD_DEFAULT); // Sử dụng PASSWORD_DEFAULT để đảm bảo tính an toàn

        // Bước 1: Tạo tài khoản người dùng với tài khoản là mã cố vấn, mật khẩu hash, phân quyền là 2 (Cố vấn)
        $sqlInsertUser = "INSERT INTO nguoidung (TaiKhoan, MatKhau, Email, MaPhanQuyen) VALUES (?, ?, ?, ?)";
        $stmtInsertUser = $pdo->prepare($sqlInsertUser);
        $stmtInsertUser->execute([$maCovan, $hashedPassword, $email, 2]);  // Phân quyền 2 cho cố vấn học tập

        // Bước 2: Lấy mã người dùng vừa tạo
        $newMaNguoiDung = $pdo->lastInsertId();  // Lấy ID của người dùng vừa thêm

        // Bước 3: Thêm cố vấn vào bảng `covanhoctap`, gán MaNguoiDung là mã người dùng mới
        $sqlInsertAdvisor = "INSERT INTO covanhoctap (MaCoVan, TenCoVan, SoDienThoai, Email, DonViQuanLy, MaNguoiDung) 
                             VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsertAdvisor = $pdo->prepare($sqlInsertAdvisor);
        $stmtInsertAdvisor->execute([$maCovan, $tenCovan, $soDienThoai, $email, $donViQuanLy, $newMaNguoiDung]);

        // Sau khi thêm thành công, chuyển hướng về trang quản lý cố vấn
        header("Location: quanLyCoVan.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Lỗi kết nối CSDL: " . $e->getMessage();
}
?>
