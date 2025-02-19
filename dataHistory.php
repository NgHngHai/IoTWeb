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

// Query to fetch data
$sql = "SELECT id, temp, humidity, light, timestamp FROM data"; // Change to your actual table name
$filter_column = isset($_GET['filter_column']) ? $_GET['filter_column'] : 'timestamp';

// Ensure filter_column is a valid column to prevent SQL injection
$allowed_columns = ['id', 'temp', 'humidity', 'light', 'timestamp'];
if (!in_array($filter_column, $allowed_columns)) {
    $filter_column = 'id'; // Default to timestamp if invalid
}

if (!empty($filter_time)) {
    $sql .= " WHERE `$filter_column` LIKE '%" . $conn->real_escape_string($filter_time) . "%'";
}
$sql .= "  ORDER BY $sort_column $sort_order LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Get total records count for pagination
$count_sql = "SELECT COUNT(*) as total FROM data";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử thông tin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/history.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'navbar.php'; ?>
        <main class="content">
            <div class="container mt-4">
                <h2 class="mb-3">Lịch sử thông tin</h2>
                
                <!-- Filter Input with Dropdown -->
                <form method="GET" class="mb-3">
                    <div class="input-group filter">
                        <select name="filter_column" class="form-select">
                            <?php
                            $filter_options = [
                                'id' => 'ID',
                                'temp' => 'Nhiệt độ',
                                'humidity' => 'Độ ẩm',
                                'light' => 'Ánh sáng',
                                'timestamp' => 'Thời gian'
                            ];
                            $selected_filter = isset($_GET['filter_column']) ? $_GET['filter_column'] : 'timestamp';
                            foreach ($filter_options as $key => $label) {
                                echo "<option value=\"$key\" " . ($selected_filter === $key ? 'selected' : '') . ">$label</option>";
                            }
                            ?>
                        </select>
                        <input type="text" name="filter_time" class="form-control" placeholder="Nhập giá trị cần tìm" value="<?php echo htmlspecialchars($filter_time); ?>">
                        <button type="submit" class="btn btn-primary">Tìm</button>
                    </div>
                </form>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><a href="?sort=id&order=<?php echo ($sort_column === 'id' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>">ID</a></th>
                            <th><a href="?sort=temp&order=<?php echo ($sort_column === 'temp' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>">Nhiệt độ</a></th>
                            <th><a href="?sort=humidity&order=<?php echo ($sort_column === 'humidity' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>">Độ ẩm</a></th>
                            <th><a href="?sort=light&order=<?php echo ($sort_column === 'light' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>">Ánh sáng</a></th>
                            <th><a href="?sort=timestamp&order=<?php echo ($sort_column === 'timestamp' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>">Thời gian</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["id"] . "</td>";
                                echo "<td>" . $row["temp"] . "</td>";
                                echo "<td>" . $row["humidity"] . "</td>";
                                echo "<td>" . $row["light"] . "</td>";
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
                            <!-- First Page -->
                            <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>&limit=<?php echo $limit; ?>&page=1">
                                    « Trang đầu
                                </a>
                            </li>

                            <!-- Previous Page -->
                            <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>&limit=<?php echo $limit; ?>&page=<?php echo max(1, $page - 1); ?>">
                                    ‹ Trang trước
                                </a>
                            </li>

                            <?php
                            if ($total_pages <= 5) {
                                // Show all pages if total pages are 5 or less
                                for ($i = 1; $i <= $total_pages; $i++) {
                                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
                                            <a class="page-link" href="?sort=' . $sort_column . '&order=' . $sort_order . '&limit=' . $limit . '&page=' . $i . '">' . $i . '</a>
                                          </li>';
                                }
                            } else {
                                // Determine start and end of the range
                                if ($page <= 2) {
                                    $start = 1;
                                    $end = 4;
                                } elseif ($page >= $total_pages - 1) {
                                    $start = $total_pages - 3;
                                    $end = $total_pages;
                                } else {
                                    $start = $page - 1;
                                    $end = $page + 2;
                                }
                            
                                // Ensure it doesn't go out of bounds
                                $start = max(1, $start);
                                $end = min($total_pages, $end);
                            
                                // Show first page if it's not already included
                                if ($start > 1) {
                                    echo '<li class="page-item">
                                            <a class="page-link" href="?sort=' . $sort_column . '&order=' . $sort_order . '&limit=' . $limit . '&page=1">1</a>
                                          </li>';
                                    if ($start > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                            
                                // Generate page numbers
                                for ($i = $start; $i <= $end; $i++) {
                                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
                                            <a class="page-link" href="?sort=' . $sort_column . '&order=' . $sort_order . '&limit=' . $limit . '&page=' . $i . '">' . $i . '</a>
                                          </li>';
                                }
                            
                                // Show ellipsis before the last page if there's a gap
                                if ($end < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            
                                // Show last page
                                if ($end < $total_pages) {
                                    echo '<li class="page-item ' . ($total_pages == $page ? 'active' : '') . '">
                                            <a class="page-link" href="?sort=' . $sort_column . '&order=' . $sort_order . '&limit=' . $limit . '&page=' . $total_pages . '">' . $total_pages . '</a>
                                          </li>';
                                }
                            }
                            ?>

                            <!-- Next Page -->
                            <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>&limit=<?php echo $limit; ?>&page=<?php echo min($total_pages, $page + 1); ?>">
                                    Trang tiếp ›
                                </a>
                            </li>
                        
                            <!-- Last Page -->
                            <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>&limit=<?php echo $limit; ?>&page=<?php echo $total_pages; ?>">
                                    Trang cuối »
                                </a>
                            </li>
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