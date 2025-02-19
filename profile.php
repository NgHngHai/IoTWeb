<?php
session_start();
require 'db_connect.php'; // Include your database connection file

// Assuming user ID is stored in session after login
$user_id = 1;

// Fetch user data from database
$sql = "SELECT name, img, mail, phone, username, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $mail = $_POST['mail'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Handle file upload
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "img/";
        $target_file = $target_dir . uniqid() . "_" . basename($_FILES["avatar"]["name"]);
        
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $img = $target_file;
            $updateImgQuery = "UPDATE users SET img=? WHERE id=?";
            $stmt = $conn->prepare($updateImgQuery);
            $stmt->bind_param("si", $img, $user_id);
            $stmt->execute();
        }
    } else {
        $img = $user['img'];
    }

    $query = "UPDATE users SET name=?, mail=?, phone=?, username=?, img=?" . ($password ? ", password=?" : "") . " WHERE id=?";
    $stmt = $conn->prepare($query);
    if ($password) {
        $stmt->bind_param("ssssssi", $name, $mail, $phone, $username, $img, $password, $user_id);
    } else {
        $stmt->bind_param("sssssi", $name, $mail, $phone, $username, $img, $user_id);
    }
    $stmt->execute();
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/profile.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'navbar.php'; ?>
            <main class="content">
                <div class="container mt-5">
                    <div class="text-center">
                        <label for="avatarInput" class="avatar-container">
                            <img id="avatarPreview" src="<?php echo !empty($user['img']) ? htmlspecialchars($user['img']) : 'default-avatar.png'; ?>" alt="Avatar" class="avatar">
                        </label>
                    </div>
                    <form method="post" enctype="multipart/form-data" class="mt-3">
                    <input type="file" id="avatarInput" name="avatar" accept="image/*">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="mail" class="form-control" value="<?= htmlspecialchars($user['mail']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Đổi mật khẩu</label>
                                <input type="password" name="password" class="form-control" placeholder="Để trống để giữ mật khẩu cũ">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vai trò</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Github</label>
                                <a href="https://github.com/NgHngHai/IoTWeb">github.com/NgHngHai/IoTWeb</a>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save</button>
                    </form>
                </div>
            </main>
    </div>
    <script>
        document.getElementById("avatarInput").addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("avatarPreview").src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>
