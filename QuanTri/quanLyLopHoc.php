<?php
$mayChu = 'localhost';
$tenCSDL = 'doancsn';
$tenNguoiDung = 'root';
$matKhauCSDL = '';

// Kết nối CSDL
$conn = new mysqli($mayChu, $tenNguoiDung, $matKhauCSDL, $tenCSDL);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Xử lý thêm, sửa, xóa lớp học
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'delete') {
            // Xóa lớp học
            $maLopHoc = $conn->real_escape_string($_POST['id']);

            $sql = "DELETE FROM lophoc WHERE MaLop = '$maLopHoc'";
            if ($conn->query($sql) === TRUE) {
                echo "<script> window.location.href = 'quanLyLopHoc.php';</script>";
            } else {
                echo "<script>alert('Lỗi khi xóa lớp học: {$conn->error}');</script>";
            }
        }
    }
}

// Thiết lập phân trang
$recordsPerPage = 10; // Số bản ghi mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Trang hiện tại
$page = $page > 0 ? $page : 1; // Đảm bảo trang lớn hơn 0
$offset = ($page - 1) * $recordsPerPage;

// Đếm tổng số lớp học
$sqlCount = "SELECT COUNT(*) AS total FROM lophoc";
$resultCount = $conn->query($sqlCount);
$totalRecords = $resultCount->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Truy vấn danh sách lớp học có phân trang
$sql = "SELECT l.MaLop, l.TenLop, l.SoLuongSinhVien, l.MaCoVan, c.TenCoVan 
        FROM lophoc l
        LEFT JOIN covanhoctap c ON l.MaCoVan = c.MaCoVan 
        LIMIT $recordsPerPage OFFSET $offset";
$result = $conn->query($sql);

// Truy vấn danh sách cố vấn
$sql2 = "SELECT MaCoVan, TenCoVan FROM covanhoctap";
$result2 = $conn->query($sql2);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Lớp Học</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <!-- Nội dung trang quản lý lớp học -->
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
                        <a href="quanLyLopHoc.php" class="active">
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Danh sách lớp học</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                        Thêm lớp học
                    </button>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Mã lớp </th>
                            <th>Tên lớp </th>
                            <th>Số lượng sinh viên</th>
                            <th>Tên cố vấn</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . $row["MaLop"] . "</td>
                                        <td>" . $row["TenLop"] . "</td>
                                        <td>" . $row["SoLuongSinhVien"] . "</td>
                                        <td>" . $row["TenCoVan"] . "</td>  <!-- Hiển thị tên cố vấn -->
                                        <td>
                                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editClassModal'
                                                data-id='" . $row["MaLop"] . "' data-ten='" . $row["TenLop"] . "' 
                                                data-gv='" . $row["SoLuongSinhVien"] . "' data-tc='" . $row["MaCoVan"] . "'>Sửa</button>

                                            <button class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteClassModal'
                                                data-id='" . $row["MaLop"] . "' data-ten='" . $row["TenLop"] . "'>Xóa</button>
                                        </td>

                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>Không có dữ liệu.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">Trước</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">Tiếp</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </section>
    </div>

    <!-- Modal thêm lớp học -->
    <div class="modal fade" id="addClassModal" tabindex="-1" aria-labelledby="addClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClassModalLabel">Thêm Lớp Học</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="LopHoc_Them.php" method="POST">
                        <div class="mb-3">
                            <label for="classCode" class="form-label">Mã lớp</label>
                            <input type="text" class="form-control" id="classCode" name="maLopHoc" placeholder="Nhập mã lớp" required>
                        </div>
                        <div class="mb-3">
                            <label for="className" class="form-label">Tên lớp</label>
                            <input type="text" class="form-control" id="className" name="tenLopHoc" placeholder="Nhập tên lớp" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentCount" class="form-label">Số lượng sinh viên</label>
                            <input type="number" class="form-control" id="studentCount" name="soLuongSinhVien" placeholder="Số lượng sinh viên" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCredits" class="form-label">Chọn Cố Vấn</label>
                            <select class="form-control" id="editCredits" name="maCoVan" required>
                                <?php
                                if ($result2->num_rows > 0) {
                                    while ($row2 = $result2->fetch_assoc()) {
                                        echo "<option value='" . $row2['MaCoVan'] . "'>" . $row2['TenCoVan'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Thêm</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal sửa lớp học -->
    <div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClassModalLabel">Sửa Lớp Học</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="LopHoc_Sua.php" method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editClassId">
                        <div class="mb-3">
                            <label for="editClassName" class="form-label">Tên lớp</label>
                            <input type="text" class="form-control" id="editClassName" name="tenLopHoc" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTeacher" class="form-label">Số lượng sinh viên</label>
                            <input type="number" class="form-control" id="editTeacher" name="soLuongSinhVien" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAdvisor" class="form-label">Chọn Cố Vấn</label>
                            <select class="form-control" id="editAdvisor" name="maCoVan" required>
                                <?php
                                // Truy vấn danh sách cố vấn
                                $sql2 = "SELECT MaCoVan, TenCoVan FROM covanhoctap";
                                $result2 = $conn->query($sql2);
                                if ($result2->num_rows > 0) {
                                    while ($row2 = $result2->fetch_assoc()) {
                                        echo "<option value='" . $row2['MaCoVan'] . "'>" . $row2['TenCoVan'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Xóa Lớp Học -->
    <div class="modal fade" id="deleteClassModal" tabindex="-1" aria-labelledby="deleteClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteClassModalLabel">Xác nhận xóa lớp học</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa lớp học <strong id="deleteClassName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <form action="quanLyLopHoc.php" method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteClassId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const editClassModal = document.getElementById('editClassModal');
        editClassModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget; // Nút "Sửa" vừa được nhấn
            const id = button.getAttribute('data-id'); // Mã lớp học (VARCHAR)
            const name = button.getAttribute('data-ten'); // Tên lớp học
            const studentCount = button.getAttribute('data-gv'); // Số lượng sinh viên
            const advisorId = button.getAttribute('data-tc'); // Mã cố vấn

            const modalId = editClassModal.querySelector('#editClassId');
            const modalName = editClassModal.querySelector('#editClassName');
            const modalStudentCount = editClassModal.querySelector('#editTeacher');
            const modalAdvisor = editClassModal.querySelector('#editAdvisor'); // Dropdown cố vấn

            // Điền các giá trị vào các trường trong modal
            modalId.value = id; // Đây là mã lớp học kiểu VARCHAR
            modalName.value = name;
            modalStudentCount.value = studentCount;

            // Chọn cố vấn đúng trong dropdown
            modalAdvisor.value = advisorId;
        });

        document.addEventListener("DOMContentLoaded", function() {
            const deleteButtons = document.querySelectorAll("[data-bs-target='#deleteClassModal']");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function() {
                    const id = button.getAttribute("data-id");
                    const name = button.getAttribute("data-ten");

                    // Điền thông tin vào modal
                    document.getElementById("deleteClassId").value = id;
                    document.getElementById("deleteClassName").textContent = name;
                });
            });
        });
    </script>
</body>

</html>