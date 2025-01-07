<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

try {
    $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maSinhVien'])) {
        $maSinhVien = $_POST['maSinhVien'];

        // Lấy MaNguoiDung từ bảng sinhvien
        $sqlGetUserId = "SELECT MaNguoiDung FROM sinhvien WHERE MaSinhVien = ?";
        $stmtGetUserId = $pdo->prepare($sqlGetUserId);
        $stmtGetUserId->execute([$maSinhVien]);
        $result = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $maNguoiDung = $result['MaNguoiDung'];

            // Xóa sinh viên khỏi bảng sinhvien
            $sqlDeleteStudent = "DELETE FROM sinhvien WHERE MaSinhVien = ?";
            $stmtDeleteStudent = $pdo->prepare($sqlDeleteStudent);
            $stmtDeleteStudent->execute([$maSinhVien]);

            // Xóa người dùng khỏi bảng nguoidung
            $sqlDeleteUser = "DELETE FROM nguoidung WHERE MaNguoiDung = ?";
            $stmtDeleteUser = $pdo->prepare($sqlDeleteUser);
            $stmtDeleteUser->execute([$maNguoiDung]);

            // Thông báo thành công và chuyển hướng
            header('Location: quanLySinhVien.php');
        } else {
            echo "<script>alert('Sinh viên không tồn tại!'); window.location.href = 'quanLySinhVien.php';</script>";
        }
    } else {
        echo "<script>alert('Không có mã sinh viên được cung cấp!'); window.location.href = 'quanLySinhVien.php';</script>";
    }
} catch (PDOException $e) {
    echo "Lỗi kết nối CSDL: " . $e->getMessage();
}
?>
