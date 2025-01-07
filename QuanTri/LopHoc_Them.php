<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

// Kết nối CSDL
$conn = new mysqli($mayChu, $tenNguoiDung, $matKhauCSDL, $tenCSDL);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối CSDL thất bại: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $tenLopHoc = trim($_POST['tenLopHoc'] ?? '');
    $maLopHoc = trim($_POST['maLopHoc'] ?? '');
    $soLuongSinhVien = trim($_POST['soLuongSinhVien'] ?? '');
    $maCoVan = trim($_POST['maCoVan'] ?? '');

    // Kiểm tra dữ liệu nhập vào
    if (empty($tenLopHoc) || empty($maLopHoc) || empty($soLuongSinhVien) || empty($maCoVan)) {
        echo "<script>alert('Vui lòng điền đầy đủ thông tin');</script>";
    } elseif (!is_numeric($soLuongSinhVien)) {
        echo "<script>alert('Số lượng sinh viên phải là một số hợp lệ');</script>";
    } else {
        // Câu lệnh SQL để thêm lớp học
        $sql = "INSERT INTO lophoc (MaLop, TenLop, SoLuongSinhVien, MaCoVan) 
                VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Ràng buộc các tham số
            if (!$stmt->bind_param("ssis", $maLopHoc, $tenLopHoc, $soLuongSinhVien, $maCoVan)) {
                echo "<script>alert('Lỗi ràng buộc tham số: {$stmt->error}');</script>";
            } else {
                // Thực thi câu lệnh
                if ($stmt->execute()) {
                    echo "<script>
                        window.location.href = 'quanLyLopHoc.php';
                    </script>";
                    exit();
                } else {
                    echo "<script>alert('Lỗi khi thêm lớp học: {$stmt->error}');</script>";
                }
            }

            // Đóng câu lệnh
            $stmt->close();
        } else {
            echo "<script>alert('Lỗi chuẩn bị câu lệnh SQL: {$conn->error}');</script>";
        }
    }
}

// Đóng kết nối CSDL
$conn->close();
?>
