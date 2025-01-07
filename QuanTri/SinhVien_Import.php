<?php
require '../vendor/autoload.php'; // Nạp PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

try {
    $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
        $file = $_FILES['excelFile']['tmp_name'];

        // Đọc file Excel
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Bỏ qua dòng tiêu đề (nếu có)
        unset($data[0]);

        // Lặp qua từng dòng để thêm sinh viên
        foreach ($data as $row) {
            if (!empty($row[1]) && !empty($row[2])) {
                $maSinhVien = $row[1];
                $hoTen = $row[2];
                $ten = $row[3];
                $maLop = $row[4];
                $soDienThoai = $row[6];
                $email = $row[7];

                // Kiểm tra `MaLop` có tồn tại trong bảng `lophoc`
                $stmtCheckClass = $pdo->prepare("SELECT COUNT(*) FROM lophoc WHERE MaLop = :MaLop");
                $stmtCheckClass->execute([':MaLop' => $maLop]);
                $classExists = $stmtCheckClass->fetchColumn();

                if (!$classExists) {
                    echo "Bỏ qua sinh viên $hoTen vì lớp học $maLop không tồn tại.<br>";
                    continue;
                }

                // Kiểm tra `MaSinhVien` đã tồn tại
                $stmtCheckStudent = $pdo->prepare("SELECT COUNT(*) FROM sinhvien WHERE MaSinhVien = :MaSinhVien");
                $stmtCheckStudent->execute([':MaSinhVien' => $maSinhVien]);
                $studentExists = $stmtCheckStudent->fetchColumn();

                if ($studentExists) {
                    echo "Sinh viên $hoTen đã tồn tại. Bỏ qua.<br>";
                    continue;
                }

                // Tạo tài khoản trong bảng `nguoidung`
                $sqlInsertUser = "INSERT INTO nguoidung (TaiKhoan, MatKhau, Email, MaPhanQuyen) 
                                  VALUES (:TaiKhoan, :MatKhau, :Email, :MaPhanQuyen)";
                $stmtInsertUser = $pdo->prepare($sqlInsertUser);
                $stmtInsertUser->execute([
                    ':TaiKhoan' => $maSinhVien,
                    ':MatKhau' => password_hash('123456', PASSWORD_DEFAULT), // Hash mật khẩu mặc định
                    ':Email' => $email,
                    ':MaPhanQuyen' => 1, // Phân quyền sinh viên
                ]);

                // Lấy `MaNguoiDung` vừa tạo
                $maNguoiDung = $pdo->lastInsertId();

                // Thêm sinh viên vào bảng `sinhvien`
                $sqlInsertStudent = "INSERT INTO sinhvien (MaSinhVien, HoTen, Ten, Email, SoDienThoai, MaLop, MaNguoiDung) 
                                     VALUES (:MaSinhVien, :HoTen, :Ten, :Email, :SoDienThoai, :MaLop, :MaNguoiDung)";
                $stmtInsertStudent = $pdo->prepare($sqlInsertStudent);
                $stmtInsertStudent->execute([
                    ':MaSinhVien' => $maSinhVien,
                    ':HoTen' => $hoTen,
                    ':Ten' => $ten,
                    ':Email' => $email,
                    ':SoDienThoai' => $soDienThoai,
                    ':MaLop' => $maLop,
                    ':MaNguoiDung' => $maNguoiDung,
                ]);
            }
        }

        // Chuyển hướng về trang quản lý sinh viên
        header('Location: quanLySinhVien.php');
    } else {
        echo "Không có file được tải lên!";
    }
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
