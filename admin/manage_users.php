<?php
require '../config.php';

// KIỂM TRA PHÂN QUYỀN: Đảm bảo có user_role và role phải là 'admin'
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';

// --- XỬ LÝ CẬP NHẬT VAI TRÒ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $new_role = trim($_POST['new_role'] ?? '');

    // Ngăn chặn admin tự hạ cấp tài khoản của mình (Đã sửa: dùng $_SESSION['user_id'])
    if ($user_id === $_SESSION['user_id'] && $new_role !== 'admin') {
        $message = '<p style="color:red;">エラー: 自分のアカウントの権限を変更することはできません。</p>';
    } elseif ($user_id > 0 && in_array($new_role, ['user', 'admin'])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $user_id]);
            $message = '<p style="color:green;">ユーザー ID: ' . $user_id . ' の権限を ' . htmlspecialchars($new_role) . ' に更新しました。</p>';
        } catch (\PDOException $e) {
            $message = '<p style="color:red;">エラー: 権限の更新に失敗しました。</p>';
        }
    }
}

// --- HIỂN THỊ DANH SÁCH NGƯỜI DÙNG ---
$users = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY id ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ユーザー管理 - Admin</title>
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
        <h1>ユーザー管理</h1>
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
            <h2>登録済みユーザー一覧</h2>
            <?= $message ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ユーザー名</th>
                        <th>メール</th>
                        <th>登録日</th>
                        <th>権限</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <form method="POST" action="manage_users.php" class="update-form">
                                    <input type="hidden" name="action" value="update_role">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="new_role" <?= ($user['id'] === $_SESSION['user_id']) ? 'disabled' : '' ?>>
                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>user</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                                    </select>
                                    <button type="submit" <?= ($user['id'] === $_SESSION['user_id']) ? 'disabled' : '' ?>>更新</button>
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