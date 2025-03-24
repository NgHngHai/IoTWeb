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
                            <img id="avatarPreview" src="./img/nhh.png" alt="Avatar" class="avatar">
                        </label>
                    </div>
                    <form method="" class="mt-3">
                    <input type="file" id="avatarInput" name="avatar" accept="image/*">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên</label>
                                <input type="text" name="name" class="form-control" value="Nguyễn Hồng Hải" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="B21DCPT096" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="mail" class="form-control" value="honghaiae@gmail.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="0866152895" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Github</label>
                                <a href="https://github.com/NgHngHai/IoTWeb">github.com/NgHngHai/IoTWeb</a>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">API Docs</label>
                                <a href="https://github.com/NgHngHai/IoTWeb">API Docs</a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Báo cáo</label>
                                <a href="https://github.com/NgHngHai/IoTWeb">Report</a>
                            </div>
                        </div>
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
