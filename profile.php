<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
session_start();
require_once "db.php";

// Lấy user_id từ URL hoặc session nếu không có
$profile_user_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];

// Lấy thông tin người dùng
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Người dùng không tồn tại.";
    exit;
}

// Lấy bài viết của người dùng này
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$profile_user_id]);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['username']) ?> のプロフィール</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="post-card">
        <div class="author">
            <img src="avatar/<?= htmlspecialchars($user['avatar']) ?>" alt="avatar" width="80">
            <div>
                <h2><?= htmlspecialchars($user['username']) ?></h2>
                <p><?= htmlspecialchars($user['email']) ?></p>
                <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                <?php if ($profile_user_id === $_SESSION['user_id']): ?>
                    <a href="edit_profile.php">✏️ Chỉnh sửa hồ sơ</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <h3>📄 Bài viết của người này</h3>
    <?php foreach ($posts as $post): ?>
        <div class="post-card">
            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
            <?php if (!empty($post['image_path'])): ?>
                <div class="image">
                    <img src="post_images/<?= htmlspecialchars($post['image_path']) ?>" alt="post image">
                </div>
            <?php endif; ?>
            <div class="actions">
                🕒 <?= $post['created_at'] ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
