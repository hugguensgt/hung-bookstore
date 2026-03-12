<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = '';

try {
    $stmt_user = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user_info = $stmt_user->fetch();

    if (!$user_info) {
        header('Location: logout.php');
        exit();
    }

    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt_orders->execute([$user_id]);
    $orders = $stmt_orders->fetchAll();

} catch (\PDOException $e) {
    $message = "データベースエラーが発生しました: " . $e->getMessage();
    $orders = [];
}

function getOrderItems($pdo, $order_id)
{
    $stmt = $pdo->prepare("
        SELECT oi.*, b.title 
        FROM order_items oi 
        JOIN books b ON oi.book_id = b.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>マイアカウント - HUNG BOOK STORE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .profile-container {
            max-width: 1200px;
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

        .profile-info p {
            font-size: 1.1em;
            line-height: 1.8;
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .order-history table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-history th,
        .order-history td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .order-history th {
            background-color: var(--light-gray);
            color: var(--primary-color);
            font-weight: 500;
        }

        .order-details {
            margin-top: 10px;
            padding-left: 20px;
            font-size: 0.9em;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
        }

        .checkout-btn {
            display: inline-block;
            padding: 10px 20px;
            background: var(--secondary-color);
            color: white;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            border-radius: 50px;
            transition: background-color 0.3s ease;
            text-decoration: none !important;
        }

        .checkout-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .status-processing {
            background-color: #f39c12;
        }

        .status-shipped {
            background-color: #3498db;
        }

        .status-delivered {
            background-color: #2ecc71;
        }

        .status-cancelled {
            background-color: #e74c3c;
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
                    <?= htmlspecialchars($username) ?></a>
                <a href="logout.php">ログアウト</a>
            </nav>
        </div>
    </header>

    <main class="profile-container">
        <h1>マイアカウント: <?= htmlspecialchars($username) ?></h1>

        <?php if ($message): ?>
            <p style="color: red;"><?= $message ?></p>
        <?php endif; ?>

        <div class="profile-section profile-info">
            <h2>アカウント情報</h2>
            <p><strong>ユーザー名:</strong> <?= htmlspecialchars($user_info['username']) ?></p>
            <p><strong>メールアドレス:</strong> <?= htmlspecialchars($user_info['email']) ?></p>
            <p><strong>登録日:</strong> <?= date('Y年m月d日', strtotime($user_info['created_at'])) ?></p>
            <a href="edit_profile.php" class="checkout-btn" style="margin-top: 15px;">パスワードを変更</a>
        </div>
        <div class="profile-section order-history">
            <h2>注文履歴</h2>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div
                        style="border: 1px solid var(--light-gray); margin-top: 20px; padding: 15px; border-radius: var(--border-radius);">
                        <h3>注文 #<?= $order['id'] ?>
                            <span class="status-badge 
                            <?php
                            if ($order['order_status'] == 'Pending')
                                echo 'status-processing';
                            elseif ($order['order_status'] == 'Shipped')
                                echo 'status-shipped';
                            elseif ($order['order_status'] == 'Delivered')
                                echo 'status-delivered';
                            else
                                echo 'status-cancelled';
                            ?>">
                                <?= htmlspecialchars($order['order_status']) ?>
                            </span>
                        </h3>
                        <p><strong>注文日:</strong> <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></p>
                        <p><strong>合計金額:</strong> ¥<?= number_format($order['total_amount']) ?></p>
                        <p><strong>配送先:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>

                        <h4>注文内容:</h4>
                        <ul class="order-details">
                            <?php
                            $items = getOrderItems($pdo, $order['id']);
                            foreach ($items as $item):
                                ?>
                                <li>
                                    <?= htmlspecialchars($item['title']) ?> (x<?= $item['quantity'] ?>) - @
                                    ¥<?= number_format($item['price']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>まだ注文履歴はありません。</p>
            <?php endif; ?>
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
                <p><i class="fas fa-map-marker-alt"></i> <strong>住所：</strong> 〒169-0073 東京都新宿区百人町１丁目１３−１6</p>
                <p><i class="fas fa-phone-alt"></i> <strong>Điện thoại：</strong> <a
                        href="tel:0123456789">0123-456-789</a></p>
                <p><i class="fas fa-envelope"></i> <strong>メール：</strong> <a
                        href="mailto:contact@hungbookstore.vn">contact@hungbookstore.vn</a></p>
            </div>
        </div>
    </footer>
</body>

</html>