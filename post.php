<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include('db.php'); ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $content = $_POST['content'];
    $imagePath = '';

    if (!empty($_FILES['image']['name'])) {
        $imagePath = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "post_images/" . $imagePath);
    }

    $stmt = $pdo->prepare("INSERT INTO posts (username, content, image_path) VALUES (?, ?, ?)");
    $stmt->execute([$username, $content, $imagePath]);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規投稿</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>📤 新規投稿</h1>
    <form action="post.php" method="post" enctype="multipart/form-data">
        <label>名前: <input type="text" name="username" required></label><br><br>
        <label>内容: <textarea name="content" rows="4" cols="40" required></textarea></label><br><br>
        <label>画像: <input type="file" name="image"></label><br><br>
        <button type="submit">投稿する</button>
    </form>
    <p><a href="index.php">一覧に戻る</a></p>
</body>
</html>