<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

try {
    $pdo = new PDO("mysql:host=$mayChu;dbname=$tenCSDL;charset=utf8", $tenNguoiDung, $matKhauCSDL);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lấy dữ liệu tìm kiếm và lọc
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $filterClass = isset($_GET['filterClass']) ? $_GET['filterClass'] : '';

    // Thiết lập phân trang
    $recordsPerPage = 20; // Số lượng bản ghi mỗi trang
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Trang hiện tại
    $page = $page > 0 ? $page : 1; // Đảm bảo trang không nhỏ hơn 1
    $offset = ($page - 1) * $recordsPerPage;

    // Lấy danh sách sinh viên với phân trang
    $sql = "SELECT * FROM sinhvien WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (
            LOWER(CONCAT(HoTen, ' ', Ten)) LIKE LOWER(:search) OR 
            LOWER(HoTen) LIKE LOWER(:search) OR 
            LOWER(Ten) LIKE LOWER(:search) OR 
            LOWER(MaSinhVien) LIKE LOWER(:search) OR 
            LOWER(Email) LIKE LOWER(:search) OR 
            LOWER(SoDienThoai) LIKE LOWER(:search)
        )";
        $params['search'] = '%' . $search . '%';
    }

    if (!empty($filterClass)) {
        $sql .= " AND MaLop = :filterClass";
        $params['filterClass'] = $filterClass;
    }

    // Thêm phân trang
    $sql .= " ORDER BY MaLop, MaSinhVien LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $students = $stmt->fetchAll();

    // Đếm tổng số bản ghi
    $sqlCount = "SELECT COUNT(*) FROM sinhvien WHERE 1=1";
    if (!empty($search)) {
        $sqlCount .= " AND (
            LOWER(CONCAT(HoTen, ' ', Ten)) LIKE LOWER(:search) OR 
            LOWER(HoTen) LIKE LOWER(:search) OR 
            LOWER(Ten) LIKE LOWER(:search) OR 
            LOWER(MaSinhVien) LIKE LOWER(:search) OR 
            LOWER(Email) LIKE LOWER(:search) OR 
            LOWER(SoDienThoai) LIKE LOWER(:search)
        )";
    }
    if (!empty($filterClass)) {
        $sqlCount .= " AND MaLop = :filterClass";
    }
    $stmtCount = $pdo->prepare($sqlCount);
    foreach ($params as $key => $value) {
        $stmtCount->bindValue(":$key", $value);
    }
    $stmtCount->execute();
    $totalRecords = $stmtCount->fetchColumn();
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Lấy danh sách lớp học
    $sqlGetClasses = "SELECT * FROM lophoc";
    $stmtGetClasses = $pdo->prepare($sqlGetClasses);
    $stmtGetClasses->execute();
    $classes = $stmtGetClasses->fetchAll();
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sinh Viên</title>
    <link rel="stylesheet" href="style.css">
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
                        <a href="quanLyCoVan.php">
                            <i class="fa-solid fa-list"></i>
                            <span>Quản lý cố vấn</span>
                        </a>
                    </li>
                    <li>
                        <a href="quanLySinhVien.php" class="active">
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
            <div class="mt-5">
                <h2>Danh sách sinh viên</h2>
                <form class="d-flex mb-3" method="GET" action="quanLySinhVien.php">
                    <div class="col-md-5 me-3">
                        <input type="text" class="form-control me-2" name="search" placeholder="Tìm kiếm sinh viên..."
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-4 me-3">
                        <select class="form-select me-2" name="filterClass">
                            <option value="">Lọc theo lớp</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['MaLop'] ?>" <?= $filterClass === $class['MaLop'] ? 'selected' : '' ?>>
                                    <?= $class['MaLop'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </form>
                <div class="d-flex justify-content-end align-items-center mb-3">
                    <button class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        Thêm sinh viên
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                        Import từ Excel
                    </button>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>MSSV</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Lớp học</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($stmt->rowCount() > 0) {
                            foreach ($students as $row) {
                                echo "<tr>
                        <td>" . $row["MaSinhVien"] . "</td>
                        <td>" . $row["HoTen"] . " " . $row["Ten"] . "</td>
                        <td>" . $row["Email"] . "</td>
                        <td>" . $row["SoDienThoai"] . "</td>
                        <td>" . $row["MaLop"] . "</td>
                        <td>
                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editStudentModal' 
                                    data-id='" . $row["MaSinhVien"] . "' 
                                    data-hoTen='" . $row["HoTen"] . "' 
                                    data-ten='" . $row["Ten"] . "' 
                                    data-email='" . $row["Email"] . "' 
                                    data-phone='" . $row["SoDienThoai"] . "' 
                                    data-class='" . $row["MaLop"] . "'>Sửa</button>
                            <button class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteStudentModal'
                                    data-id='" . $row["MaSinhVien"] . "' 
                                    data-hoTen='" . $row["HoTen"] . " " . $row["Ten"] . "'>Xóa</button>
                        </td>
                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>Không có dữ liệu.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= htmlspecialchars($search) ?>&filterClass=<?= htmlspecialchars($filterClass) ?>">Trước</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>&filterClass=<?= htmlspecialchars($filterClass) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= htmlspecialchars($search) ?>&filterClass=<?= htmlspecialchars($filterClass) ?>">Tiếp</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </section>
    </div>

    <!-- Modal thêm sinh viên -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Thêm Sinh Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="SinhVien_Them.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="studentName" class="form-label">Mã sinh viên</label>
                            <input type="text" class="form-control" id="studentName" name="maSinhVien"
                                placeholder="Nhập mã sinh viên" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentFullName" class="form-label">Họ lót sinh viên</label>
                            <input type="text" class="form-control" id="studentFullName" name="HoTen" placeholder="Nhập họ sinh viên" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentLastName" class="form-label">Tên sinh viên</label>
                            <input type="text" class="form-control" id="studentLastName" name="Ten" placeholder="Nhập tên sinh viên" required>
                        </div>

                        <div class="mb-3">
                            <label for="studentEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="studentEmail" name="email"
                                placeholder="Nhập email" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentPhone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="studentPhone" name="soDienThoai"
                                placeholder="Nhập số điện thoại" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentClass" class="form-label">Lớp học</label>
                            <select class="form-select" id="studentClass" name="MaLop" required>
                                <option value="">Chọn lớp</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['MaLop'] ?>"><?= $class['MaLop'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Thêm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal sửa sinh viên -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Sửa Thông Tin Sinh Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="SinhVien_Sua.php" method="POST">
                        <input type="hidden" name="maSinhVien" id="editMaSinhVien">

                        <div class="mb-3">
                            <label for="editStudentFullName" class="form-label">Họ sinh viên</label>
                            <input type="text" class="form-control" id="editStudentFullName" name="HoTen" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStudentLastName" class="form-label">Tên sinh viên</label>
                            <input type="text" class="form-control" id="editStudentLastName" name="Ten" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStudentEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editStudentEmail" name="Email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStudentPhone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="editStudentPhone" name="SoDienThoai" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStudentClass" class="form-label">Lớp học</label>
                            <select class="form-select" id="editStudentClass" name="MaLop" required>
                                <option value="">Chọn lớp</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['MaLop'] ?>"><?= $class['MaLop'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa sinh viên -->
    <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteStudentModalLabel">Xác nhận xóa sinh viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa sinh viên <strong id="deleteStudentName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <form action="SinhVien_Xoa.php" method="POST">
                        <input type="hidden" name="maSinhVien" id="deleteStudentId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal import sinh viên từ Excel -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelModalLabel">Import Sinh Viên Từ Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="SinhVien_Import.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="excelFile" class="form-label">Chọn file Excel</label>
                            <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xls,.xlsx" required>
                        </div>
                        <button type="submit" class="btn btn-success">Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var editButtons = document.querySelectorAll("[data-bs-target='#editStudentModal']");
        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var maSinhVien = this.getAttribute('data-id');
                var hoTen = this.getAttribute('data-hoTen');
                var ten = this.getAttribute('data-ten');
                var email = this.getAttribute('data-email');
                var phone = this.getAttribute('data-phone');
                var studentClass = this.getAttribute('data-class');

                document.getElementById('editMaSinhVien').value = maSinhVien;
                document.getElementById('editStudentFullName').value = hoTen;
                document.getElementById('editStudentLastName').value = ten;
                document.getElementById('editStudentEmail').value = email;
                document.getElementById('editStudentPhone').value = phone;
                document.getElementById('editStudentClass').value = studentClass;
            });
        });

        var deleteButtons = document.querySelectorAll("[data-bs-target='#deleteStudentModal']");
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var studentId = this.getAttribute('data-id');
                var studentName = this.getAttribute('data-hoTen');

                document.getElementById('deleteStudentId').value = studentId;
                document.getElementById('deleteStudentName').textContent = studentName;
            });
        });
    </script>
</body>

</html>