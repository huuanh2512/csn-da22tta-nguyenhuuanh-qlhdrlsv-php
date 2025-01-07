<?php
session_start();
$maSinhVien = $_SESSION['MaSinhVien'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "doancsn";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Biến lưu thông báo
$modalMessage = null;
$modalType = null;

// Xử lý khi form đổi mật khẩu được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['changePassword'])) {
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        // Kiểm tra mật khẩu mới và xác nhận mật khẩu có khớp
        if ($newPassword !== $confirmPassword) {
            $modalMessage = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
            $modalType = "error";
        } else {
            // Lấy thông tin người dùng
            $sql_get_user = "
                SELECT nd.MatKhau, nd.MaNguoiDung 
                FROM nguoidung nd 
                INNER JOIN sinhvien sv ON nd.MaNguoiDung = sv.MaNguoiDung 
                WHERE sv.MaSinhVien = ?";
            $stmt = $conn->prepare($sql_get_user);
            $stmt->bind_param("i", $maSinhVien);
            $stmt->execute();
            $result_user = $stmt->get_result();

            if ($result_user->num_rows > 0) {
                $row_user = $result_user->fetch_assoc();

                // Kiểm tra mật khẩu hiện tại
                if (!password_verify($currentPassword, $row_user['MatKhau'])) {
                    $modalMessage = "Mật khẩu hiện tại không đúng.";
                    $modalType = "error";
                } else {
                    // Hash mật khẩu mới
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    // Cập nhật mật khẩu mới
                    $sql_update_password = "UPDATE nguoidung SET MatKhau = ? WHERE MaNguoiDung = ?";
                    $stmt_update = $conn->prepare($sql_update_password);
                    $stmt_update->bind_param("si", $hashedPassword, $row_user['MaNguoiDung']);
                    if ($stmt_update->execute()) {
                        $modalMessage = "Đổi mật khẩu thành công!";
                        $modalType = "success";
                    } else {
                        $modalMessage = "Lỗi khi đổi mật khẩu: " . $conn->error;
                        $modalType = "error";
                    }
                }
            } else {
                $modalMessage = "Người dùng không tồn tại.";
                $modalType = "error";
            }
        }
    }

    if (isset($_POST['updateInfo'])) {
        $hoTen = $_POST['HoTen'];
        $ten = $_POST['Ten'];
        $ngaySinh = $_POST['NgaySinh'];
        $tenDoanKhoa = $_POST['TenDoanKhoa'];
        $trangThaiSinhVien = $_POST['TrangThaiSinhVien'];
        $nganhHoc = $_POST['NganhHoc'];
        $email = $_POST['Email'];
        $soDienThoai = $_POST['SoDienThoai'];
        $sql_update_info = "UPDATE sinhvien SET HoTen = ?, Ten = ?, NgaySinh = ?, TenDoanKhoa = ?, TrangThaiSinhVien = ?, NganhHoc = ?, Email = ?, SoDienThoai = ? WHERE MaSinhVien = ?";
        $stmt_update_info = $conn->prepare($sql_update_info);
        $stmt_update_info->bind_param("ssssssssi", $hoTen, $ten, $ngaySinh, $tenDoanKhoa, $trangThaiSinhVien, $nganhHoc, $email, $soDienThoai, $maSinhVien);

        if ($stmt_update_info->execute()) {
            $modalMessage = "Cập nhật thông tin thành công!";
            $modalType = "success";
        } else {
            $modalMessage = "Lỗi khi cập nhật thông tin: " . $conn->error;
            $modalType = "error";
        }
    }
}

// Truy vấn thông tin sinh viên
$sql_sinhvien = "SELECT * FROM sinhvien WHERE MaSinhVien = ?";
$stmt_sinhvien = $conn->prepare($sql_sinhvien);
$stmt_sinhvien->bind_param("i", $maSinhVien);
$stmt_sinhvien->execute();
$result_sinhvien = $stmt_sinhvien->get_result();

if ($result_sinhvien->num_rows > 0) {
    $row_sinhvien = $result_sinhvien->fetch_assoc();
} else {
    $row_sinhvien = null;
}

// Đóng kết nối
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Sinh Viên</title>
    <link rel="stylesheet" href="style.css">
    <script src="javascript.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container-header">
        <header>
            <div class="logo">
                <h2>TVU</h2>
            </div>
            <nav>
                <ul>
                    <li>
                        <a href="index.php">
                            <i class="fa-solid fa-house icon"></i>
                            <span>Quản lý hoạt động</span>
                        </a>
                    </li>
                    <li>
                        <a href="thongKeHoatDong.php">
                            <i class="fa-solid fa-chart-bar"></i>
                            <span>Thống kê hoạt động</span>
                        </a>
                    </li>
                    <li>
                        <a href="hoSoCaNhan.php" class="active">
                            <i class="fa-solid fa-user"></i>
                            <span>Hồ sơ cá nhân</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </header>
        <div class="btn-log">
            <div class="logBtn">
                <button>
                    <a href="http://localhost/CSN/Login/logOut.php" class="nav-link">Đăng xuất</a>
                </button>
            </div>
        </div>
    </div>
    <div class="container-content">
        <section>
            <h1 class="text-center mb-4">Hồ Sơ Sinh Viên</h1>

            <?php if ($row_sinhvien): ?>
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4>Sinh viên: <?php echo $row_sinhvien['HoTen']; ?></h4>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editInfoModal">
                            <i class="fa-solid fa-pen"></i> Sửa
                        </button>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Mã sinh viên:</strong> <?php echo $row_sinhvien['MaSinhVien']; ?></li>
                            <li class="list-group-item"><strong>Họ tên:</strong> <?php echo $row_sinhvien['HoTen'] . " " . $row_sinhvien['Ten']; ?></li>
                            <li class="list-group-item"><strong>Ngày sinh:</strong> <?php echo date('d-m-Y', strtotime($row_sinhvien['NgaySinh'])); ?></li>
                            <li class="list-group-item"><strong>Email:</strong> <?php echo $row_sinhvien['Email']; ?></li>
                            <li class="list-group-item"><strong>Số điện thoại:</strong> <?php echo $row_sinhvien['SoDienThoai']; ?></li>
                            <li class="list-group-item"><strong>Mã lớp:</strong> <?php echo $row_sinhvien['MaLop']; ?></li>
                            <li class="list-group-item"><strong>Đoàn khoa:</strong> <?php echo $row_sinhvien['TenDoanKhoa']; ?></li>
                            <li class="list-group-item"><strong>Trạng thái:</strong> <?php echo $row_sinhvien['TrangThaiSinhVien']; ?></li>
                            <li class="list-group-item"><strong>Ngành học:</strong> <?php echo $row_sinhvien['NganhHoc']; ?></li>
                        </ul>
                    </div>

                </div>
            <?php else: ?>
                <p>Không tìm thấy thông tin sinh viên.</p>
            <?php endif; ?>

            <div class="mt-5">
                <h2 class="text-center">Đổi Mật Khẩu</h2>
                <form method="POST">
                    <input type="hidden" name="changePassword" value="1">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Nhập lại mật khẩu mới</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Đổi Mật Khẩu</button>
                </form>
            </div>
    </div>
    </section>
    <!-- Modal Sửa Thông Tin -->
    <div class="modal fade" id="editInfoModal" tabindex="-1" aria-labelledby="editInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInfoModalLabel">Sửa Thông Tin Sinh Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="updateInfo" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="HoTen" class="form-label">Họ</label>
                            <input type="text" class="form-control" id="HoTen" name="HoTen" value="<?php echo $row_sinhvien['HoTen']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="Ten" class="form-label">Tên</label>
                            <input type="text" class="form-control" id="Ten" name="Ten" value="<?php echo $row_sinhvien['Ten']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="NgaySinh" class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" id="NgaySinh" name="NgaySinh" value="<?php echo $row_sinhvien['NgaySinh']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="Email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="Email" name="Email" value="<?php echo $row_sinhvien['Email']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="SoDienThoai" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="SoDienThoai" name="SoDienThoai" value="<?php echo $row_sinhvien['SoDienThoai']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="TenDoanKhoa" class="form-label">Đoàn khoa</label>
                            <input type="text" class="form-control" id="TenDoanKhoa" name="TenDoanKhoa" value="<?php echo $row_sinhvien['TenDoanKhoa']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="TrangThaiSinhVien" class="form-label">Trạng thái</label>
                            <input type="text" class="form-control" id="TrangThaiSinhVien" name="TrangThaiSinhVien" value="<?php echo $row_sinhvien['TrangThaiSinhVien']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="NganhHoc" class="form-label">Ngành học</label>
                            <input type="text" class="form-control" id="NganhHoc" name="NganhHoc" value="<?php echo $row_sinhvien['NganhHoc']; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Thông báo -->
    <?php if ($modalMessage): ?>
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificationModalLabel">
                            <?php echo ($modalType === 'success') ? 'Thành công!' : 'Lỗi!'; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo $modalMessage; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'), {});
                notificationModal.show();
            });
        </script>
    <?php endif; ?>
</body>

</html>