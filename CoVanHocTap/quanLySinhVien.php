<?php
// Kết nối cơ sở dữ liệu
$host = 'localhost';
$dbname = 'doancsn';
$username = 'root';
$password = '';

session_start();

// Lấy mã cố vấn từ session
$maCoVan = isset($_SESSION['MaCoVan']) ? $_SESSION['MaCoVan'] : null;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

// Kiểm tra nếu cố vấn chưa đăng nhập
if (!$maCoVan) {
    die("Bạn cần đăng nhập để xem thông tin này.");
}

// Lấy thông tin tìm kiếm và lọc
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filterClass = isset($_GET['class']) ? $_GET['class'] : '';
$filterCategory = isset($_GET['category']) ? $_GET['category'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// Phân trang
$recordsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page > 0 ? $page : 1; // Đảm bảo số trang >= 1
$offset = ($page - 1) * $recordsPerPage;

// Xây dựng truy vấn chính
$sql = "SELECT sinhvien.HoTen, sinhvien.Ten, sinhvien.Email, sinhvien.SoDienThoai, hoatdong.*, lophoc.MaLop, tieuchi.TenTieuChi, danhmuc.TenDanhMuc
        FROM hoatdong
        INNER JOIN sinhvien ON hoatdong.MaSinhVien = sinhvien.MaSinhVien
        INNER JOIN lophoc ON sinhvien.MaLop = lophoc.MaLop
        LEFT JOIN tieuchi ON hoatdong.MaTieuChi = tieuchi.MaTieuChi
        LEFT JOIN danhmuc ON tieuchi.MaDanhMuc = danhmuc.MaDanhMuc
        WHERE lophoc.MaCoVan = :maCoVan";

$whereClauses = [];
$params = ['maCoVan' => $maCoVan];

// Thêm điều kiện lọc
if ($filterClass) {
    $whereClauses[] = "lophoc.MaLop = :class";
    $params['class'] = $filterClass;
}
if ($filterCategory) {
    $whereClauses[] = "tieuchi.MaDanhMuc = :category";
    $params['category'] = $filterCategory;
}
if ($search) {
    $whereClauses[] = "(hoatdong.TenHoatDong LIKE :search OR sinhvien.MaSinhVien LIKE :search)";
    $params['search'] = "%$search%";
}
if ($startDate) {
    $whereClauses[] = "hoatdong.ThoiGianBatDau >= :startDate";
    $params['startDate'] = $startDate;
}
if ($endDate) {
    $whereClauses[] = "hoatdong.ThoiGianKetThuc <= :endDate";
    $params['endDate'] = $endDate;
}

// Thêm các điều kiện vào truy vấn
if (count($whereClauses) > 0) {
    $sql .= " AND " . implode(" AND ", $whereClauses);
}

// Phân trang
$sql .= " ORDER BY lophoc.MaLop, sinhvien.HoTen, sinhvien.Ten ASC LIMIT :limit OFFSET :offset";

// Chuẩn bị truy vấn
$stmt = $pdo->prepare($sql);

// Gắn giá trị LIMIT và OFFSET
$stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

// Gắn các tham số lọc
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}

$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Đếm tổng số bản ghi
$sql_count = "SELECT COUNT(*) FROM hoatdong
              INNER JOIN sinhvien ON hoatdong.MaSinhVien = sinhvien.MaSinhVien
              INNER JOIN lophoc ON sinhvien.MaLop = lophoc.MaLop
              WHERE lophoc.MaCoVan = :maCoVan";
if (count($whereClauses) > 0) {
    $sql_count .= " AND " . implode(" AND ", $whereClauses);
}

$stmt_count = $pdo->prepare($sql_count);
foreach ($params as $key => $value) {
    $stmt_count->bindValue(":$key", $value);
}
$stmt_count->execute();
$totalRecords = $stmt_count->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage);

// Lấy danh sách lớp để lọc
$sql_classes = "SELECT DISTINCT MaLop, TenLop FROM lophoc WHERE MaCoVan = :maCoVan ORDER BY MaLop";
$stmt_classes = $pdo->prepare($sql_classes);
$stmt_classes->execute(['maCoVan' => $maCoVan]);
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách danh mục để lọc
$sql_categories = "SELECT DISTINCT MaDanhMuc, TenDanhMuc FROM danhmuc ORDER BY MaDanhMuc ASC";
$stmt_categories = $pdo->query($sql_categories);
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sinh viên</title>
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
                        <a href="index.php">
                            <i class="fa-solid fa-house icon"></i>
                            <span>Quản lý lớp học</span>
                        </a>
                    </li>
                    <li>
                        <a href="quanLySinhVien.php" class="active">
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
                <h1 class="text-center mb-4">Danh sách hoạt động sinh viên</h1>
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="mb-4">
                            <div class="row g-3 align-items-end">
                                <!-- Tìm kiếm -->
                            <div class="col-md-5">
                                <label for="search" class="form-label">Tìm kiếm</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    placeholder="Tìm kiếm hoạt động hoặc mã sinh viên"
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>

                            <!-- Lọc theo lớp -->
                            <div class="col-md-3">
                                <label for="class" class="form-label">Lọc theo lớp</label>
                                <select id="class" class="form-select" name="class">
                                    <option value="">Tất cả lớp</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['MaLop']; ?>" <?php echo ($filterClass === $class['MaLop']) ? 'selected' : ''; ?>>
                                            <?php echo $class['TenLop']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Lọc theo danh mục -->
                            <div class="col-md-3">
                                <label for="category" class="form-label">Lọc theo danh mục</label>
                                <select id="category" class="form-select" name="category">
                                    <option value="">Tất cả danh mục</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['MaDanhMuc']; ?>" <?php echo ($filterCategory === $category['MaDanhMuc']) ? 'selected' : ''; ?>>
                                            <?php echo $category['TenDanhMuc']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Lọc theo ngày bắt đầu -->
                            <div class="col-md-5">
                                <label for="startDate" class="form-label">Từ ngày</label>
                                <input type="date" id="startDate" class="form-control" name="startDate"
                                    value="<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : ''; ?>">
                            </div>

                            <!-- Lọc theo ngày kết thúc -->
                            <div class="col-md-6">
                                <label for="endDate" class="form-label">Đến ngày</label>
                                <input type="date" id="endDate" class="form-control" name="endDate"
                                    value="<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : ''; ?>">
                            </div>

                            <!-- Nút tìm kiếm và xóa lọc -->
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                            </div>
                            <div class="col-md-2 ">
                                <a href="?" class="btn btn-secondary">Xóa lọc</a>
                            </div>
                            </div>
                        </form>
                    </div>
                </div>

                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>Mã sinh viên</th>
                            <th>Tên hoạt động</th>
                            <th>Địa điểm</th>
                            <th>Đơn vị tổ chức</th>
                            <th>Thời gian</th>
                            <th>Tiêu chí</th>
                            <th>Link minh chứng</th>
                            <th>Lớp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($activities) > 0): ?>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['MaSinhVien']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['TenHoatDong']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['DiaDiem']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['TenDonViToChuc']); ?></td>
                                    <td>
                                        <?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianBatDau'])) ?> -<br>
                                        <?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianKetThuc'])) ?>
                                    </td>
                                    <td style="width: 500px;"><?php echo $activity['TenTieuChi'] ? htmlspecialchars($activity['TenTieuChi']) : 'Không có tiêu chí'; ?></td>
                                    <td>
                                        <?php if ($activity['LinkMinhChung']): ?>
                                            <a href="<?php echo htmlspecialchars($activity['LinkMinhChung']); ?>" target="_blank">Xem minh chứng</a>
                                        <?php else: ?>
                                            Không có minh chứng
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['MaLop']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Không tìm thấy hoạt động nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query($_GET) ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($_GET) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query($_GET) ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
        </section>
    </div>
</body>

</html>