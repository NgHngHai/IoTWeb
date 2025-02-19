<?php
require 'db_connect.php'; // Include database connection

date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set to your timezone

// Handle device status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['device_id'], $_POST['status'])) {
    $deviceId = intval($_POST['device_id']);
    $newStatus = $_POST['status'] === 'on' ? 'on' : 'off';

    // Get current status
    $query = "SELECT status FROM devices WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $deviceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentStatus = $result->fetch_assoc()['status'] ?? '';

    // Update status if different
    if ($currentStatus !== $newStatus) {
        $updateStmt = $conn->prepare("UPDATE devices SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newStatus, $deviceId);
        $updateStmt->execute();

        $currentDatetime = date('Y-m-d H:i:s');
        $historyStmt = $conn->prepare("INSERT INTO devicehistory (deviceid, status, timestamp) VALUES (?, ?, ?)");
        $historyStmt->bind_param("iss", $deviceId, $newStatus, $currentDatetime);
        $historyStmt->execute();
    }
}

// Fetch the latest 25 sensor values
$dataQuery = "SELECT temp, humidity, light, timestamp FROM data ORDER BY timestamp DESC LIMIT 25";
$dataStmt = $conn->prepare($dataQuery);
$dataStmt->execute();
$dataResult = $dataStmt->get_result();

$chartData = [];
$latestData = null;

while ($row = $dataResult->fetch_assoc()) {
    $chartData[] = $row;
}

if (!empty($chartData)) {
    $latestData = $chartData[0]; // Most recent entry for display boxes
}

$chartData = array_reverse($chartData); // Reverse for chronological order

// Fetch the device statuses
$statusQuery = "SELECT status FROM devices ORDER BY id ASC";
$statusStmt = $conn->prepare($statusQuery);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();

while ($row = $statusResult->fetch_assoc()) {
    $deviceStatuses[] = $row['status'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/dashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>
    <div class="dashboard-container">
        <?php include 'navbar.php'; ?>
        <main class="content">
            <div class="sensor-boxes">
                <div class="sensor-box bg-danger text-white">Nhiệt độ:<br> <?php echo $latestData['temp'] ?? 'N/A'; ?>°C</div>
                <div class="sensor-box bg-primary text-white">Độ ẩm:<br> <?php echo $latestData['humidity'] ?? 'N/A'; ?>%</div>
                <div class="sensor-box bg-warning text-white">Ánh sáng:<br> <?php echo $latestData['light'] ?? 'N/A'; ?> Lux</div>
            </div>
            <div class="chart-controls-container">
                <div class="chart-container dashboard-element">
                    <canvas id="sensorChart"></canvas>
                </div>
                <div class="controls">
                    <?php $deviceNames = ["Đèn 1", "Đèn 2", "Quạt"]; ?>
                    <?php for ($i = 0; $i < count($deviceNames); $i++): ?>
                    <div class="control-button dashboard-element">
                        <span><?php echo $deviceNames[$i]; ?></span>
                        <div class="button-group">
                            <form method="post">
                                <input type="hidden" name="device_id" value="<?php echo $i + 1; ?>">
                                <button type="submit" name="status" value="on" class="btn btn-success <?php if ($deviceStatuses[$i] == "on") echo "current"?>">Bật</button>
                                <button type="submit" name="status" value="off" class="btn btn-danger <?php if ($deviceStatuses[$i] == "off") echo "current"?>">Tắt</button>
                            </form>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        const ctx = document.getElementById('sensorChart').getContext('2d');
        const chartData = <?php echo json_encode($chartData); ?>;
        
        const labels = chartData.map(d => new Date(d.timestamp).toLocaleTimeString());
        const tempData = chartData.map(d => d.temp);
        const humidityData = chartData.map(d => d.humidity);
        const lightData = chartData.map(d => d.light);
        const sensorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nhiệt độ (°C)',
                        data: tempData,
                        borderColor: 'red',
                        fill: false,
                        cubicInterpolationMode: 'monotone',
                        pointRadius: 3,
                        pointHoverRadius: 5

                    },
                    {
                        label: 'Độ ẩm (%)',
                        data: humidityData,
                        borderColor: 'blue',
                        fill: false,
                        cubicInterpolationMode: 'monotone',
                        pointRadius: 3,
                        pointHoverRadius: 5

                    },
                    {
                        label: 'Ánh sáng (Lux)',
                        data: lightData,
                        borderColor: 'yellow',
                        fill: false,
                        cubicInterpolationMode: 'monotone',
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            boxHeight: 1
                        }
                    }, tooltip: {
                        enabled: true
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: { drawBorder: true, drawOnChartArea: false },
                        ticks: { 
                            display: true,
                            callback: function(value, index, values) {
                                // Show every other label
                                if (index % 2 === 0) {
                                    return labels[index];
                                }
                                return '';
                            }
                         }
                    },
                    y: {
                        display: true,
                        grid: { drawBorder: true, drawOnChartArea: false },
                        ticks: { display: true }
                    }
                }
            }
        });
    </script>
    <script src="./js/dashboardDeviceBtn.js"></script>
</body>
</html>
<?php
$conn->close();
?>
