<?php
require 'config.php';

// 1. KIỂM TRA ĐĂNG NHẬP VÀ GIỎ HÀNG
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header('Location: cart.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = $_SESSION['cart'];
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$message = '';
$is_success = false;

// 2. XỬ LÝ THANH TOÁN KHI FORM ĐƯỢC GỬI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['shipping_address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    if (empty($address) || empty($payment_method)) {
        $message = '配送先住所と支払い方法を選択してください。';
    } else {
        // Mở giao dịch (Transaction) để đảm bảo dữ liệu nhất quán
        $pdo->beginTransaction();

        try {
            // A. LƯU VÀO BẢNG `orders`
            $stmt_order = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, shipping_address, order_status)
                VALUES (?, ?, ?, 'Pending')
            ");
            $stmt_order->execute([$user_id, $total, $address]);
            $order_id = $pdo->lastInsertId();

            // B. LƯU VÀO BẢNG `order_items`
            $stmt_item = $pdo->prepare("
                INSERT INTO order_items (order_id, book_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($cart_items as $item) {
                $stmt_item->execute([
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            // C. KẾT THÚC GIAO DỊCH VÀ XÓA GIỎ HÀNG
            $pdo->commit();
            unset($_SESSION['cart']); // Xóa giỏ hàng sau khi đặt hàng thành công

            $message = 'ご注文が完了しました！注文番号: #' . $order_id;
            $is_success = true;

        } catch (\Exception $e) {
            $pdo->rollBack(); // Hoàn tác nếu có lỗi
            $message = "注文処理中にエラーが発生しました: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>チェックアウト - HUNG BOOK STORE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .checkout-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            display: flex;
            gap: 30px;
        }

        .checkout-form,
        .order-summary {
            flex: 1;
            background: var(--card-background);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .checkout-form h2,
        .order-summary h2 {
            color: var(--primary-color);
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--light-gray);
            padding-bottom: 10px;
        }

        .checkout-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .checkout-form textarea,
        .checkout-form input[type="radio"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            box-sizing: border-box;
        }

        .payment-options {
            display: flex;
            /* Sử dụng Flexbox để căn chỉnh các mục */
            flex-direction: column;
            /* Xếp các mục theo chiều dọc */
            gap: 10px;
            /* Khoảng cách giữa các lựa chọn */
            margin-bottom: 20px;
            /* Khoảng cách với nút bấm */
        }

        .payment-option-item label {
            display: flex;
            /* Biến label thành flex container */
            align-items: center;
            /* Căn input radio và chữ ở giữa */
            font-weight: normal;
            cursor: pointer;
        }

        .payment-option-item input[type="radio"] {
            width: auto;
            /* Để input radio không chiếm 100% chiều rộng */
            margin-right: 8px;
            /* Khoảng cách giữa nút radio và chữ */
            margin-bottom: 0;
            /* Loại bỏ margin-bottom mặc định */
        }

        /* Style làm mờ tùy chọn bị disabled */
        .disabled-option {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .payment-options label {
            display: inline-block;
            margin-right: 20px;
            font-weight: normal;
        }

        .final-total {
            font-size: 1.5em;
            font-weight: bold;
            color: #e74c3c;
            text-align: right;
            margin-top: 20px;
        }

        .place-order-btn {
            background: #27ae60;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .place-order-btn:hover {
            background: #2ecc71;
        }

        .item-list li {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed var(--light-gray);
        }

        .item-list {
            list-style: none;
            padding: 0;
        }

        .success-panel {
            text-align: center;
            padding: 50px;
            background-color: #e8f8f5;
            border: 2px solid #2ecc71;
            border-radius: 8px;
        }

        .success-panel h2 {
            color: #2ecc71;
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
                <a href="profile.php"><i class="fas fa-user"></i>
                    <?= htmlspecialchars($_SESSION['username'] ?? 'ゲスト') ?></a>
                <a href="logout.php">ログアウト</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="checkout-container">

            <?php if ($is_success): ?>
                <div class="checkout-form" style="flex: 2; margin: auto;">
                    <div class="success-panel">
                        <h2>🎉 注文完了！ 🎉</h2>
                        <p style="font-size: 1.1em; color: var(--text-color);"><?= htmlspecialchars($message) ?></p>
                        <p>ご注文の詳細は、マイアカウントの注文履歴からご確認いただけます。</p>
                        <a href="profile.php" class="place-order-btn"
                            style="width: auto; padding: 10px 20px; background: var(--secondary-color);">注文履歴へ</a>
                        <a href="index.php" class="place-order-btn"
                            style="width: auto; padding: 10px 20px; background: #95a5a6;">お買い物を続ける</a>
                    </div>
                </div>

            <?php else: ?>
                <div class="checkout-form">
                    <h2>配送・支払い情報</h2>
                    <?php if ($message): ?>
                        <p style="color: red; margin-bottom: 15px;"><?= htmlspecialchars($message) ?></p>
                    <?php endif; ?>

                    <form method="POST" action="checkout.php">
                        <label for="address">配送先住所:</label>
                        <textarea id="address" name="shipping_address" rows="4" required
                            placeholder="郵便番号、住所、マンション/アパート名など"></textarea>

                        <label>支払い方法:</label>
                        <div class="payment-options">
                            <div class="payment-option-item">
                                <label>
                                    <input type="radio" name="payment_method" value="COD" required>
                                    代金引換 (COD)
                                </label>
                            </div>
                            <div class="payment-option-item">
                                <label class="disabled-option">
                                    <input type="radio" name="payment_method" value="CreditCard" disabled>
                                    クレジットカード (現在利用できません)
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="place-order-btn">
                            ¥<?= number_format($total) ?> で注文を確定する
                        </button>
                    </form>
                </div>

                <div class="order-summary">
                    <h2>注文内容の確認</h2>
                    <ul class="item-list">
                        <?php foreach ($cart_items as $item): ?>
                            <li>
                                <span><?= htmlspecialchars($item['title']) ?> x <?= $item['quantity'] ?></span>
                                <span>¥<?= number_format($item['price'] * $item['quantity']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="final-total">
                        合計: ¥<?= number_format($total) ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <footer class="site-footer">
    </footer>
</body>

</html>