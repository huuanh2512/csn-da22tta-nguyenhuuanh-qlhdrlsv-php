<?php
session_start();
$maCoVan = $_SESSION['MaCoVan'];

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
            $sql_get_user = "SELECT nd.MatKhau, nd.MaNguoiDung 
                             FROM nguoidung nd 
                             INNER JOIN covanhoctap cv ON nd.MaNguoiDung = cv.MaNguoiDung 
                             WHERE cv.MaCoVan = ?";
            $stmt = $conn->prepare($sql_get_user);
            $stmt->bind_param("i", $maCoVan);
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
}

$sql_covan = "SELECT * FROM covanhoctap WHERE MaCoVan = ?";
$stmt_covan = $conn->prepare($sql_covan);
$stmt_covan->bind_param("i", $maCoVan);
$stmt_covan->execute();
$result_covan = $stmt_covan->get_result();

if ($result_covan->num_rows > 0) {
    $row_covan = $result_covan->fetch_assoc();

    // Truy vấn số lớp học mà cố vấn quản lý
    $sql_lophoc = "SELECT * FROM lophoc WHERE MaCoVan = ?";
    $stmt_lophoc = $conn->prepare($sql_lophoc);
    $stmt_lophoc->bind_param("i", $maCoVan);
    $stmt_lophoc->execute();
    $result_lophoc = $stmt_lophoc->get_result();
} else {
    $row_covan = null;
    $result_lophoc = null;
}

// Đóng kết nối
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cố Vấn</title>
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
                            <span>Quản lý lớp học</span>
                        </a>
                    </li>
                    <li>
                        <a href="quanLySinhVien.php">
                            <i class="fa-solid fa-list"></i>
                            <span>Hoạt động</span>
                        </a>
                    </li>
                    <li>
                        <a href="thongKeHoatDong.php">
                            <i class="fa-solid fa-chart-bar"></i>
                            <span>Thống kê</span>
                        </a>
                    </li>
                    <li>
                        <a href="hoSoCoVan.php" class="active">
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
            <div class="container mt-5">
                <h1 class="text-center mb-4">Thông tin cố vấn học tập</h1>
                <!-- Card hiển thị thông tin cố vấn -->
                <?php if ($row_covan): ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4>Cố vấn: <?php echo $row_covan['TenCoVan']; ?></h4>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editInfoModal">
                                <i class="fa-solid fa-pen"></i> Sửa
                            </button>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Tên cố vấn:</strong> <?php echo $row_covan['TenCoVan']; ?></li>
                                <li class="list-group-item"><strong>Email:</strong> <?php echo $row_covan['Email']; ?></li>
                                <li class="list-group-item"><strong>Số điện thoại:</strong> <?php echo $row_covan['SoDienThoai']; ?></li>
                                <li class="list-group-item"><strong>Đơn vị quản lý:</strong> <?php echo $row_covan['DonViQuanLy']; ?></li>
                                <li class="list-group-item"><strong>Số lượng lớp quản lý:</strong> <?php echo $result_lophoc->num_rows; ?></li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Không tìm thấy thông tin cố vấn.</p>
                <?php endif; ?>

            </div>
            <div class="container mt-5">
                <h2 class="text-center">Đổi mật khẩu</h2>
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
                    <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                </form>
            </div>
        </section>
    </div>
    <!-- Modal Sửa Thông Tin -->
    <div class="modal fade" id="editInfoModal" tabindex="-1" aria-labelledby="editInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInfoModalLabel">Sửa Thông Tin Cố Vấn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="updateInfo" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="TenCoVan" class="form-label">Tên cố vấn</label>
                            <input type="text" class="form-control" id="TenCoVan" name="TenCoVan" value="<?php echo $row_covan['TenCoVan']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="Email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="Email" name="Email" value="<?php echo $row_covan['Email']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="SoDienThoai" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="SoDienThoai" name="SoDienThoai" value="<?php echo $row_covan['SoDienThoai']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="DonViQuanLy" class="form-label">Đơn vị quản lý</label>
                            <input type="text" class="form-control" id="DonViQuanLy" name="DonViQuanLy" value="<?php echo $row_covan['DonViQuanLy']; ?>" required>
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
        document.addEventListener("DOMContentLoaded", function () {
            var notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'), {});
            notificationModal.show();
        });
    </script>
<?php endif; ?>
</body>
</html>