<?php
require 'config.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
  header('Location: index.php');
  exit();
}
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
  unset($_SESSION['user_id']);
  unset($_SESSION['username']);
  unset($_SESSION['user_role']);
}


$message = '';
$username_input = '';
$password = '';

if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
  $message = '<p class="success-message">登録が完了しました。ログインしてください。</p>';
}

if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
  $message = '<p class="success-message">パスワードが正常に更新されました。ログインしてください。</p>';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $username_input = $username;

  if (empty($username) || empty($password)) {
    $message = '<p class="error-message">ユーザー名とパスワードを入力してください。</p>';
  } else {
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      if ($user['role'] === 'user') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        $current_session_cart = $_SESSION['cart'] ?? [];

        if (!empty($current_session_cart)) {
          $pdo->beginTransaction();
          try {
            $stmt_merge = $pdo->prepare("
                            INSERT INTO cart_items (user_id, book_id, quantity, price)
                            VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
                        ");
            foreach ($current_session_cart as $item) {
              if (is_array($item)) {
                $stmt_merge->execute([
                  $user['id'],
                  $item['id'],
                  $item['quantity'],
                  $item['price']
                ]);
              }
            }
            $pdo->commit();
          } catch (\Exception $e) {
            $pdo->rollBack();
          }
        }

        $stmt_db = $pdo->prepare("SELECT ci.book_id AS id, b.title, b.image, ci.price, ci.quantity
                                         FROM cart_items ci JOIN books b ON ci.book_id = b.id
                                         WHERE ci.user_id = ?");
        $stmt_db->execute([$user['id']]);
        $cart_items = [];
        foreach ($stmt_db->fetchAll() as $row) {
          $cart_items[$row['id']] = $row;
        }
        $_SESSION['cart'] = $cart_items;
        header('Location: index.php');
        exit();

      } else {
        $message = '<p class="error-message">管理者アカウントはこのページではログインできません。<a href="admin/login.php">管理者ログインページへ</a></p>';
      }
    } else {
      $message = '<p class="error-message">ユーザー名またはパスワードが正しくありません。</p>';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>ログイン - HUNG BOOK STORE</title>
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

    .auth-container input[type="text"],
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
      background: var(--secondary-color);
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
      background: var(--primary-color);
    }

    .error-message {
      color: #e74c3c;
      margin-bottom: 15px;
    }

    .error-message a {
      color: #e67e22;
      text-decoration: underline;
    }

    .success-message {
      color: #27ae60;
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
      <h2>ログイン</h2>
      <?= $message ?>
      <form method="POST" action="login.php">
        <input type="text" name="username" placeholder="ユーザー名" required
          value="<?= htmlspecialchars($username_input) ?>">
        <input type="password" name="password" placeholder="パスワード" required>
        <button type="submit">ログイン</button>
      </form>
      <p style="margin-top: 15px;">アカウントをお持ちでありませんか？ <a href="register.php"
          style="color: var(--secondary-color);">新規登録</a></p>
      <p style="margin-top: 5px;"><a href="forgot_password.php" style="color: #e67e22;">パスワードをお忘れですか？</a></p>
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