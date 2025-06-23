<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông báo chưa đọc
$stmt = $pdo->prepare("
    SELECT notifications.*, users.username 
    FROM notifications 
    JOIN users ON notifications.from_user_id = users.id
    WHERE notifications.user_id = ?
    ORDER BY notifications.created_at DESC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Đánh dấu đã đọc
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔔 Thông báo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>🔔 Thông báo của bạn</h2>
    <ul>
        <?php foreach ($notifications as $n): ?>
            <li>
                <strong><?= htmlspecialchars($n['username']) ?></strong>
                <?= $n['type'] === 'like' ? 'đã thích' : 'đã bình luận vào' ?>
                <a href="index.php#post-<?= $n['post_id'] ?>">bài viết của bạn</a>
                – <?= $n['created_at'] ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="index.php">⬅️ Quay lại bảng tin</a>
</div>
</body>
</html>
