<?php
require 'config.php';

$message = '';
$email_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $email_input = $email;

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = '<p class="error-message">有効なメールアドレスを入力してください。</p>';
  } else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
      header("Location: reset_password.php?email=" . urlencode($email));
      exit();

    } else {
      $message = '<p class="error-message">入力されたメールアドレスは登録されていません。</p>';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>パスワードを忘れた場合 - オンライン書店</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    html {
      height: 100%;
    }

    body {
      min-height: 100%;
      display: flex;
      flex-direction: column;
      margin: 0;
    }

    main {
      flex-grow: 1;
      max-width: 1200px;
      width: 100%;
      margin-left: auto;
      margin-right: auto;
      padding: 0 15px;
      box-sizing: border-box;
    }

    .auth-container {
      max-width: 400px;
      margin: auto;
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

    .auth-container input[type="email"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid var(--light-gray);
      border-radius: 4px;
      box-sizing: border-box;
      font-size: 16px;
    }

    .auth-container button {
      background: #e67e22;
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
      background: #d35400;
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
      <h2>パスワード再設定</h2>
      <p style="margin-bottom: 20px; font-size: 14px;">登録済みのメールアドレスを入力してください。</p>
      <?= $message ?>
      <form method="POST" action="forgot_password.php">
        <input type="email" name="email" placeholder="メールアドレス" required value="<?= htmlspecialchars($email_input) ?>">
        <button type="submit">メールを送信 (模擬)</button>
      </form>
      <p style="margin-top: 15px;"><a href="login.php" style="color: var(--secondary-color);">ログイン画面に戻る</a></p>
    </div>
  </main>
  <footer class="site-footer">
    <div class="footer-container">
      <div class="footer-left">
        <h3>HUNG BOOK STORE</h3>
        <p>© 2025 HUNG BOOK STORE. 全著作権所有。</p>
        <div class="footer-links">
          <a href="https://facebook.com/hungbookstore" target="_blank">
            <i class="fab fa-facebook-f"></i> Facebook
          </a>
          <a href="https://instagram.com/hungbookstore" target="_blank">
            <i class="fab fa-instagram"></i> Instagram
          </a>
          <a href="/privacy-policy">
            <i class="fas fa-file-alt"></i> プライバシーポリシー
          </a>
        </div>
      </div>

      <div class="footer-right">
        <br>
        <p><i class="fas fa-map-marker-alt"></i> <strong>住所：</strong> 〒169-0073 東京都新宿区百人町１丁目１３−１６</p>
        <p><i class="fas fa-phone-alt"></i> <strong>電話番号：</strong> <a href="tel:0123456789">0123-456-789</a></p>
        <p><i class="fas fa-envelope"></i> <strong>メール：</strong> <a
            href="mailto:contact@hungbookstore.vn">contact@hungbookstore.vn</a></p>
      </div>
    </div>
  </footer>
</body>

</html>