<?php
session_start(); // Bắt đầu session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maNguoiDung = $_POST['username']; // Mã sinh viên hoặc mã cố vấn
    $matKhau = $_POST['password']; // Mật khẩu

    // Thông tin kết nối CSDL
    $mayChu = 'localhost';
    $tenCSDL = 'doancsn';
    $tenNguoiDung = 'root';
    $matKhauCSDL = '';

    try {
        // Kết nối đến CSDL
        $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lấy thông tin người dùng
        $stmtUser = $pdo->prepare("
            SELECT nd.MaNguoiDung, nd.MatKhau, nd.MaPhanQuyen, sv.MaSinhVien, sv.HoTen, cv.MaCoVan, cv.TenCoVan
            FROM nguoidung nd
            LEFT JOIN sinhvien sv ON nd.MaNguoiDung = sv.MaNguoiDung
            LEFT JOIN covanhoctap cv ON nd.MaNguoiDung = cv.MaNguoiDung
            WHERE nd.TaiKhoan = :maNguoiDung
        ");
        $stmtUser->execute(['maNguoiDung' => $maNguoiDung]);
        $nguoiDung = $stmtUser->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra nếu người dùng tồn tại
        if ($nguoiDung && password_verify($matKhau, $nguoiDung['MatKhau'])) {
            // Xác định loại người dùng và lưu thông tin vào session
            if ($nguoiDung['MaPhanQuyen'] == 1) { // Sinh viên
                $_SESSION['MaSinhVien'] = $nguoiDung['MaSinhVien'];
                $_SESSION['HoTen'] = $nguoiDung['HoTen'];
                header("Location: /CSN/SinhVien/index.php");
                exit;
            } elseif ($nguoiDung['MaPhanQuyen'] == 2) { // Cố vấn
                $_SESSION['MaCoVan'] = $nguoiDung['MaCoVan'];
                $_SESSION['TenCoVan'] = $nguoiDung['TenCoVan'];
                header("Location: /CSN/CoVanHocTap/index.php");
                exit;
            } elseif ($nguoiDung['MaPhanQuyen'] == 3) { // Quản trị
                $_SESSION['MaNguoiDung'] = $nguoiDung['MaNguoiDung'];
                $_SESSION['Quyen'] = 'Quản trị';
                header("Location: /CSN/QuanTri/quanLyCoVan.php");
                exit;
            }
        } else {
            echo "<script>alert('Sai thông tin đăng nhập. Vui lòng kiểm tra lại!');</script>";
        }
    } catch (PDOException $e) {
        echo "Lỗi kết nối CSDL: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3 class="text-center">Đăng Nhập</h3>
        <form method="POST">
            <!-- Mã đăng nhập -->
            <div class="mb-3">
                <label for="username" class="form-label">Tài khoản</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Nhập mã số sinh viên hoặc mã cố vấn" required>
            </div>
            <!-- Mật khẩu -->
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Đăng Nhập</button>
        </form>
    </div>
</body>
</html>
