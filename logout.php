<?php
session_start();          // khởi động session
session_unset();          // xóa toàn bộ biến session
session_destroy();        // hủy session
header("Location: login.php"); // quay về trang đăng nhập
exit();

