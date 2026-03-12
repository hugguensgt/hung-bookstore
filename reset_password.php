<?php
require 'config.php';

$email = $_GET['email'] ?? '';
$message = '';
$is_valid = false;

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $is_valid = true;
        $user_id = $user['id'];
    }
}

if (!$is_valid) {
    $message = '<p class="error-message">無効なリセットリンクです。再度手続きを行ってください。</p>';
}

if ($is_valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $message = '<p class="error-message">パスワードは6文字以上で入力してください。</p>';
    } elseif ($password !== $confirm_password) {
        $message = '<p class="error-message">パスワードが一致しません。</p>';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $user_id])) {
            header("Location: login.php?reset=true");
            exit();
        } else {
            $message = '<p class="error-message">パスワードの更新中にエラーが発生しました。</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>パスワードリセット - オンライン書店</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
        }

        .auth-container h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
        }

        .auth-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .auth-container button {
            background: #27ae60;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }

        .auth-container button:hover {
            background: #2ecc71;
        }

        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo"><a href="index.php">
                    <img src="assets/images/logo.png" alt="オンライン書店ロゴ" style="height:70px;">
                </a></div>
            <nav>
                <a href="index.php">ホーム</a>
                <a href="cart.php">カート</a>
                <a href="login.php">ログイン</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="auth-container">
            <h2>新しいパスワードを設定</h2>
            <?= $message ?>

            <?php if ($is_valid): ?>
                <form method="POST" action="reset_password.php?email=<?= urlencode($email) ?>">
                    <input type="password" name="password" placeholder="新しいパスワード (6文字以上)" required>
                    <input type="password" name="confirm_password" placeholder="パスワードを再確認" required>
                    <button type="submit">パスワードを更新</button>
                </form>
            <?php endif; ?>

            <p style="margin-top: 15px;"><a href="login.php" style="color: var(--secondary-color);">ログイン画面に戻る</a></p>
        </div>
    </main>
</body>

</html>