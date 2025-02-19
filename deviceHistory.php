<?php
require 'db_connect.php';

// Sorting
$filter_time = isset($_GET['filter_time']) ? $_GET['filter_time'] : '';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc';

// Pages
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // Default limit
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Default page
$offset = ($page - 1) * $limit;

// Toggle sort order
$new_order = $sort_order === 'asc' ? 'desc' : 'asc';

// Allowed columns for sorting
$allowed_columns = ['id', 'deviceid', 'status', 'timestamp'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'id';
}

// Query to fetch data
$sql = "SELECT id, deviceid, status, timestamp FROM devicehistory"; // Change to your actual table name
if (!empty($filter_time)) {
    $sql .= " WHERE `timestamp` LIKE '%" . $conn->real_escape_string($filter_time) . "%'";
}
$sql .= "  ORDER BY $sort_column $sort_order LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Get total records count for pagination
$count_sql = "SELECT COUNT(*) as total FROM devicehistory";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Mapping deviceid to human-readable names
$device_map = [
    "1" => "Đèn 1",
    "2" => "Đèn 2",
    "3" => "Quạt"
];

// Mapping status
$status_map = [
    "on" => "Bật",
    "off" => "Tắt"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử thiết bị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/history.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'navbar.php'; ?>
        <main class="content">
            <div class="container mt-4">
                <h2 class="mb-3">Lịch sử thiết bị</h2>
                
                 <!-- Filter Input -->
                <form method="GET" class="mb-3">
                    <div class="input-group filter">
                        <input type="text" name="filter_time" class="form-control" placeholder="Nhập thời gian" value="<?php echo htmlspecialchars($filter_time); ?>">
                        <button type="submit" class="btn btn-primary">Tìm</button>
                    </div>
                </form>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><a href="?sort=id&order=<?php echo ($sort_column === 'id' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>">ID</a></th>
                            <th><a href="?sort=deviceid&order=<?php echo ($sort_column === 'deviceid' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>">Thiết bị</a></th>
                            <th><a href="?sort=status&order=<?php echo ($sort_column === 'status' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>">Trạng thái</a></th>
                            <th><a href="?sort=timestamp&order=<?php echo ($sort_column === 'timestamp' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>">Thời gian</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["id"] . "</td>";
                                echo "<td>" . ($device_map[$row["deviceid"]] ?? "Unknown") . "</td>";
                                echo "<td>" . ($status_map[$row["status"]] ?? "Unknown") . "</td>";
                                echo "<td>" . $row["timestamp"] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>Không có dữ liệu</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Table Size Selector and Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <form method="GET">
                        <label for="limit" class="form-label">Số hàng:</label>
                        <select name="limit" id="limit" class="form-select d-inline w-auto" onchange="this.form.submit()">
                            <?php foreach ([10, 15, 20, 25, 50] as $option) : ?>
                                <option value="<?php echo $option; ?>" <?php echo $limit == $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                            
                    <!-- Pagination -->
                    <nav>
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>&limit=<?php echo $limit; ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

<?php
$conn->close();
?>