<?php
require '../config.php'; // Quay lại thư mục gốc để include config.php

// Kiểm tra nếu đã đăng nhập admin thì chuyển hướng
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$username_input = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $username_input = $username;

    if (empty($username) || empty($password)) {
        $message = 'ユーザー名とパスワードを入力してください。';
    } else {
        // Lấy thông tin người dùng, KIỂM TRA VAI TRÒ (ROLE)
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Kiểm tra mật khẩu VÀ vai trò phải là 'admin'
        if ($user && password_verify($password, $user['password']) && $user['role'] === 'admin') {

            // *** SỬA LẠI BIẾN SESSION CHO NHẤT QUÁN ***
            session_regenerate_id(true); // Tăng cường bảo mật session
            $_SESSION['user_id'] = $user['id'];         // Dùng user_id chung
            $_SESSION['username'] = $user['username'];   // Dùng username chung
            $_SESSION['user_role'] = $user['role'];     // Đặt user_role là 'admin'

            // Bỏ các biến session cũ không cần thiết
            // unset($_SESSION['admin_id']); 
            // unset($_SESSION['admin_username']);

            header('Location: dashboard.php');
            exit();
        } else {
            $message = '認証情報が正しくないか、管理者権限がありません。';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>管理者ログイン</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>管理者ログイン</h2>
        <?php if ($message): ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <input type="text" name="username" placeholder="ユーザー名" required
                value="<?= htmlspecialchars($username_input) ?>">
            <input type="password" name="password" placeholder="パスワード" required>
            <button type="submit">ログイン</button>
        </form>
    </div>
</body>

</html>