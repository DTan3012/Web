<?php
require_once "db.php"; // kết nối CSDL
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];

    if ($password !== $confirm) {
        die("Mật khẩu xác nhận không khớp.");
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hash);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        echo "Lỗi đăng ký: " . $stmt->error;
    }
}
?>

<!-- Giao diện đơn giản -->
<form method="POST" action="">
    <h2>Đăng ký</h2>
    <input name="username" placeholder="Tên người dùng" required><br>
    <input name="email" type="email" placeholder="Email" required><br>
    <input name="password" type="password" placeholder="Mật khẩu" required><br>
    <input name="confirm" type="password" placeholder="Xác nhận mật khẩu" required><br>
    <button type="submit">Đăng ký</button>
</form>
