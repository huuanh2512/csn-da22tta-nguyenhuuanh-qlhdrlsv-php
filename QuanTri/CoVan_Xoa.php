<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

try {
    // Kết nối CSDL
    $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Kiểm tra nếu yêu cầu xóa được gửi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $maCoVan = $_POST['id'];

        // Lấy mã người dùng từ bảng `covanhoctap`
        $sqlGetUserId = "SELECT MaNguoiDung FROM covanhoctap WHERE MaCoVan = ?";
        $stmtGetUserId = $pdo->prepare($sqlGetUserId);
        $stmtGetUserId->execute([$maCoVan]);
        $result = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $maNguoiDung = $result['MaNguoiDung'];

            // Xóa thông tin cố vấn từ bảng `covanhoctap`
            $sqlDeleteAdvisor = "DELETE FROM covanhoctap WHERE MaCoVan = ?";
            $stmtDeleteAdvisor = $pdo->prepare($sqlDeleteAdvisor);
            $stmtDeleteAdvisor->execute([$maCoVan]);

            // Xóa thông tin người dùng từ bảng `nguoidung`
            $sqlDeleteUser = "DELETE FROM nguoidung WHERE MaNguoiDung = ?";
            $stmtDeleteUser = $pdo->prepare($sqlDeleteUser);
            $stmtDeleteUser->execute([$maNguoiDung]);

            // Chuyển hướng về trang quản lý cố vấn với thông báo thành công
            header("Location: quanLyCoVan.php?status=success");
            exit();
        } else {
            // Trả về lỗi nếu không tìm thấy cố vấn
            header("Location: quanLyCoVan.php?status=not_found");
            exit();
        }
    }
} catch (PDOException $e) {
    // Hiển thị lỗi kết nối CSDL
    echo "Lỗi kết nối CSDL: " . $e->getMessage();
    exit();
}
?>
