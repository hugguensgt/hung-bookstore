<?php
require '../config.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';

// --- XỬ LÝ CẬP NHẬT TRẠNG THÁI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int) ($_POST['order_id'] ?? 0);
    $new_status = trim($_POST['new_status'] ?? '');

    if ($order_id > 0 && $new_status) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            $message = '<p style="color:green;">注文 #' . $order_id . ' のステータスを更新しました。</p>';
        } catch (\PDOException $e) {
            $message = '<p style="color:red;">エラー: ステータスの更新に失敗しました。</p>';
        }
    }
}

// --- HIỂN THỊ DANH SÁCH ĐƠN HÀNG ---
$orders = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();

// Hàm lấy chi tiết đơn hàng (Dùng lại từ profile.php)
function getOrderItemsForAdmin($pdo, $order_id)
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
    <title>注文管理 - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* CSS cơ bản từ dashboard/manage_books */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header a {
            color: #f1c40f;
            text-decoration: none;
            margin-left: 20px;
        }

        .container {
            display: flex;
        }

        .sidebar {
            width: 200px;
            background: #34495e;
            color: white;
            height: 100vh;
            padding-top: 20px;
        }

        .sidebar a {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            border-bottom: 1px solid #4a627a;
        }

        .sidebar a:hover {
            background: #1abc9c;
        }

        .content {
            flex-grow: 1;
            padding: 30px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .admin-table th,
        .admin-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }

        .admin-table th {
            background-color: #ecf0f1;
            color: #2c3e50;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
            font-size: 12px;
        }

        .status-Pending {
            background-color: #f39c12;
        }

        .status-Shipped {
            background-color: #3498db;
        }

        .status-Delivered {
            background-color: #2ecc71;
        }

        .status-Cancelled {
            background-color: #e74c3c;
        }

        .order-detail-list {
            font-size: 0.9em;
            margin-top: 5px;
            padding-left: 15px;
        }

        .update-form select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .update-form button {
            padding: 5px 10px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>注文管理</h1>
        <span>ようこそ、<?= htmlspecialchars($_SESSION['username']) ?> さん | <a href="logout.php">ログアウト</a></span>
    </div>

    <div class="container">
        <div class="sidebar">
            <a href="dashboard.php"><i class="fas fa-home"></i> ホーム</a>
            <a href="manage_books.php"><i class="fas fa-book"></i> 書籍管理</a>
            <a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> 注文管理</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> ユーザー管理</a>
        </div>

        <div class="content">
            <h2>すべての注文</h2>
            <?= $message ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ユーザー</th>
                        <th>合計</th>
                        <th>日付</th>
                        <th>ステータス</th>
                        <th>詳細</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['username']) ?></td>
                            <td>¥<?= number_format($order['total_amount']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= htmlspecialchars($order['order_status']) ?>">
                                    <?= htmlspecialchars($order['order_status']) ?>
                                </span>
                            </td>
                            <td>
                                <ul class="order-detail-list">
                                    <?php
                                    $items = getOrderItemsForAdmin($pdo, $order['id']);
                                    foreach ($items as $item):
                                        ?>
                                        <li><?= htmlspecialchars($item['title']) ?> (x<?= $item['quantity'] ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>
                                <form method="POST" action="manage_orders.php" class="update-form">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="new_status">
                                        <option value="Pending" <?= $order['order_status'] === 'Pending' ? 'selected' : '' ?>>
                                            Pending</option>
                                        <option value="Shipped" <?= $order['order_status'] === 'Shipped' ? 'selected' : '' ?>>
                                            Shipped</option>
                                        <option value="Delivered" <?= $order['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="Cancelled" <?= $order['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit">更新</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>