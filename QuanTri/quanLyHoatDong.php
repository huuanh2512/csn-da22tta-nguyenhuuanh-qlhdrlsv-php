<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

try {
    $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Xử lý thêm hoạt động
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $maSinhVien = $_POST['MaSinhVien'];
            $maTieuChi = $_POST['MaTieuChi'];
            $tenHoatDong = $_POST['TenHoatDong'];
            $diaDiem = $_POST['DiaDiem'];
            $donViToChuc = $_POST['TenDonViToChuc'];
            $thoiGianBatDau = $_POST['ThoiGianBatDau'];
            $thoiGianKetThuc = $_POST['ThoiGianKetThuc'];
            $linkMinhChung = $_POST['LinkMinhChung'];

            $sqlInsert = "
                INSERT INTO hoatdong (MaSinhVien, MaTieuChi, TenHoatDong, DiaDiem, TenDonViToChuc, ThoiGianBatDau, ThoiGianKetThuc, LinkMinhChung)
                VALUES (:MaSinhVien, :MaTieuChi, :TenHoatDong, :DiaDiem, :TenDonViToChuc, :ThoiGianBatDau, :ThoiGianKetThuc, :LinkMinhChung)
            ";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                ':MaSinhVien' => $maSinhVien,
                ':MaTieuChi' => $maTieuChi,
                ':TenHoatDong' => $tenHoatDong,
                ':DiaDiem' => $diaDiem,
                ':TenDonViToChuc' => $donViToChuc,
                ':ThoiGianBatDau' => $thoiGianBatDau,
                ':ThoiGianKetThuc' => $thoiGianKetThuc,
                ':LinkMinhChung' => $linkMinhChung,
            ]);
            header("Location: quanLyHoatDong.php");
            exit;
        }

        // Xử lý sửa hoạt động
        if ($_POST['action'] === 'edit') {
            $maHoatDong = $_POST['MaHoatDong'];
            $maSinhVien = $_POST['MaSinhVien'];
            $maTieuChi = $_POST['MaTieuChi'];
            $tenHoatDong = $_POST['TenHoatDong'];
            $diaDiem = $_POST['DiaDiem'];
            $donViToChuc = $_POST['TenDonViToChuc'];
            $thoiGianBatDau = $_POST['ThoiGianBatDau'];
            $thoiGianKetThuc = $_POST['ThoiGianKetThuc'];
            $linkMinhChung = $_POST['LinkMinhChung'];

            $sqlUpdate = "
                UPDATE hoatdong 
                SET MaSinhVien = :MaSinhVien, MaTieuChi = :MaTieuChi, TenHoatDong = :TenHoatDong, 
                    DiaDiem = :DiaDiem, TenDonViToChuc = :TenDonViToChuc, ThoiGianBatDau = :ThoiGianBatDau, 
                    ThoiGianKetThuc = :ThoiGianKetThuc, LinkMinhChung = :LinkMinhChung
                WHERE MaHoatDong = :MaHoatDong
            ";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':MaSinhVien' => $maSinhVien,
                ':MaTieuChi' => $maTieuChi,
                ':TenHoatDong' => $tenHoatDong,
                ':DiaDiem' => $diaDiem,
                ':TenDonViToChuc' => $donViToChuc,
                ':ThoiGianBatDau' => $thoiGianBatDau,
                ':ThoiGianKetThuc' => $thoiGianKetThuc,
                ':LinkMinhChung' => $linkMinhChung,
                ':MaHoatDong' => $maHoatDong,
            ]);
            header("Location: quanLyHoatDong.php");
            exit;
        }

        // Xử lý xóa hoạt động
        if ($_POST['action'] === 'delete') {
            $maHoatDong = $_POST['MaHoatDong'];

            $sqlDelete = "DELETE FROM hoatdong WHERE MaHoatDong = :MaHoatDong";
            $stmtDelete = $pdo->prepare($sqlDelete);
            $stmtDelete->execute([':MaHoatDong' => $maHoatDong]);

            header("Location: quanLyHoatDong.php");
            exit;
        }
    }
    // Các tham số phân trang
    $recordsPerPage = 10;
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $recordsPerPage;

    // Xử lý tìm kiếm và lọc
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $filterClass = isset($_GET['filterClass']) ? $_GET['filterClass'] : '';
    $filterCriteria = isset($_GET['filterCriteria']) ? $_GET['filterCriteria'] : '';

    $sqlActivities = "
    SELECT 
        hd.MaHoatDong,
        hd.TenHoatDong,
        hd.DiaDiem,
        hd.TenDonViToChuc,
        hd.ThoiGianBatDau,
        hd.ThoiGianKetThuc,
        hd.LinkMinhChung,
        sv.HoTen AS HoTenSinhVien,
        sv.Ten AS TenSinhVien,
        sv.MaSinhVien,
        sv.MaLop,
        tc.TenTieuChi
    FROM hoatdong hd
    LEFT JOIN sinhvien sv ON hd.MaSinhVien = sv.MaSinhVien
    LEFT JOIN tieuchi tc ON hd.MaTieuChi = tc.MaTieuChi
    WHERE 1=1
";

    $params = [];

    // Tìm kiếm theo họ tên, mã sinh viên
    if (!empty($search)) {
        $sqlActivities .= " AND (
            CONCAT(LOWER(sv.HoTen), ' ', LOWER(sv.Ten)) LIKE LOWER(:search) OR 
            LOWER(sv.MaSinhVien) LIKE LOWER(:search)
        )";
        $params['search'] = '%' . $search . '%';
    }

    // Lọc theo lớp
    if (!empty($filterClass)) {
        $sqlActivities .= " AND sv.MaLop = :filterClass";
        $params['filterClass'] = $filterClass;
    }

    // Lọc theo tiêu chí
    if (!empty($filterCriteria)) {
        $sqlActivities .= " AND tc.MaTieuChi = :filterCriteria";
        $params['filterCriteria'] = $filterCriteria;
    }

    // Tính tổng số bản ghi
    $sqlCount = "SELECT COUNT(*) FROM (" . $sqlActivities . ") AS total";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $totalRecords = $stmtCount->fetchColumn();

    // Tính tổng số trang
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Thêm LIMIT và OFFSET cho truy vấn
    $sqlActivities .= " ORDER BY hd.ThoiGianBatDau DESC LIMIT :limit OFFSET :offset";
    $stmtActivities = $pdo->prepare($sqlActivities);

    // Gán tham số LIMIT và OFFSET
    foreach ($params as $key => $value) {
        $stmtActivities->bindValue(':' . $key, $value);
    }
    $stmtActivities->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmtActivities->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmtActivities->execute();
    $activities = $stmtActivities->fetchAll();
    $students = "SELECT * FROM sinhvien";
    $stmtStudents = $pdo->prepare($students);
    $stmtStudents->execute();
    $students = $stmtStudents->fetchAll();

    $criteria = "SELECT * FROM tieuchi";
    $stmtCriteria = $pdo->prepare($criteria);
    $stmtCriteria->execute();
    $criteria = $stmtCriteria->fetchAll();
} catch (PDOException $e) {
    echo "Lỗi kết nối CSDL: " . $e->getMessage();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Hoạt Động</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a href="quanLyHoatDong.php" class="active">
                            <i class="fa-solid fa-list-check"></i>
                            <span>Quản lý hoạt động</span>
                        </a>
                    </li>
                    <li>
                        <a href="thongKeHoatDong.php">
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
            <h1 class="mb-4">Quản lý hoạt động</h1>

            <!-- Tìm kiếm và lọc -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <!-- Row 1: Search and Class filter -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="search" class="form-label">Tìm kiếm sinh viên</label>
                                <input type="text" name="search" id="search" class="form-control"
                                    placeholder="Nhập MSSV hoặc họ tên..."
                                    value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="filterClass" class="form-label">Lọc theo lớp</label>
                                <select name="filterClass" id="filterClass" class="form-select">
                                    <option value="">Chọn lớp</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?= $class['MaLop'] ?>"
                                            <?= $filterClass === $class['MaLop'] ? 'selected' : '' ?>>
                                            <?= $class['MaLop'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Row 2: Criteria and Date filters -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="filterCriteria" class="form-label">Lọc theo tiêu chí</label>
                                <select name="filterCriteria" id="filterCriteria" class="form-select">
                                    <option value="">Chọn tiêu chí</option>
                                    <?php foreach ($criteria as $criterion): ?>
                                        <option value="<?= $criterion['MaTieuChi'] ?>"
                                            <?= $filterCriteria === $criterion['MaTieuChi'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($criterion['TenTieuChi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="startDate" class="form-label">Từ ngày</label>
                                    <input type="date" name="startDate" id="startDate" class="form-control"
                                        value="<?= htmlspecialchars($startDate) ?>" placeholder="YYYY-MM-DD">
                                </div>
                                <div class="col-md-6">
                                    <label for="endDate" class="form-label">Đến ngày</label>
                                    <input type="date" name="endDate" id="endDate" class="form-control"
                                        value="<?= htmlspecialchars($endDate) ?>" placeholder="YYYY-MM-DD">
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Buttons -->
                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                <a href="?" class="btn btn-secondary me-2">Xóa lọc</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Nút Thêm Hoạt Động -->
            <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#addActivityModal">Thêm Hoạt Động</button>

            <!-- Bảng Hiển Thị -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Mã Sinh Viên</th>
                        <th>Mã Lớp</th>
                        <th>Tên Hoạt Động</th>
                        <th>Địa Điểm</th>
                        <th>Đơn vị tổ chức</th>
                        <th>Thời Gian</th>
                        <th>Tiêu Chí</th>
                        <th>Link Minh Chứng</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td><?= htmlspecialchars($activity['MaSinhVien']) ?></td>
                            
                            <td><?= htmlspecialchars($activity['MaLop']) ?></td>
                            <td><?= htmlspecialchars($activity['TenHoatDong']) ?></td>
                            <td><?= htmlspecialchars($activity['DiaDiem']) ?></td>
                            <td><?= htmlspecialchars($activity['TenDonViToChuc']) ?></td>
                            <td>
                                <?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianBatDau'])) ?> -
                                <?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianKetThuc'])) ?>
                            </td>
                            <td><?= htmlspecialchars($activity['TenTieuChi']) ?></td>
                            <td><a href="<?= htmlspecialchars($activity['LinkMinhChung']) ?>" target="_blank">Xem</a></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-activity"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editActivityModal"
                                    data-id="<?= $activity['MaHoatDong'] ?>">
                                    Sửa
                                </button>
                                <button class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteActivityModal"
                                    data-id="<?= $activity['MaHoatDong'] ?>"
                                    data-tenhoatdong="<?= htmlspecialchars($activity['TenHoatDong']) ?>">
                                    Xóa
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . $search : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </section>
    </div>

    <!-- Modal Thêm Hoạt Động -->
    <div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addActivityModalLabel">Thêm Hoạt Động</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Chọn Sinh Viên -->
                        <div class="mb-3">
                            <label for="MaSinhVien" class="form-label">Sinh Viên</label>
                            <select class="form-select" id="MaSinhVien" name="MaSinhVien" required>
                                <option value="">Chọn Sinh Viên</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['MaSinhVien'] ?>">
                                        <?= htmlspecialchars($student['HoTen'] . " " . $student['Ten'] . " (" . $student['MaLop'] . ")") ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Chọn Tiêu Chí -->
                        <div class="mb-3">
                            <label for="MaTieuChi" class="form-label">Tiêu Chí</label>
                            <select class="form-select" id="MaTieuChi" name="MaTieuChi" required>
                                <option value="">Chọn Tiêu Chí</option>
                                <?php foreach ($criteria as $criterion): ?>
                                    <option value="<?= $criterion['MaTieuChi'] ?>">
                                        <?= htmlspecialchars($criterion['TenTieuChi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Các Trường Nhập Hoạt Động -->
                        <div class="mb-3">
                            <label for="TenHoatDong" class="form-label">Tên Hoạt Động</label>
                            <input type="text" class="form-control" id="TenHoatDong" name="TenHoatDong" required>
                        </div>
                        <div class="mb-3">
                            <label for="DiaDiem" class="form-label">Địa Điểm</label>
                            <input type="text" class="form-control" id="DiaDiem" name="DiaDiem" required>
                        </div>
                        <div class="mb-3">
                            <label for="TenDonViToChuc" class="form-label">Đơn Vị Tổ Chức</label>
                            <input type="text" class="form-control" id="TenDonViToChuc" name="TenDonViToChuc" required>
                        </div>
                        <div class="mb-3">
                            <label for="ThoiGianBatDau" class="form-label">Thời Gian Bắt Đầu</label>
                            <input type="datetime-local" class="form-control" id="ThoiGianBatDau" name="ThoiGianBatDau" required>
                        </div>
                        <div class="mb-3">
                            <label for="ThoiGianKetThuc" class="form-label">Thời Gian Kết Thúc</label>
                            <input type="datetime-local" class="form-control" id="ThoiGianKetThuc" name="ThoiGianKetThuc" required>
                        </div>
                        <div class="mb-3">
                            <label for="LinkMinhChung" class="form-label">Link Minh Chứng</label>
                            <input type="url" class="form-control" id="LinkMinhChung" name="LinkMinhChung">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Thêm Hoạt Động</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sửa Hoạt Động -->
    <div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="editMaHoatDong" name="MaHoatDong">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editActivityModalLabel">Sửa Hoạt Động</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Chọn Sinh Viên -->
                        <div class="mb-3">
                            <label for="editMaSinhVien" class="form-label">Sinh Viên</label>
                            <select class="form-select" id="editMaSinhVien" name="MaSinhVien" required>
                                <option value="">Chọn Sinh Viên</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['MaSinhVien'] ?>">
                                        <?= htmlspecialchars($student['HoTen'] . " " . $student['Ten'] . " (" . $student['MaLop'] . ")") ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Chọn Tiêu Chí -->
                        <div class="mb-3">
                            <label for="editMaTieuChi" class="form-label">Tiêu Chí</label>
                            <select class="form-select" id="editMaTieuChi" name="MaTieuChi" required>
                                <option value="">Chọn Tiêu Chí</option>
                                <?php foreach ($criteria as $criterion): ?>
                                    <option value="<?= $criterion['MaTieuChi'] ?>">
                                        <?= htmlspecialchars($criterion['TenTieuChi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Các Trường Nhập Hoạt Động -->
                        <div class="mb-3">
                            <label for="editTenHoatDong" class="form-label">Tên Hoạt Động</label>
                            <input type="text" class="form-control" id="editTenHoatDong" name="TenHoatDong" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDiaDiem" class="form-label">Địa Điểm</label>
                            <input type="text" class="form-control" id="editDiaDiem" name="DiaDiem" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTenDonViToChuc" class="form-label">Đơn Vị Tổ Chức</label>
                            <input type="text" class="form-control" id="editTenDonViToChuc" name="TenDonViToChuc" required>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Cập Nhật Hoạt Động</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Xác nhận Xóa -->
    <div class="modal fade" id="deleteActivityModal" tabindex="-1" aria-labelledby="deleteActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteActivityModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa hoạt động này không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteMaHoatDong" name="MaHoatDong">
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lưu dữ liệu hoạt động vào biến JavaScript
        const activities = <?php echo json_encode($activities); ?>;

        // Gắn giá trị vào modal Sửa
        document.querySelectorAll('.edit-activity').forEach(button => {
            button.addEventListener('click', function() {
                const activityId = this.getAttribute('data-id');
                const activity = activities.find(a => a.MaHoatDong == activityId);

                if (activity) {
                    const modal = document.getElementById('editActivityModal');
                    modal.querySelector('#editMaHoatDong').value = activity.MaHoatDong;
                    modal.querySelector('#editMaSinhVien').value = activity.MaSinhVien;
                    modal.querySelector('#editMaTieuChi').value = activity.MaTieuChi;
                    modal.querySelector('#editTenHoatDong').value = activity.TenHoatDong;
                    modal.querySelector('#editDiaDiem').value = activity.DiaDiem;
                    modal.querySelector('#editTenDonViToChuc').value = activity.TenDonViToChuc;
                    modal.querySelector('#editLinkMinhChung').value = activity.LinkMinhChung;

                    // Xử lý datetime
                    const batDau = activity.ThoiGianBatDau.replace(' ', 'T');
                    const ketThuc = activity.ThoiGianKetThuc.replace(' ', 'T');

                    modal.querySelector('#editThoiGianBatDau').value = batDau;
                    modal.querySelector('#editThoiGianKetThuc').value = ketThuc;
                }
            });
        });

        // Modal xóa (giữ nguyên như cũ)
        const deleteButtons = document.querySelectorAll("[data-bs-target='#deleteActivityModal']");
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modal = document.getElementById('deleteActivityModal');
                modal.querySelector('#deleteMaHoatDong').value = this.dataset.id; // Gán giá trị MaHoatDong
                modal.querySelector('.modal-body').textContent =
                    `Bạn có chắc chắn muốn xóa hoạt động "${this.dataset.tenhoatdong}" không?`;
            });
        });
    </script>
</body>

</html>