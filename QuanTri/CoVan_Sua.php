<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

try {
    $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
        // Lấy dữ liệu từ form
        $maCoVan = $_POST['id'];  // Mã cố vấn để xác định bản ghi cần sửa
        $tenCovan = $_POST['tenCovan'];
        $email = $_POST['email'];
        $soDienThoai = $_POST['soDienThoai'];
        $donVi = $_POST['DonViQuanLy'];

        // Cập nhật thông tin cố vấn vào cơ sở dữ liệu
        $stmt = $pdo->prepare("UPDATE covanhoctap SET TenCovan = ?, Email = ?, SoDienThoai = ?, DonViQuanLy = ? WHERE MaCoVan = ?");
        $stmt->execute([$tenCovan, $email, $soDienThoai, $donVi, $maCoVan]);

        // Sau khi sửa, chuyển hướng về trang quản lý cố vấn
        header("Location: quanLyCoVan.php");
        exit(); // Dừng chương trình sau khi chuyển hướng
    }

} catch (PDOException $e) {
    echo "Lỗi kết nối CSDL: " . $e->getMessage();
}
?>
