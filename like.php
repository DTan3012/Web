<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? 0;

// Kiểm tra nếu đã like → unlike, chưa like → like
$stmt = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->execute([$user_id, $post_id]);

if ($stmt->rowCount() > 0) {
    // Đã like rồi → unlike
    $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?")->execute([$user_id, $post_id]);
} else {
    // Like mới
    $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)")->execute([$user_id, $post_id]);
}
// Lấy user_id của chủ bài viết
$owner = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
$owner->execute([$post_id]);
$owner_id = $owner->fetchColumn();

// Nếu người like khác người đăng → tạo thông báo
if ($owner_id && $owner_id != $user_id) {
    $check = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND from_user_id = ? AND post_id = ? AND type = 'like'");
    $check->execute([$owner_id, $user_id, $post_id]);

    if ($check->rowCount() === 0) {
        $insert = $pdo->prepare("INSERT INTO notifications (user_id, from_user_id, post_id, type) VALUES (?, ?, ?, 'like')");
        $insert->execute([$owner_id, $user_id, $post_id]);
    }
}


header("Location: index.php");
exit();
