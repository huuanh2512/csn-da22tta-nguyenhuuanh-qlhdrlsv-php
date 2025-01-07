<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

try {
    // Kết nối cơ sở dữ liệu
    $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Kiểm tra dữ liệu từ form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maSinhVien'])) {
        $maSinhVien = $_POST['maSinhVien'];
        $hoTen = $_POST['HoTen'] ?? null; // Họ
        $ten = $_POST['Ten'] ?? null; // Tên
        $email = $_POST['email'] ?? null;
        $soDienThoai = $_POST['soDienThoai'] ?? null;
        $lop = $_POST['MaLop'] ?? null;

        if ($hoTen && $ten && $email && $soDienThoai && $lop) {
            // Gộp họ và tên thành một chuỗi nếu cần
            trim($hoTen) ;
            trim($ten);

            // Sửa thông tin sinh viên trong bảng sinhvien
            $sqlUpdate = "UPDATE sinhvien 
                          SET HoTen = ?,Ten = ?, Email = ?, SoDienThoai = ?, MaLop = ? 
                          WHERE MaSinhVien = ?";
            $stmt = $pdo->prepare($sqlUpdate);
            $stmt->execute([$hoTen,$ten, $email, $soDienThoai, $lop, $maSinhVien]);

            // Chuyển hướng về trang quản lý sinh viên sau khi sửa thành công
            header("Location: quanLySinhVien.php?status=success");
            exit();
        } else {
            // Trả về thông báo lỗi nếu dữ liệu không đầy đủ
            header("Location: quanLySinhVien.php?status=error");
            exit();
        }
    }
} catch (PDOException $e) {
    echo "Lỗi kết nối CSDL: " . $e->getMessage();
}
?>
