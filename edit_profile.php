<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$user_info = null;

try {
  $stmt = $pdo->prepare("SELECT username, email, password FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user_info = $stmt->fetch();

} catch (\PDOException $e) {
  $message = "データベースエラーが発生しました: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_info) {
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if (!password_verify($current_password, $user_info['password'])) {
    $message = '<p style="color:red;">現在のパスワードが間違っています。</p>';
  }
  elseif (strlen($new_password) < 6) {
    $message = '<p style="color:red;">新しいパスワードは6文字以上で入力してください。</p>';
  }
  elseif ($new_password !== $confirm_password) {
    $message = '<p style="color:red;">新しいパスワードと確認用パスワードが一致しません。</p>';
  }
  else {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt_update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmt_update->execute([$hashed_password, $user_id])) {
      $message = '<p style="color:green;">パスワードが正常に更新されました！</p>';
    } else {
      $message = '<p style="color:red;">パスワードの更新中にエラーが発生しました。</p>';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>パスワード変更 - HUNG BOOK STORE</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .profile-container {
      max-width: 600px;
      margin: 30px auto;
      padding: 20px;
    }

    .profile-section {
      background: var(--card-background);
      padding: 30px;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      margin-bottom: 30px;
    }

    .profile-section h2 {
      color: var(--primary-color);
      border-bottom: 1px solid var(--light-gray);
      padding-bottom: 10px;
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }

    .form-group input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid var(--light-gray);
      border-radius: 4px;
      box-sizing: border-box;
    }

    .btn-update {
      display: block;
      width: 100%;
      padding: 12px;
      background: #2ecc71;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s;
    }

    .btn-update:hover {
      background: #27ae60;
    }

    .back-link {
      margin-top: 20px;
      display: block;
      text-align: center;
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
        <a href="cart.php">カート
          <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            (<?= count($_SESSION['cart']) ?>)
          <?php endif; ?>
        </a>
        <a href="profile.php" style="color: var(--secondary-color);"><i class="fas fa-user"></i>
          <?= htmlspecialchars($_SESSION['username']) ?></a>
        <a href="logout.php">ログアウト</a>
      </nav>
    </div>
  </header>

  <main class="profile-container">

    <div class="profile-section">
      <h2>パスワード変更</h2>
      <?= $message ?>

      <form method="POST" action="edit_profile.php">
        <div class="form-group">
          <label for="current_password">現在のパスワード:</label>
          <input type="password" id="current_password" name="current_password" required>
        </div>

        <div class="form-group">
          <label for="new_password">新しいパスワード:</label>
          <input type="password" id="new_password" name="new_password" required>
        </div>

        <div class="form-group">
          <label for="confirm_password">新しいパスワード (確認用):</label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn-update">パスワードを更新する</button>
      </form>

      <a href="profile.php" class="back-link">アカウント情報に戻る</a>
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