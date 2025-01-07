<?php
session_start();

// Kiểm tra nếu sinh viên chưa đăng nhập
$maSinhVien = isset($_SESSION['MaSinhVien']) ? $_SESSION['MaSinhVien'] : null;
if (!$maSinhVien) {
    die("Bạn cần đăng nhập để xem thông tin này.");
}

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
// Lấy danh mục, tiêu chí và tổng điểm
$sql_categories = "
    SELECT dm.MaDanhMuc, dm.TenDanhMuc, 
           tc.MaTieuChi, tc.TenTieuChi, 
           SUM(CASE WHEN h.MaSinhVien = :maSinhVien THEN COALESCE(tc.SoDiem, 0) ELSE 0 END) AS TongDiemDanhMuc
    FROM danhmuc dm
    LEFT JOIN tieuchi tc ON dm.MaDanhMuc = tc.MaDanhMuc
    LEFT JOIN hoatdong h ON tc.MaTieuChi = h.MaTieuChi AND h.MaSinhVien = :maSinhVien
    GROUP BY dm.MaDanhMuc, tc.MaTieuChi
    ORDER BY dm.MaDanhMuc, tc.MaTieuChi
";


$stmt_categories = $pdo->prepare($sql_categories);
$stmt_categories->execute(['maSinhVien' => $maSinhVien]);
$categories = $stmt_categories->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

// Lấy các hoạt động của sinh viên
$sql_activities = "
    SELECT h.MaTieuChi, COUNT(h.MaHoatDong) AS SoHoatDong
    FROM hoatdong h
    WHERE h.MaSinhVien = :maSinhVien
    GROUP BY h.MaTieuChi
";
$stmt_activities = $pdo->prepare($sql_activities);
$stmt_activities->execute(['maSinhVien' => $maSinhVien]);
$activityCounts = $stmt_activities->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Hoạt Động Theo Tiêu Chí</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a href="thongKeHoatDong.php" class="active">
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
        <h1 class="text-center mb-4">Thống kê hoạt động theo tiêu chí</h1>
        <div class="text-center mb-5">
            <h3>Tổng điểm tất cả các hoạt động: 
                <?php
                    $sql_total_points = "
                        SELECT SUM(tc.SoDiem) AS TongDiem
                        FROM hoatdong h
                        INNER JOIN tieuchi tc ON h.MaTieuChi = tc.MaTieuChi
                        WHERE h.MaSinhVien = :maSinhVien
                    ";
                    $stmt_total_points = $pdo->prepare($sql_total_points);
                    $stmt_total_points->execute(['maSinhVien' => $maSinhVien]);
                    $totalPoints = $stmt_total_points->fetchColumn();
                    echo $totalPoints ?: 0;

                    // Xác định xếp loại dựa trên tổng điểm
                    if ($totalPoints < 50) {
                        $rank = 'Yếu';
                    } elseif ($totalPoints < 65) {
                        $rank = 'Trung bình';
                    } elseif ($totalPoints < 80) {
                        $rank = 'Khá';
                    } elseif ($totalPoints < 90) {
                        $rank = 'Giỏi';
                    } else {
                        $rank = 'Xuất sắc';
                    }
                    echo " Xếp loại: " . $rank;
                ?>
                
            </h3>
        </div>
        <div class="accordion" id="accordionCategories">
            <?php foreach ($categories as $maDanhMuc => $criteria):
                // Tính tổng điểm của danh mục
                $tongDiemDanhMuc = array_reduce($criteria, function ($carry, $item) {
                    return $carry + $item['TongDiemDanhMuc'];
                }, 0);
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-<?= $maDanhMuc ?>">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-<?= $maDanhMuc ?>" aria-expanded="true"
                            aria-controls="collapse-<?= $maDanhMuc ?>">
                            <?= htmlspecialchars($criteria[0]['TenDanhMuc']) ?> -
                            <strong>Tổng điểm: <?= $tongDiemDanhMuc ?></strong>
                        </button>
                    </h2>
                    <div id="collapse-<?= $maDanhMuc ?>" class="accordion-collapse collapse"
                        aria-labelledby="heading-<?= $maDanhMuc ?>" data-bs-parent="#accordionCategories">
                        <div class="accordion-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tiêu chí</th>
                                        <th>Hoạt động</th>
                                        <th>Xem chi tiết</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($criteria as $criterion): ?>
                                        <tr>
                                            <td class="w-75"><?= htmlspecialchars($criterion['TenTieuChi']) ?></td>
                                            <td>
                                                <?= isset($activityCounts[$criterion['MaTieuChi']])
                                                    ? "Có (" . $activityCounts[$criterion['MaTieuChi']] . " hoạt động)"
                                                    : "Không có"
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (isset($activityCounts[$criterion['MaTieuChi']])): ?>
                                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modal-<?= $criterion['MaTieuChi'] ?>">Xem</button>

                                                    <!-- Modal hiển thị chi tiết hoạt động -->
                                                    <div class="modal fade" id="modal-<?= $criterion['MaTieuChi'] ?>" tabindex="-1"
                                                        aria-labelledby="modalLabel-<?= $criterion['MaTieuChi'] ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalLabel-<?= $criterion['MaTieuChi'] ?>">
                                                                        Hoạt động thuộc tiêu chí <?= htmlspecialchars($criterion['TenTieuChi']) ?>
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <?php
                                                                    // Lấy danh sách hoạt động thuộc tiêu chí
                                                                    $sql_activity_details = "
                                                                        SELECT h.TenHoatDong, h.DiaDiem, h.ThoiGianBatDau, h.ThoiGianKetThuc, h.LinkMinhChung
                                                                        FROM hoatdong h
                                                                        WHERE h.MaSinhVien = :maSinhVien AND h.MaTieuChi = :maTieuChi
                                                                    ";
                                                                    $stmt_activity_details = $pdo->prepare($sql_activity_details);
                                                                    $stmt_activity_details->execute([
                                                                        'maSinhVien' => $maSinhVien,
                                                                        'maTieuChi' => $criterion['MaTieuChi']
                                                                    ]);
                                                                    $activityDetails = $stmt_activity_details->fetchAll(PDO::FETCH_ASSOC);
                                                                    ?>

                                                                    <?php if (!empty($activityDetails)): ?>
                                                                        <table class="table table-striped">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Tên hoạt động</th>
                                                                                    <th>Địa điểm</th>
                                                                                    <th>Thời gian</th>
                                                                                    <th>Link minh chứng</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php foreach ($activityDetails as $activity): ?>
                                                                                    <tr>
                                                                                        <td><?= htmlspecialchars($activity['TenHoatDong']) ?></td>
                                                                                        <td><?= htmlspecialchars($activity['DiaDiem']) ?></td>
                                                                                        <td><?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianBatDau'])) ?> - <?= date('H:i:s d-m-Y', strtotime($activity['ThoiGianKetThuc'])) ?></td>
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
                                                                        <p>Không có hoạt động nào thuộc tiêu chí này.</p>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Kết thúc modal -->
                                                <?php else: ?>
                                                    Không có hoạt động
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
</body>

</html>