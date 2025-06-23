<?php
session_start();
require_once "db.php";

// Nếu chưa đăng nhập, chuyển hướng về trang login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];  // Tên người dùng hiện tại cho hiển thị

$message = "";

// Xử lý form đăng bài mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Đăng bài viết mới
    if (isset($_POST['create_post'])) {
        $content = trim($_POST['post_content'] ?? "");
        if (!empty($content)) {
            // Xử lý upload ảnh (nếu người dùng chọn ảnh)
            $imagePath = "";
            if (!empty($_FILES['post_image']['name'])) {
                $imageName = basename($_FILES['post_image']['name']);
                // Tạo đường dẫn lưu ảnh vào thư mục post_images
                $targetPath = "post_images/" . $imageName;
                move_uploaded_file($_FILES['post_image']['tmp_name'], $targetPath);
                $imagePath = $imageName;
            }
            // Chèn bài viết mới vào CSDL
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_path, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $content, $imagePath]);
            // Thông báo thành công
            $message = "<div class='success-message'>✅ Đã đăng bài viết thành công!</div>";
        }
    }

    // 2. Xử lý Like/Unlike bài viết
    if (isset($_POST['like_post'])) {
        $postId = intval($_POST['post_id']);
        // Kiểm tra bài viết đã được like bởi user chưa
        $check = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
        $check->execute([$userId, $postId]);
        if ($check->rowCount() > 0) {
            // Nếu đã like rồi thì xóa (unlike)
            $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?")->execute([$userId, $postId]);
        } else {
            // Nếu chưa like thì thêm like mới
            $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)")->execute([$userId, $postId]);
        }
        // Thêm thông báo cho chủ bài viết khi có like (nếu người like không phải chủ bài)
        $ownerStmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
        $ownerStmt->execute([$postId]);
        $ownerId = $ownerStmt->fetchColumn();
        if ($ownerId && $ownerId != $userId) {
            // Tránh trùng lặp thông báo like (chỉ tạo nếu chưa có thông báo này)
            $dup = $pdo->prepare("SELECT id FROM notifications 
                                   WHERE user_id = ? AND from_user_id = ? AND post_id = ? AND type = 'like'");
            $dup->execute([$ownerId, $userId, $postId]);
            if ($dup->rowCount() === 0) {
                $pdo->prepare("INSERT INTO notifications (user_id, from_user_id, post_id, type) 
                               VALUES (?, ?, ?, 'like')")->execute([$ownerId, $userId, $postId]);
            }
        }
    }

    // 3. Xử lý gửi bình luận mới
    if (isset($_POST['comment_submit'])) {
        $postId  = intval($_POST['post_id']);
        $content = trim($_POST['comment_content'] ?? "");
        if (!empty($content)) {
            // Thêm bình luận vào CSDL
            $pdo->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)")
                ->execute([$userId, $postId, $content]);
            // Thêm thông báo cho chủ bài viết về bình luận mới (nếu khác user)
            $ownerStmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
            $ownerStmt->execute([$postId]);
            $ownerId = $ownerStmt->fetchColumn();
            if ($ownerId && $ownerId != $userId) {
                $pdo->prepare("INSERT INTO notifications (user_id, from_user_id, post_id, type) 
                               VALUES (?, ?, ?, 'comment')")->execute([$ownerId, $userId, $postId]);
            }
        }
    }
}

// Lấy danh sách bài viết mới nhất kèm số like, số comment
$sql = "SELECT posts.id, posts.content, posts.image_path, posts.created_at, users.username,
               (SELECT COUNT(*) FROM likes    WHERE likes.post_id    = posts.id) AS likes,
               (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comments
        FROM posts 
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC";
$stmtPosts = $pdo->query($sql);
$posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

// Lấy một số người dùng khác để hiển thị trong phần Story (demo)
$stmtUsers = $pdo->prepare("SELECT username FROM users WHERE id != ? LIMIT 5");
$stmtUsers->execute([$userId]);
$storyUsers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Pixel Social S&P</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/style2.css">

</head>
<body>

<?php include 'nav.php'; ?>  <!-- Thanh sidebar điều hướng -->

<div class="container">
  <!-- Hiển thị thông báo thành công (nếu có) -->
  <?= $message ?>

  <!-- Phần Stories (tin 24h) -->
  <div class="stories">
    <!-- Story của chính user -->
    <div class="story" onclick="alert('Tính năng đăng tin đang được phát triển!')">
      <div class="story-avatar">+</div>
      <div class="story-name">Tin của bạn</div>
    </div>
    <!-- Story của một số người dùng khác -->
    <?php foreach ($storyUsers as $u): ?>
      <div class="story" onclick="alert('Xem tin của <?= htmlspecialchars($u['username']) ?>')">
        <div class="story-avatar"><?= strtoupper($u['username'][0]) ?></div>
        <div class="story-name"><?= htmlspecialchars($u['username']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Form đăng bài mới -->
  <div class="post-box">
    <form method="POST" enctype="multipart/form-data">
      <div style="margin-bottom: 10px; display: flex; align-items: center;">
        <!-- Ảnh đại diện user (tạm dùng ký tự tên) -->
        <div class="user-avatar"><?= strtoupper($username[0]) ?></div>
        <textarea name="post_content" class="post-input" rows="3" 
                  placeholder="Đang nghĩ gì vậy, <?= htmlspecialchars($username) ?>?" required></textarea>
      </div>
      <div class="post-actions">
        <input type="file" name="post_image" class="image-input" accept="image/*">
        <button type="submit" name="create_post" class="post-btn">Đăng</button>
      </div>
    </form>
  </div>

  <!-- Danh sách các bài post -->
  <?php foreach ($posts as $post): ?>
    <div class="post-card">
      <!-- Thông tin người đăng và thời gian -->
      <div class="author">
        <strong><?= htmlspecialchars($post['username']) ?></strong> 
        <span class="handle">@<?= htmlspecialchars($post['username']) ?></span><br>
        <small>🕒 <?= $post['created_at'] ?></small>
      </div>
      <!-- Nội dung bài viết -->
      <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
      <!-- Hình ảnh đính kèm (nếu có) -->
      <?php if (!empty($post['image_path'])): ?>
        <div class="image">
          <img src="post_images/<?= htmlspecialchars($post['image_path']) ?>" alt="post image">
        </div>
      <?php endif; ?>
      <!-- Các nút tương tác: like, comment -->
      <div class="actions">
        <!-- Nút Like -->
        <form method="POST" style="display:inline;">
          <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
          <button type="submit" name="like_post" class="action-btn like-btn">
            ❤️ <?= $post['likes'] ?>
          </button>
        </form>
        <!-- Nút hiển thị bình luận (giả lập) -->
        <button type="button" class="action-btn" onclick="alert('Xem các bình luận...')">
          💬 <?= $post['comments'] ?>
        </button>
      </div>
      <!-- Form thêm bình luận mới cho bài viết -->
      <form method="POST" class="comment-form">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        <input type="text" name="comment_content" placeholder="Viết bình luận..." required>
        <button type="submit" name="comment_submit">Gửi</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>

</body>
</html>






