<?php
// Kết nối cơ sở dữ liệu
$host = 'localhost';
$dbname = 'doancsn';
$username = 'root';
$password = '';

session_start();

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

// Kiểm tra nếu sinh viên chưa đăng nhập
$maSinhVien = isset($_SESSION['MaSinhVien']) ? $_SESSION['MaSinhVien'] : null;
if (!$maSinhVien) {
    die("Bạn cần đăng nhập để thực hiện chức năng này.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $maSinhVien = $_SESSION['MaSinhVien'];

    try {
        if ($action === 'add') {
            // Lấy dữ liệu từ form
            $tenHoatDong = $_POST['TenHoatDong'] ?? '';
            $diaDiem = $_POST['DiaDiem'] ?? '';
            $donViToChuc = $_POST['TenDonViToChuc'] ?? ''; // Đơn vị tổ chức
            $thoiGianBatDau = $_POST['ThoiGianBatDau'] ?? '';
            $thoiGianKetThuc = $_POST['ThoiGianKetThuc'] ?? '';
            $linkMinhChung = $_POST['LinkMinhChung'] ?? '';
            $maTieuChi = $_POST['MaTieuChi'] ?? null;

            // Kiểm tra dữ liệu hợp lệ
            if (empty($tenHoatDong) || empty($diaDiem) || empty($thoiGianBatDau) || empty($thoiGianKetThuc) || !$maTieuChi || empty($donViToChuc)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin hoạt động.");
            }

            // Thêm hoạt động
            $sql = "INSERT INTO hoatdong (MaSinhVien, TenHoatDong, DiaDiem, TenDonViToChuc, ThoiGianBatDau, ThoiGianKetThuc, LinkMinhChung, MaTieuChi)
                    VALUES (:MaSinhVien, :TenHoatDong, :DiaDiem, :TenDonViToChuc, :ThoiGianBatDau, :ThoiGianKetThuc, :LinkMinhChung, :MaTieuChi)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'MaSinhVien' => $maSinhVien,
                'TenHoatDong' => $tenHoatDong,
                'DiaDiem' => $diaDiem,
                'TenDonViToChuc' => $donViToChuc,
                'ThoiGianBatDau' => $thoiGianBatDau,
                'ThoiGianKetThuc' => $thoiGianKetThuc,
                'LinkMinhChung' => $linkMinhChung,
                'MaTieuChi' => $maTieuChi,
            ]);
        } elseif ($action === 'edit') {
            // Lấy dữ liệu từ form
            $maHoatDong = $_POST['MaHoatDong'] ?? null;
            $tenHoatDong = $_POST['TenHoatDong'] ?? '';
            $diaDiem = $_POST['DiaDiem'] ?? '';
            $donViToChuc = $_POST['TenDonViToChuc'] ?? ''; // Đơn vị tổ chức
            $thoiGianBatDau = $_POST['ThoiGianBatDau'] ?? '';
            $thoiGianKetThuc = $_POST['ThoiGianKetThuc'] ?? '';
            $linkMinhChung = $_POST['LinkMinhChung'] ?? '';
            $maTieuChi = $_POST['MaTieuChi'] ?? null;
        
            // Kiểm tra dữ liệu hợp lệ
            if (empty($tenHoatDong) || empty($diaDiem) || empty($thoiGianBatDau) || empty($thoiGianKetThuc) || !$maTieuChi || empty($donViToChuc)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin hoạt động.");
            }
        
            // Sửa hoạt động
            $sql = "UPDATE hoatdong SET TenHoatDong = :TenHoatDong, DiaDiem = :DiaDiem, TenDonViToChuc = :TenDonViToChuc, ThoiGianBatDau = :ThoiGianBatDau, ThoiGianKetThuc = :ThoiGianKetThuc, LinkMinhChung = :LinkMinhChung, MaTieuChi = :MaTieuChi
                    WHERE MaHoatDong = :MaHoatDong AND MaSinhVien = :MaSinhVien";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'TenHoatDong' => $tenHoatDong,
                'DiaDiem' => $diaDiem,
                'TenDonViToChuc' => $donViToChuc,
                'ThoiGianBatDau' => $thoiGianBatDau,
                'ThoiGianKetThuc' => $thoiGianKetThuc,
                'LinkMinhChung' => $linkMinhChung,
                'MaTieuChi' => $maTieuChi,
                'MaHoatDong' => $maHoatDong,
                'MaSinhVien' => $maSinhVien,
            ]);
        } elseif ($action === 'delete') {
            // Lấy mã hoạt động cần xóa
            $maHoatDong = $_POST['MaHoatDong'] ?? null;

            if (!$maHoatDong) {
                throw new Exception("Không tìm thấy hoạt động cần xóa.");
            }

            // Xóa hoạt động
            $sql = "DELETE FROM hoatdong WHERE MaHoatDong = :MaHoatDong AND MaSinhVien = :MaSinhVien";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'MaHoatDong' => $maHoatDong,
                'MaSinhVien' => $maSinhVien
            ]);
        } else {
            throw new Exception("Hành động không hợp lệ.");
        }
        // Xử lý tìm kiếm và lọc
        $search = $_GET['search'] ?? '';
        $danhmuc = $_GET['danhmuc'] ?? '';
        $startDate = $_GET['startDate'] ?? '';
        $endDate = $_GET['endDate'] ?? '';

        $sql = "
    SELECT h.*, t.TenTieuChi, dm.TenDanhMuc 
    FROM hoatdong h
    JOIN tieuchi t ON h.MaTieuChi = t.MaTieuChi
    JOIN danhmuc dm ON t.MaDanhMuc = dm.MaDanhMuc
    WHERE h.MaSinhVien = :MaSinhVien
";

        $params = ['MaSinhVien' => $maSinhVien];

        if (!empty($search)) {
            $sql .= " AND h.TenHoatDong LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($danhmuc)) {
            $sql .= " AND dm.TenDanhMuc = :danhmuc";
            $params['danhmuc'] = $danhmuc;
        }

        // Lọc theo thời gian
        if (!empty($startDate)) {
            $sql .= " AND h.ThoiGianBatDau >= :startDate";
            $params['startDate'] = $startDate;
        }

        if (!empty($endDate)) {
            $sql .= " AND h.ThoiGianKetThuc <= :endDate";
            $params['endDate'] = $endDate;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $sql .= " ORDER BY t.TenTieuChi ASC, h.ThoiGianBatDau DESC LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Đếm tổng số hoạt động
        $sql_total = "SELECT COUNT(*) AS total FROM hoatdong h
        JOIN tieuchi t ON h.MaTieuChi = t.MaTieuChi
        JOIN danhmuc dm ON t.MaDanhMuc = dm.MaDanhMuc
        WHERE h.MaSinhVien = :MaSinhVien";

        if (!empty($search)) {
            $sql_total .= " AND h.TenHoatDong LIKE :search";
        }

        if (!empty($danhmuc)) {
            $sql_total .= " AND dm.TenDanhMuc = :danhmuc";
        }

        if (!empty($startDate)) {
            $sql_total .= " AND h.ThoiGianBatDau >= :startDate";
        }

        if (!empty($endDate)) {
            $sql_total .= " AND h.ThoiGianKetThuc <= :endDate";
        }

        $stmt_total = $pdo->prepare($sql_total);
        $stmt_total->execute($params);
        $totalActivities = $stmt_total->fetchColumn();
        $totalPages = ceil($totalActivities / $limit);
    } catch (Exception $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách tiêu chí
$sql_criteria = "
    SELECT dm.TenDanhMuc, tc.MaTieuChi, tc.TenTieuChi, tc.SoDiem 
    FROM tieuchi tc
    JOIN danhmuc dm ON tc.MaDanhMuc = dm.MaDanhMuc
    ORDER BY tc.MaTieuChi ASC
";
$criteria = $pdo->query($sql_criteria)->fetchAll(PDO::FETCH_ASSOC);


// Xử lý tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$danhmuc = $_GET['danhmuc'] ?? '';

$sql = "
    SELECT h.*, t.TenTieuChi, dm.TenDanhMuc 
    FROM hoatdong h
    JOIN tieuchi t ON h.MaTieuChi = t.MaTieuChi
    JOIN danhmuc dm ON t.MaDanhMuc = dm.MaDanhMuc
    WHERE h.MaSinhVien = :MaSinhVien
";

$params = ['MaSinhVien' => $maSinhVien];

if (!empty($search)) {
    $sql .= " AND h.TenHoatDong LIKE :search";
    $params['search'] = '%' . $search . '%';
}

if (!empty($danhmuc)) {
    $sql .= " AND dm.TenDanhMuc = :danhmuc";
    $params['danhmuc'] = $danhmuc;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 25;
$offset = ($page - 1) * $limit;

$sql .= " ORDER BY t.TenTieuChi ASC, h.ThoiGianBatDau DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Đếm tổng số hoạt động
$sql_total = "SELECT COUNT(*) AS total FROM hoatdong h
    JOIN tieuchi t ON h.MaTieuChi = t.MaTieuChi
    JOIN danhmuc dm ON t.MaDanhMuc = dm.MaDanhMuc
    WHERE h.MaSinhVien = :MaSinhVien";

if (!empty($search)) {
    $sql_total .= " AND h.TenHoatDong LIKE :search";
}

if (!empty($danhmuc)) {
    $sql_total .= " AND dm.TenDanhMuc = :danhmuc";
}

$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($params);
$totalActivities = $stmt_total->fetchColumn();
$totalPages = ceil($totalActivities / $limit);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Hoạt Động</title>
    <link rel="stylesheet" href="style.css">
    <script src="javascript.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                        <a href="hoSoCaNhan.php">
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
            <h1 class="text-center mb-4">Quản lý hoạt động</h1>
            <!-- Form tìm kiếm và lọc -->
            <div class=" card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <!-- Tìm kiếm -->
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên hoạt động..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <!-- Lọc theo danh mục -->
                        <div class="col-md-5">
                            <select name="danhmuc" class="form-select">
                                <option value="">-- Lọc theo danh mục --</option>
                                <?php
                                $currentDanhMuc = '';
                                foreach ($criteria as $criterion) {
                                    if ($currentDanhMuc !== $criterion['TenDanhMuc']) {
                                        $currentDanhMuc = $criterion['TenDanhMuc'];
                                        $selected = $danhmuc === $currentDanhMuc ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($currentDanhMuc) . '" ' . $selected . '>' . htmlspecialchars($currentDanhMuc) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <!-- Lọc theo thời gian -->
                        <div class="col-md-5">
                            <label for="filterCriteria" class="form-label">Từ ngày</label>
                            <input type="date" name="startDate" class="form-control" placeholder="Từ ngày" value="<?= htmlspecialchars($startDate) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="filterCriteria" class="form-label">Đến ngày</label>
                            <input type="date" name="endDate" class="form-control" placeholder="Đến ngày" value="<?= htmlspecialchars($endDate) ?>">
                        </div>
                        <!-- Nút tìm kiếm -->
                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                            <a href="?" class="btn btn-secondary ">Xóa lọc</a>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Button trigger modal for Add -->
            <button type="button" class="btn btn-primary mb-3 float-end" data-bs-toggle="modal" data-bs-target="#addModal">
                Thêm hoạt động
            </button>

            <!-- Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tên hoạt động</th>
                        <th>Địa điểm</th>
                        <th>Đơn vị tổ chức</th>
                        <th>Thời gian</th>
                        <th>Tiêu chí</th>
                        <th>Link minh chứng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($activities) > 0): ?>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?= htmlspecialchars($activity['TenHoatDong']) ?></td>
                                <td><?= htmlspecialchars($activity['DiaDiem']) ?></td>
                                <td><?= htmlspecialchars($activity['TenDonViToChuc']) ?></td>
                                <td>
                                    <?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianBatDau'])) ?> - <br>
                                    <?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianKetThuc'])) ?>
                                </td>
                                <td><?= htmlspecialchars($activity['TenTieuChi'] ?? 'Không có tiêu chí') ?></td>
                                <td>
                                    <?php if ($activity['LinkMinhChung']): ?>
                                        <a href="<?= htmlspecialchars($activity['LinkMinhChung']) ?>" target="_blank">Xem minh chứng</a>
                                    <?php else: ?>
                                        Không có
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" 
                                        onclick="populateEditModal(<?= htmlspecialchars(json_encode($activity)) ?>)">
                                         Sửa
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                        onclick="setDeleteId(<?= htmlspecialchars($activity['MaHoatDong']) ?>)">
                                         Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Không tìm thấy hoạt động nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Phân trang -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&danhmuc=<?= urlencode($danhmuc) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </section>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Thêm Hoạt Động</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="addTenHoatDong" class="form-label">Tên Hoạt Động</label>
                            <input type="text" class="form-control" id="addTenHoatDong" name="TenHoatDong" required>
                        </div>
                        <div class="mb-3">
                            <label for="addDiaDiem" class="form-label">Địa Điểm</label>
                            <input type="text" class="form-control" id="addDiaDiem" name="DiaDiem" required>
                        </div>
                        <div class="mb-3">
                            <label for="addDonViToChuc" class="form-label">Đơn vị tổ chức</label>
                            <input type="text" class="form-control" id="addDonViToChuc" name="TenDonViToChuc" required>
                        </div>
                        <div class="mb-3">
                            <label for="addThoiGianBatDau" class="form-label">Thời Gian Bắt Đầu</label>
                            <input type="datetime-local" class="form-control" id="addThoiGianBatDau" name="ThoiGianBatDau" required>
                        </div>
                        <div class="mb-3">
                            <label for="addThoiGianKetThuc" class="form-label">Thời Gian Kết Thúc</label>
                            <input type="datetime-local" class="form-control" id="addThoiGianKetThuc" name="ThoiGianKetThuc" required>
                        </div>
                        <div class="mb-3">
                            <label for="addLinkMinhChung" class="form-label">Link Minh Chứng</label>
                            <input type="url" class="form-control" id="addLinkMinhChung" name="LinkMinhChung">
                        </div>
                        <div class="mb-3">
                            <label for="addMaTieuChi" class="form-label">Tiêu Chí</label>
                            <select class="form-select" id="addMaTieuChi" name="MaTieuChi" required>
                                <option value="">-- Chọn Tiêu Chí --</option>
                                <?php
                                $currentCategory = '';
                                foreach ($criteria as $criterion):
                                    // Nếu danh mục mới, in ra tiêu đề danh mục
                                    if ($currentCategory !== $criterion['TenDanhMuc']):
                                        $currentCategory = $criterion['TenDanhMuc'];
                                ?>
                                        <optgroup label="<?= htmlspecialchars($currentCategory) ?>">
                                        <?php endif; ?>
                                        <option value="<?= htmlspecialchars($criterion['MaTieuChi']) ?>">
                                            <?= htmlspecialchars($criterion['TenTieuChi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Thêm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Sửa Hoạt Động</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" id="editMaHoatDong" name="MaHoatDong">
                        <div class="mb-3">
                            <label for="editTenHoatDong" class="form-label">Tên Hoạt Động</label>
                            <input type="text" class="form-control" id="editTenHoatDong" name="TenHoatDong" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDiaDiem" class="form-label">Địa Điểm</label>
                            <input type="text" class="form-control" id="editDiaDiem" name="DiaDiem" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDonViToChuc" class="form-label">Đơn vị tổ chức</label>
                            <input type="text" class="form-control" id="editDonViToChuc" name="TenDonViToChuc" required>
                        </div>
                        <div class="mb-3">
                            <label for="editThoiGianBatDau" class="form-label">Thời Gian Bắt Đầu</label>
                            <input type="datetime-local" class="form-control" id="editThoiGianBatDau" name="ThoiGianBatDau" required>
                        </div>
                        <div class="mb-3">
                            <label for="editThoiGianKetThuc" class="form-label">Thời Gian Kết Thúc</label>
                            <input type="datetime-local" class="form-control" id="editThoiGianKetThuc" name="ThoiGianKetThuc" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLinkMinhChung" class="form-label">Link Minh Chứng</label>
                            <input type="url" class="form-control" id="editLinkMinhChung" name="LinkMinhChung">
                        </div>
                        <div class="mb-3">
                            <label for="editMaTieuChi" class="form-label">Tiêu Chí</label>
                            <select class="form-select" id="editMaTieuChi" name="MaTieuChi" required>
                                <option value="">-- Chọn Tiêu Chí --</option>
                                <?php foreach ($criteria as $criterion): ?>
                                    <option value="<?= htmlspecialchars($criterion['MaTieuChi']) ?>">
                                        <?= htmlspecialchars($criterion['TenTieuChi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Xóa Hoạt Động</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteMaHoatDong" name="MaHoatDong">
                        <p>Bạn có chắc chắn muốn xóa hoạt động này?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function populateEditModal(activity) {
        document.getElementById('editMaHoatDong').value = activity.MaHoatDong;
        document.getElementById('editTenHoatDong').value = activity.TenHoatDong;
        document.getElementById('editDiaDiem').value = activity.DiaDiem;
        document.getElementById('editDonViToChuc').value = activity.TenDonViToChuc;
        document.getElementById('editThoiGianBatDau').value = activity.ThoiGianBatDau.replace(' ', 'T');
        document.getElementById('editThoiGianKetThuc').value = activity.ThoiGianKetThuc.replace(' ', 'T');
        document.getElementById('editLinkMinhChung').value = activity.LinkMinhChung;
        document.getElementById('editMaTieuChi').value = activity.MaTieuChi;
    }

    function setDeleteId(maHoatDong) {
        document.getElementById('deleteMaHoatDong').value = maHoatDong;
    }
</script>

</html>