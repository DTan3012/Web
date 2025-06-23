<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? 0;
$content = trim($_POST['content'] ?? '');

if (!empty($content)) {
    $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $post_id, $content]);
}
// Lấy user_id của chủ bài viết
$owner = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
$owner->execute([$post_id]);
$owner_id = $owner->fetchColumn();

if ($owner_id && $owner_id != $user_id) {
    $insert = $pdo->prepare("INSERT INTO notifications (user_id, from_user_id, post_id, type) VALUES (?, ?, ?, 'comment')");
    $insert->execute([$owner_id, $user_id, $post_id]);
}


header("Location: index.php");
exit();
