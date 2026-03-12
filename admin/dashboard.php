<?php
require '../config.php';

// KIỂM TRA PHÂN QUYỀN
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Lấy số liệu thống kê (ví dụ)
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>管理者ダッシュボード</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #3498db;
            margin-top: 0;
        }

        .stat-card p {
            font-size: 30px;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>管理者ダッシュボード</h1>
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
            <h2>サイト概要</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>総書籍数</h3>
                    <p><?= $total_books ?></p>
                </div>
                <div class="stat-card">
                    <h3>総注文数</h3>
                    <p><?= $total_orders ?></p>
                </div>
                <div class="stat-card">
                    <h3>総ユーザー数</h3>
                    <p><?= $total_users ?></p>
                </div>
            </div>

        </div>
    </div>
</body>

</html>