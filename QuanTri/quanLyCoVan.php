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

// Số bản ghi trên mỗi trang
$recordsPerPage = 10;

// Trang hiện tại
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Tính toán OFFSET
$offset = ($page - 1) * $recordsPerPage;

// Thực hiện truy vấn để lấy danh sách cố vấn với phân trang
$sql = "SELECT * FROM covanhoctap LIMIT $recordsPerPage OFFSET $offset";
$result = $conn->query($sql);

// Tính tổng số bản ghi
$sqlTotal = "SELECT COUNT(*) as total FROM covanhoctap";
$totalResult = $conn->query($sqlTotal);
$totalRecords = $totalResult->fetch_assoc()['total'];

// Tính tổng số trang
$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Cố Vấn</title>
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
                        <a href="quanLyCoVan.php" class="active">
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
                    <a href="http://localhost/CSN/Login/logOut.php" class="nav-link">Log Out</a>
                </button>
            </div>
        </div>
    </div>
    <div class="container-content">
        <section>
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Danh sách cố vấn</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdvisorModal">
                        Thêm cố vấn
                    </button>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Mã cố vấn</th>
                            <th>Tên cố vấn</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Đơn vị</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . $row["MaCoVan"] . "</td>
                                        <td>" . $row["TenCoVan"] . "</td>
                                        <td>" . $row["Email"] . "</td>
                                        <td>" . $row["SoDienThoai"] . "</td>
                                        <td>" . $row["DonViQuanLy"] . "</td>
                                        <td>
                                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editAdvisorModal' 
                                                data-id='" . $row["MaCoVan"] . "' 
                                                data-ten='" . $row["TenCoVan"] . "' 
                                                data-email='" . $row["Email"] . "' 
                                                data-phone='" . $row["SoDienThoai"] . "' 
                                                data-department='" . $row["DonViQuanLy"] . "'>Sửa</button>
                                            <button class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteAdvisorModal' 
                                                data-id='" . $row["MaCoVan"] . "' 
                                                data-ten='" . $row["TenCoVan"] . "'>Xóa</button>
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
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </section>
    </div>

    <!-- Modal thêm cố vấn -->
    <div class="modal fade" id="addAdvisorModal" tabindex="-1" aria-labelledby="addAdvisorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAdvisorModalLabel">Thêm Cố Vấn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="CoVan_Them.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="advisorName" class="form-label">Mã cố vấn</label>
                            <input type="text" class="form-control" id="advisorName" name="maCovan" placeholder="Nhập mã cố vấn" required>
                        </div>
                        <div class="mb-3">
                            <label for="advisorName" class="form-label">Tên cố vấn</label>
                            <input type="text" class="form-control" id="advisorName" name="tenCovan" placeholder="Nhập tên cố vấn" required>
                        </div>
                        <div class="mb-3">
                            <label for="advisorEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="advisorEmail" name="email" placeholder="Nhập email" required>
                        </div>
                        <div class="mb-3">
                            <label for="advisorPhone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="advisorPhone" name="soDienThoai" placeholder="Nhập số điện thoại" required>
                        </div>
                        <div class="mb-3">
                            <label for="advisorDepartment" class="form-label">Đơn vị</label>
                            <input type="text" class="form-control" id="advisorDepartment" name="DonViQuanLy" placeholder="Nhập đơn vị" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Thêm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal sửa cố vấn -->
    <div class="modal fade" id="editAdvisorModal" tabindex="-1" aria-labelledby="editAdvisorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAdvisorModalLabel">Sửa Cố Vấn Học Tập</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="CoVan_Sua.php" method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editAdvisorId">
                        <div class="mb-3">
                            <label for="editAdvisorName" class="form-label">Tên cố vấn</label>
                            <input type="text" class="form-control" id="editAdvisorName" name="tenCovan" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAdvisorEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editAdvisorEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAdvisorPhone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="editAdvisorPhone" name="soDienThoai" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAdvisorDepartment" class="form-label">Đơn vị</label>
                            <input type="text" class="form-control" id="editAdvisorDepartment" name="DonViQuanLy" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal xác nhận xóa cố vấn -->
    <div class="modal fade" id="deleteAdvisorModal" tabindex="-1" aria-labelledby="deleteAdvisorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAdvisorModalLabel">Xác nhận xóa cố vấn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa cố vấn <strong id="deleteAdvisorName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <form action="CoVan_Xoa.php" method="POST">
                        <input type="hidden" name="id" id="deleteAdvisorId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const editAdvisorModal = document.getElementById('editAdvisorModal');
        editAdvisorModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-ten');
            const email = button.getAttribute('data-email');
            const phone = button.getAttribute('data-phone');
            const department = button.getAttribute('data-department');

            const modalId = editAdvisorModal.querySelector('#editAdvisorId');
            const modalName = editAdvisorModal.querySelector('#editAdvisorName');
            const modalEmail = editAdvisorModal.querySelector('#editAdvisorEmail');
            const modalPhone = editAdvisorModal.querySelector('#editAdvisorPhone');
            const modalDepartment = editAdvisorModal.querySelector('#editAdvisorDepartment');

            modalId.value = id;
            modalName.value = name;
            modalEmail.value = email;
            modalPhone.value = phone;
            modalDepartment.value = department;
        });

        document.addEventListener("DOMContentLoaded", function() {
            const deleteButtons = document.querySelectorAll("[data-bs-target='#deleteAdvisorModal']");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function() {
                    const id = button.getAttribute("data-id");
                    const name = button.getAttribute("data-ten");

                    document.getElementById("deleteAdvisorId").value = id;
                    document.getElementById("deleteAdvisorName").textContent = name;
                });
            });
        });
    </script>
</body>

</html>