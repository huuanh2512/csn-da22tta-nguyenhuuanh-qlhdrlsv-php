<?php
session_start();

// Kết nối cơ sở dữ liệu
$host = 'localhost';
$dbname = 'doancsn';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

// Lọc theo lớp học
$filterClass = isset($_GET['class']) ? $_GET['class'] : '';

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 20; // Số bản ghi trên mỗi trang
$offset = ($page - 1) * $recordsPerPage;

// Lấy danh sách lớp học
$sql_classes = "SELECT DISTINCT MaLop, TenLop FROM lophoc ORDER BY MaLop";
$stmt_classes = $pdo->prepare($sql_classes);
$stmt_classes->execute();
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

// Lấy thống kê hoạt động với phân trang
$sql = "SELECT 
            lophoc.MaLop, lophoc.TenLop, sinhvien.MaSinhVien, sinhvien.HoTen, sinhvien.Ten, sinhvien.Email, sinhvien.SoDienThoai,
            COUNT(hoatdong.MaHoatDong) AS SoLuongHoatDong,
            SUM(COALESCE(tieuchi.SoDiem, 0)) AS TongDiem
        FROM sinhvien
        LEFT JOIN hoatdong ON sinhvien.MaSinhVien = hoatdong.MaSinhVien
        LEFT JOIN tieuchi ON hoatdong.MaTieuChi = tieuchi.MaTieuChi
        INNER JOIN lophoc ON sinhvien.MaLop = lophoc.MaLop
        WHERE 1=1";

$params = [];

if (!empty($filterClass)) {
    $sql .= " AND lophoc.MaLop = :class";
    $params['class'] = $filterClass;
}

$sql .= " GROUP BY sinhvien.MaSinhVien 
          ORDER BY lophoc.MaLop, sinhvien.MaSinhVien
          LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Đếm tổng số bản ghi
$sql_count = "SELECT COUNT(DISTINCT sinhvien.MaSinhVien) AS totalRecords
              FROM sinhvien
              INNER JOIN lophoc ON sinhvien.MaLop = lophoc.MaLop
              WHERE 1=1";

if (!empty($filterClass)) {
    $sql_count .= " AND lophoc.MaLop = :class";
}

$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$totalRecords = $stmt_count->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Hoạt Động Sinh Viên (Quản Trị)</title>
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
                        <a href="quanLyCoVan.php">
                            <i class="fa-solid fa-list"></i>
                            <span>Quản lý cố vấn</span>
                        </a>
                    </li>
                    <li>
                        <a href="quanLySinhVien.php">
                            <i class="fa-solid fa-list-check"></i>
                            <span>Quản lý sinh viên</span>
                        </a>
                    </li>
                    <li>
                        <a href="quanLyLopHoc.php">
                            <i class="fa-solid fa-list-check"></i>
                            <span>Quản lý lớp học</span>
                        </a>
                    </li>
                    <li>
                        <a href="quanLyHoatDong.php">
                            <i class="fa-solid fa-list-check"></i>
                            <span>Quản lý hoạt động</span>
                        </a>
                    </li>
                    <li>
                        <a href="thongKeHoatDong.php" class="active">
                            <i class="fa-solid fa-chart-bar"></i>
                            <span>Thống kê hoạt động</span>
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
            <h1 class="text-center mb-4">Thống kê hoạt động sinh viên</h1>
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <select class="form-select" name="class">
                        <option value="">Tất cả lớp</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['MaLop'] ?>" <?= $filterClass == $class['MaLop'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['MaLop']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </div>
            </form>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Lớp</th>
                        <th>Mã số sinh viên</th>
                        <th>Tên sinh viên</th>
                        <th>Số điện thoại</th>
                        <th>Tổng điểm</th>
                        <th>Xem hoạt động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($statistics)): ?>
                        <?php foreach ($statistics as $stat): ?>
                            <tr>
                                <td><?= htmlspecialchars($stat['MaLop']) ?></td>
                                <td><?= htmlspecialchars($stat['MaSinhVien']) ?></td>
                                <td><?= htmlspecialchars($stat['HoTen']." ". $stat['Ten']) ?></td>
                                <td><?= htmlspecialchars($stat['SoDienThoai']) ?></td>
                                <td><?= htmlspecialchars($stat['TongDiem']) ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#modal-<?= $stat['MaSinhVien'] ?>">Xem hoạt động</button>

                                    <!-- Modal hiển thị hoạt động -->
                                    <div class="modal fade" id="modal-<?= $stat['MaSinhVien'] ?>" tabindex="-1"
                                        aria-labelledby="modalLabel-<?= $stat['MaSinhVien'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-fullscreen">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalLabel-<?= $stat['MaSinhVien'] ?>">
                                                        Hoạt động của <?= htmlspecialchars($stat['HoTen'] ." " . $stat['Ten']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    // Truy vấn hoạt động của sinh viên
                                                    $sql_hoatdong = "
                                                        SELECT h.TenHoatDong, h.DiaDiem, h.ThoiGianBatDau, h.ThoiGianKetThuc, t.TenTieuChi, h.LinkMinhChung
                                                        FROM hoatdong h
                                                        LEFT JOIN tieuchi t ON h.MaTieuChi = t.MaTieuChi
                                                        WHERE h.MaSinhVien = :maSinhVien
                                                    ";
                                                    $stmt_hoatdong = $pdo->prepare($sql_hoatdong);
                                                    $stmt_hoatdong->execute(['maSinhVien' => $stat['MaSinhVien']]);
                                                    $activities = $stmt_hoatdong->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>

                                                    <?php if (!empty($activities)): ?>
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Tên hoạt động</th>
                                                                    <th>Địa điểm</th>
                                                                    <th>Thời gian</th>
                                                                    <th>Tiêu chí</th>
                                                                    <th>Link minh chứng</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($activities as $activity): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($activity['TenHoatDong']) ?></td>
                                                                        <td><?= htmlspecialchars($activity['DiaDiem']) ?></td>
                                                                        <td><?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianBatDau'])) ?> - <?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianKetThuc'])) ?></td>
                                                                        <td><?= htmlspecialchars($activity['TenTieuChi']) ?></td>
                                                                        <td>
                                                                            <?php if ($activity['LinkMinhChung']): ?>
                                                                                <a href="<?= htmlspecialchars($activity['LinkMinhChung']) ?>" target="_blank">Xem</a>
                                                                            <?php else: ?>
                                                                                Không có
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
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
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Không có dữ liệu thống kê.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&class=<?= htmlspecialchars($filterClass) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </section>
    </div>
</body>

</html>