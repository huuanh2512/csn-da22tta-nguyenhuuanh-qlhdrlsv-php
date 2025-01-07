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

// Truy vấn danh sách lớp học của cố vấn
$sql_lophoc = "SELECT * FROM lophoc WHERE MaCoVan = $maCoVan";
$result_lophoc = $conn->query($sql_lophoc);

// Xử lý chọn lớp học
$selectedClass = isset($_GET['MaLop']) ? $_GET['MaLop'] : '';

// Thiết lập lớp mặc định là lớp đầu tiên trong danh sách nếu không có lớp được chọn
if (!$selectedClass && $result_lophoc->num_rows > 0) {
    $row_first_class = $result_lophoc->fetch_assoc();
    $selectedClass = $row_first_class['MaLop'];
    // Reset lại con trỏ kết quả để truy vấn lớp học cho dropdown
    $result_lophoc->data_seek(0);
}

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page > 0 ? $page : 1; // Đảm bảo trang >= 1
$recordsPerPage = 20; // Số bản ghi trên mỗi trang
$offset = ($page - 1) * $recordsPerPage;

// Truy vấn sinh viên trong lớp đã chọn với phân trang
$sql_sinhvien = "SELECT * FROM sinhvien WHERE MaLop = '$selectedClass' ORDER BY MaSinhVien ASC LIMIT $recordsPerPage OFFSET $offset";
$result_sinhvien = $conn->query($sql_sinhvien);

// Đếm tổng số bản ghi
$sql_total = "SELECT COUNT(*) as total FROM sinhvien WHERE MaLop = '$selectedClass'";
$result_total = $conn->query($sql_total);
$totalRecords = $result_total->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách lớp học và sinh viên</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
                        <a href="index.php" class="active">
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
                        <a href="hoSoCoVan.php">
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
                <h1 class="text-center mb-4">Danh Sách Lớp Học</h1>
                <!-- Chọn lớp học -->
                <form action="index.php" method="GET">
                    <div class="mb-3">
                        <label for="MaLop" class="form-label">Lớp Học</label>
                        <select name="MaLop" id="MaLop" class="form-select">
                            <?php while ($row_lop = $result_lophoc->fetch_assoc()): ?>
                                <option value="<?php echo $row_lop['MaLop']; ?>" <?php echo ($selectedClass == $row_lop['MaLop']) ? 'selected' : ''; ?>>
                                    <?php echo $row_lop['TenLop']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Xem sinh viên</button>
                </form>

                <?php if ($selectedClass): ?>
                    <!-- Truy vấn sinh viên trong lớp đã chọn -->
                    <?php
                    $sql_sinhvien = "SELECT * FROM sinhvien WHERE MaLop = '$selectedClass' ORDER BY MaSinhVien ASC";
                    $result_sinhvien = $conn->query($sql_sinhvien);
                    ?>

                    <?php if ($result_sinhvien->num_rows > 0): ?>
                        <h3 class="mt-4">Danh Sách Sinh Viên - Lớp <?php echo $selectedClass; ?></h3>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mã Sinh Viên</th>
                                    <th>Tên Sinh Viên</th>
                                    <th>Email</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Hoạt động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_sinhvien = $result_sinhvien->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row_sinhvien['MaSinhVien']; ?></td>
                                        <td><?php echo $row_sinhvien['HoTen'] . " " . $row_sinhvien['Ten']; ?></td>
                                        <td><?php echo $row_sinhvien['Email']; ?></td>
                                        <td><?php echo $row_sinhvien['SoDienThoai']; ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#modal-<?php echo $row_sinhvien['MaSinhVien']; ?>">Xem hoạt động</button>

                                            <!-- Modal hiển thị hoạt động -->
                                            <div class="modal fade" id="modal-<?php echo $row_sinhvien['MaSinhVien']; ?>"
                                                tabindex="-1" aria-labelledby="modalLabel-<?php echo $row_sinhvien['MaSinhVien']; ?>"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-fullscreen">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalLabel-<?php echo $row_sinhvien['MaSinhVien']; ?>">
                                                                Hoạt Động - <?php echo $row_sinhvien['HoTen'] . " " . $row_sinhvien['Ten']; ?>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            // Truy vấn hoạt động của sinh viên
                                                            $maSinhVien = $row_sinhvien['MaSinhVien'];
                                                            $sql_hoatdong = "
                                                                SELECT h.TenHoatDong, h.DiaDiem, h.ThoiGianBatDau, h.ThoiGianKetThuc, h.LinkMinhChung, t.TenTieuChi
                                                                FROM hoatdong h
                                                                LEFT JOIN tieuchi t ON h.MaTieuChi = t.MaTieuChi
                                                                WHERE h.MaSinhVien = '$maSinhVien'";
                                                            $result_hoatdong = $conn->query($sql_hoatdong);
                                                            ?>

                                                            <?php if ($result_hoatdong->num_rows > 0): ?>
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Tên Hoạt Động</th>
                                                                            <th>Địa Điểm</th>
                                                                            <th>Thời Gian Bắt Đầu</th>
                                                                            <th>Thời Gian Kết Thúc</th>
                                                                            <th>Tiêu chí</th>
                                                                            <th>Link Minh Chứng</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php while ($row_hoatdong = $result_hoatdong->fetch_assoc()): ?>
                                                                            <tr>
                                                                                <td><?php echo $row_hoatdong['TenHoatDong']; ?></td>
                                                                                <td><?php echo $row_hoatdong['DiaDiem']; ?></td>
                                                                                <td><?php echo date('H:i:s d-m-Y', strtotime($row_hoatdong['ThoiGianBatDau'])); ?></td>
                                                                                <td><?php echo date('H:i:s d-m-Y', strtotime($row_hoatdong['ThoiGianKetThuc'])); ?></td>
                                                                                <td class="w-50"><?php echo $row_hoatdong['TenTieuChi'] ? $row_hoatdong['TenTieuChi'] : 'Không có'; ?></td>
                                                                                <td>
                                                                                    <?php if ($row_hoatdong['LinkMinhChung']): ?>
                                                                                        <a href="<?php echo $row_hoatdong['LinkMinhChung']; ?>" target="_blank">Xem Minh Chứng</a>
                                                                                    <?php else: ?>
                                                                                        Không có
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endwhile; ?>
                                                                    </tbody>
                                                                </table>
                                                            <?php else: ?>
                                                                <p>Không có hoạt động nào.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Kết thúc modal -->
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <!-- Nút quay lại -->
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?MaLop=<?= $selectedClass ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Các trang -->
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?MaLop=<?= $selectedClass ?>&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Nút tiếp theo -->
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?MaLop=<?= $selectedClass ?>&page=<?= $page + 1 ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Không có sinh viên trong lớp học này.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>

</html>