<?php
/* login.php */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';           // $pdo = new PDO(...);

/* Nếu đã login rồi -> về thẳng home2.php */
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? "");
    $password = trim($_POST['password'] ?? "");

    /* Lấy hash từ CSDL */
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($u && password_verify($password, $u['password_hash'])) {
        /* Đăng nhập OK */
        $_SESSION['user_id']   = $u['id'];
        $_SESSION['username']  = $username;
        header("Location: home2.php");
        exit();
    } else {
        $err = "❌ Sai tên đăng nhập hoặc mật khẩu";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập • Social Pixel</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* chỉ demo: bạn có thể chuyển block này vào style.css */
    .login-box{max-width:360px;margin:60px auto;padding:32px;border-radius:16px;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.05)}
    .login-box h1{margin-bottom:24px;font-size:24px}
    .login-box input{width:100%;padding:10px 14px;margin-bottom:14px;border:1px solid #ddd;border-radius:8px}
    .login-box button{width:100%;padding:12px 0;border:none;border-radius:8px;background:var(--primary);color:#fff;font-weight:600;cursor:pointer}
    .error{color:red;margin-bottom:12px;font-size:14px}
  </style>
</head>
<body>
  <div class="login-box">
    <h1>Đăng nhập</h1>

    <?php if ($err): ?><div class="error"><?= $err ?></div><?php endif;?>

    <form method="POST">
      <input type="text"     name="username" placeholder="Tên người dùng" required>
      <input type="password" name="password" placeholder="Mật khẩu"      required>
      <button type="submit">Đăng nhập</button>
    </form>
  </div>
</body>
</html>
