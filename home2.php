<?php
// SocialSphere - Social Media Platform (No Database)
session_start();

// Initialize posts in session if not exists
if (!isset($_SESSION['posts'])) {
    $_SESSION['posts'] = [
        [
            'id' => 1,
            'username' => 'Sarah Johnson',
            'handle' => '@sarah_adventures',
            'content' => 'Just had the most amazing sunset hike! Nature never fails to inspire me 🌅',
            'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=400&fit=crop',
            'likes' => 142,
            'comments' => 23,
            'timestamp' => time() - 86400 // 1 day ago
        ],
        [
            'id' => 2,
            'username' => 'Mike Chen',
            'handle' => '@mike_photography',
            'content' => 'Captured this beautiful moment in the city today! Street photography is my passion 📸',
            'image' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&h=400&fit=crop',
            'likes' => 89,
            'comments' => 15,
            'timestamp' => time() - 43200 // 12 hours ago
        ],
        [
            'id' => 3,
            'username' => 'Emma Wilson',
            'handle' => '@emma_foodie',
            'content' => 'Made this delicious pasta from scratch! Cooking is therapy for the soul 🍝',
            'image' => 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=800&h=400&fit=crop',
            'likes' => 67,
            'comments' => 12,
            'timestamp' => time() - 21600 // 6 hours ago
        ]
    ];
}

// Sample users data
$users = [
    ['username' => 'Sarah', 'handle' => '@sarah_adventures'],
    ['username' => 'Mike', 'handle' => '@mike_photography'],
    ['username' => 'Emma', 'handle' => '@emma_foodie'],
    ['username' => 'Alex', 'handle' => '@alex_travel']
];

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_post']) && !empty(trim($_POST['post_content']))) {
        $new_post = [
            'id' => count($_SESSION['posts']) + 1,
            'username' => 'You',
            'handle' => '@your_handle',
            'content' => trim($_POST['post_content']),
            'image' => !empty($_POST['post_image']) ? $_POST['post_image'] : '',
            'likes' => 0,
            'comments' => 0,
            'timestamp' => time()
        ];
        
        // Add to beginning of posts array
        array_unshift($_SESSION['posts'], $new_post);
        $message = '<div class="success-message">Post created successfully! 🎉</div>';
    }
    
    // Handle like action
    if (isset($_POST['like_post'])) {
        $post_id = (int)$_POST['post_id'];
        foreach ($_SESSION['posts'] as &$post) {
            if ($post['id'] == $post_id) {
                $post['likes']++;
                break;
            }
        }
    }
}

// Helper function to format time
function timeAgo($timestamp) {
    $time = time() - $timestamp;
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 604800) return floor($time/86400) . 'd ago';
    return date('M j', $timestamp);
}

// Trending topics
$trending_topics = ['#TechLife', '#Photography', '#Travel', '#Food', '#Nature', '#Coding'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialSphere</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .search-bar {
            flex: 1;
            max-width: 400px;
            margin: 0 30px;
        }

        .search-bar input {
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            background: rgba(0, 0, 0, 0.05);
            font-size: 16px;
            outline: none;
        }

        .notification-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            font-size: 16px;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            gap: 20px;
        }

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            height: fit-content;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 100px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin: 8px 0;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .nav-item.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            transform: translateX(5px);
        }

        .nav-item:hover:not(.active) {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(5px);
        }

        .nav-icon {
            margin-right: 12px;
            font-size: 18px;
        }

        .trending-topics {
            margin-top: 30px;
        }

        .trending-topics h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
        }

        .topic-tag {
            display: inline-block;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            margin: 5px 5px 5px 0;
            font-size: 14px;
            cursor: pointer;
            transition: transform 0.2s ease;
            text-decoration: none;
        }

        .topic-tag:hover {
            transform: scale(1.05);
        }

        .main-content {
            flex: 1;
            max-width: 600px;
        }

        .stories {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            overflow-x: auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .story {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            min-width: 70px;
            transition: transform 0.2s ease;
        }

        .story:hover {
            transform: translateY(-3px);
        }

        .story-avatar {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .story-name {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }

        .post-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 15px;
        }

        .post-input-container {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .post-input {
            flex: 1;
            border: none;
            background: rgba(0, 0, 0, 0.02);
            font-size: 16px;
            padding: 15px 20px;
            border-radius: 15px;
            resize: none;
            outline: none;
            font-family: inherit;
        }

        .post-input::placeholder {
            color: #999;
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .image-input {
            flex: 1;
            border: none;
            background: rgba(0, 0, 0, 0.02);
            padding: 12px 20px;
            border-radius: 15px;
            outline: none;
            font-size: 14px;
        }

        .post-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .post-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .post-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .posts-feed {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .post {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .post:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .post-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
            font-size: 18px;
        }

        .post-info h4 {
            margin: 0 0 4px 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }

        .post-info .handle {
            color: #666;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .post-info .time {
            color: #999;
            font-size: 13px;
        }

        .post-content {
            margin-bottom: 15px;
            line-height: 1.6;
            color: #333;
            font-size: 16px;
        }

        .post-image {
            width: 100%;
            border-radius: 15px;
            margin-bottom: 15px;
            max-height: 400px;
            object-fit: cover;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .action-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #666;
            font-weight: 500;
        }

        .action-btn:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            transform: translateY(-1px);
        }

        .like-btn:hover {
            color: #ff4757;
        }

        .success-message {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                position: static;
            }
            
            .header {
                padding: 10px 15px;
                flex-direction: column;
                gap: 15px;
            }
            
            .search-bar {
                margin: 0;
                max-width: none;
            }
            
            .stories {
                padding: 15px;
            }
            
            .post-form, .post {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SocialSphere</div>
        <div class="search-bar">
            <input type="text" placeholder="Search..." id="searchInput">
        </div>
        <button class="notification-btn" onclick="showNotifications()">
            🔔
            <span class="notification-badge">1</span>
        </button>
    </div>

    <div class="container">
        <div class="sidebar">
            <nav>
                <a href="#" class="nav-item active">
                    <span class="nav-icon">🏠</span> Home
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">👤</span> Profile
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">⚡</span> Explore
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">👥</span> Friends
                </a>
            </nav>

            <div class="trending-topics">
                <h3>Trending Topics</h3>
                <?php foreach ($trending_topics as $topic): ?>
                    <a href="#" class="topic-tag"><?php echo $topic; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="main-content">
            <?php echo $message; ?>

            <!-- Stories Section -->
            <div class="stories">
                <div class="story" onclick="createStory()">
                    <div class="story-avatar">+</div>
                    <div class="story-name">Your Story</div>
                </div>
                <?php foreach ($users as $user): ?>
                    <div class="story" onclick="viewStory('<?php echo $user['username']; ?>')">
                        <div class="story-avatar"><?php echo strtoupper($user['username'][0]); ?></div>
                        <div class="story-name"><?php echo $user['username']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Post Creation Form -->
            <div class="post-form">
                <form method="POST" id="postForm">
                    <div class="post-input-container">
                        <div class="user-avatar">Y</div>
                        <textarea class="post-input" name="post_content" placeholder="What's on your mind, You?" rows="3" required></textarea>
                    </div>
                    <div class="post-actions">
                        <input type="url" name="post_image" class="image-input" placeholder="Add image URL (optional)">
                        <button type="submit" name="create_post" class="post-btn">Post</button>
                    </div>
                </form>
            </div>

            <!-- Posts Feed -->
            <div class="posts-feed">
                <?php foreach ($_SESSION['posts'] as $post): ?>
                    <div class="post">
                        <div class="post-header">
                            <div class="post-avatar"><?php echo strtoupper($post['username'][0]); ?></div>
                            <div class="post-info">
                                <h4><?php echo htmlspecialchars($post['username']); ?></h4>
                                <div class="handle"><?php echo htmlspecialchars($post['handle']); ?></div>
                                <div class="time"><?php echo timeAgo($post['timestamp']); ?></div>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                        
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post image" class="post-image">
                        <?php endif; ?>
                        
                        <div class="post-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="like_post" class="action-btn like-btn">
                                    ❤️ <?php echo $post['likes']; ?>
                                </button>
                            </form>
                            <button class="action-btn" onclick="showComments(<?php echo $post['id']; ?>)">
                                💬 <?php echo $post['comments']; ?>
                            </button>
                            <button class="action-btn" onclick="sharePost(<?php echo $post['id']; ?>)">
                                📤 Share
                            </button>
                            <button class="action-btn" onclick="savePost(<?php echo $post['id']; ?>)">
                                🔖 Save
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Add some interactivity
        function showNotifications() {
            alert('🔔 You have 1 new notification:\n\nSarah Johnson liked your post!');
        }

        function createStory() {
            alert('📸 Story creation feature coming soon!');
        }

        function viewStory(username) {
            alert(`👀 Viewing ${username}'s story...`);
        }

        function showComments(postId) {
            alert(`💬 Comments for post ${postId} will be shown here.`);
        }

        function sharePost(postId) {
            alert(`📤 Post ${postId} shared!`);
        }

        function savePost(postId) {
            alert(`🔖 Post ${postId} saved to your collection!`);
        }

        // Add real-time character count
        const postInput = document.querySelector('.post-input');
        const postBtn = document.querySelector('.post-btn');

        postInput.addEventListener('input', function() {
            const length = this.value.trim().length;
            if (length === 0) {
                postBtn.disabled = true;
                postBtn.textContent = 'Post';
            } else {
                postBtn.disabled = false;
                postBtn.textContent = `Post (${length})`;
            }
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                alert(`🔍 Searching for: "${this.value}"`);
            }
        });

        // Navigation interactivity
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Auto-refresh posts every 30 seconds (simulation)
        setInterval(function() {
            // In a real app, this would fetch new posts
            console.log('🔄 Checking for new posts...');
        }, 30000);
    </script>
</body>
</html>