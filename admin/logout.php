<?php
// QUAN TRỌNG: Đảm bảo config.php gọi session_start()
require '../config.php';

// Xóa các biến session liên quan đến đăng nhập (admin hoặc user)
unset($_SESSION['user_id']);
unset($_SESSION['username']);
unset($_SESSION['user_role']);

// Hủy toàn bộ session (tùy chọn, an toàn hơn)
// session_destroy(); // Nếu dùng dòng này, giỏ hàng cũng sẽ mất

// *** SỬA LỖI: Chuyển hướng đến trang đăng nhập admin ***
header('Location: login.php');
exit(); // Luôn thêm exit() sau header Location
?>