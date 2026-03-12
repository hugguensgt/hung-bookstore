<?php
require '../config.php';
// Giả định người dùng đã đăng nhập và là admin
// (Trong môi trường thật, bạn cần kiểm tra $_SESSION['user_role'] một cách nghiêm ngặt hơn)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
$message = '';
$is_edit_mode = false;
$book_to_edit = null;

// --- 0. KIỂM TRA CHẾ ĐỘ SỬA (EDIT MODE) ---
$edit_id = (int) ($_GET['id'] ?? 0);
if (isset($_GET['action']) && $_GET['action'] === 'edit' && $edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$edit_id]);
    $book_to_edit = $stmt->fetch();
    if ($book_to_edit) {
        $is_edit_mode = true;
    } else {
        $message = '<p style="color:red;">エラー: 指定された書籍が見つかりません。</p>';
        // Nếu không tìm thấy sách, chuyển hướng để thoát chế độ edit
        header('Location: manage_books.php');
        exit();
    }
}


// --- XỬ LÝ HÀNH ĐỘNG POST (ADD hoặc EDIT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
    // --- Lấy giá trị từ form ---
    $action_type = $_POST['action']; // Lấy action type
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);

    // ===== LẤY GIÁ KHUYẾN MÃI TỪ FORM =====
    $sale_price_input = $_POST['sale_price'] ?? '';
    // Chuyển đổi giá trị rỗng hoặc <= 0 thành NULL
    $sale_price = ($sale_price_input === '' || (float) $sale_price_input <= 0) ? NULL : (float) $sale_price_input;
    // ===== KẾT THÚC LẤY GIÁ SALE =====

    $year = (int) ($_POST['published_year'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $image_name = $_POST['current_image'] ?? 'default.jpg'; // Giữ ảnh hiện tại/mặc định

    // --- Xử lý upload ảnh (Giữ nguyên logic cũ) ---
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $new_image_name = basename($_FILES['image']['name']);
        $target_dir = "../assets/images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $new_image_name)) {
            $image_name = $new_image_name;
        } else {
            $message = '<p style="color:red;">エラー: 画像のアップロードに失敗しました。</p>';
        }
    }

    // --- Validate và Lưu vào DB ---
    if ($title && $author && $price > 0) {
        // ===== KIỂM TRA GIÁ SALE HỢP LỆ =====
        if ($sale_price !== NULL && $sale_price >= $price) {
            $message = '<p style="color:red;">エラー: セール価格は通常価格より低く設定してください。</p>';
        } else {
            // ===== KẾT THÚC KIỂM TRA GIÁ SALE =====
            try {
                if ($action_type === 'add') {
                    // ===== THÊM sale_price VÀO INSERT =====
                    $sql = "INSERT INTO books (title, author, price, sale_price, published_year, description, image) VALUES (:title, :author, :price, :sale_price, :year, :desc, :image)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':title' => $title,
                        ':author' => $author,
                        ':price' => $price,
                        ':sale_price' => $sale_price, // Thêm giá sale
                        ':year' => $year,
                        ':desc' => $description,
                        ':image' => $image_name
                    ]);
                    // ===== KẾT THÚC THÊM INSERT =====
                    $message = '<p style="color:green;">書籍の追加に成功しました！</p>';
                } elseif ($action_type === 'edit') {
                    // ===== THÊM sale_price VÀO UPDATE =====
                    $book_id = (int) ($_POST['book_id'] ?? 0);
                    $sql = "UPDATE books
                            SET title = :title, author = :author, price = :price, sale_price = :sale_price,
                                published_year = :year, description = :desc, image = :image
                            WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':title' => $title,
                        ':author' => $author,
                        ':price' => $price,
                        ':sale_price' => $sale_price, // Thêm giá sale
                        ':year' => $year,
                        ':desc' => $description,
                        ':image' => $image_name,
                        ':id' => $book_id
                    ]);
                    // ===== KẾT THÚC THÊM UPDATE =====
                    $message = '<p style="color:green;">書籍の更新に成功しました！</p>';
                    header('Location: manage_books.php?update_success=true#form-edit-anchor');
                    exit();
                }
            } catch (\PDOException $e) {
                $message = '<p style="color:red;">エラー: データベース処理に失敗しました: ' . $e->getMessage() . '</p>';
            }
        } // Đóng 'else' của kiểm tra giá sale
    } else {
        $message = '<p style="color:red;">エラー: タイトル、著者、価格を正しく入力してください。</p>';
    }
}
// 4. Xử lý ÁP DỤNG SALE NGẪU NHIÊN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply_random_sale') {
    $percentage = (int) ($_POST['sale_percentage'] ?? 0);
    $num_books_option = $_POST['num_books'] ?? '10';
    $limit = 0;

    if ($num_books_option === '10')
        $limit = 10;
    elseif ($num_books_option === '20')
        $limit = 20;
    elseif ($num_books_option === '30')
        $limit = 30;
    elseif ($num_books_option === 'all')
        $limit = -1;

    if (($limit > 0 || $limit === -1) && $percentage >= 0 && $percentage <= 100) {
        try {
            $pdo->beginTransaction();
            // Bước 1: Xóa sale cũ
            $pdo->exec("UPDATE books SET sale_price = NULL");

            // Bước 2: Áp dụng sale mới nếu % > 0
            if ($percentage > 0) {
                $sql_select_random = "SELECT id, price FROM books ORDER BY RAND()";
                if ($limit !== -1) {
                    $sql_select_random .= " LIMIT ?";
                }
                $stmt_select = $pdo->prepare($sql_select_random);
                if ($limit !== -1) {
                    $stmt_select->bindValue(1, $limit, PDO::PARAM_INT);
                }
                $stmt_select->execute();
                $books_to_sale = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

                $stmt_update = $pdo->prepare("UPDATE books SET sale_price = ? WHERE id = ?");
                $discount_multiplier = (100 - $percentage) / 100;
                $count_applied_actual = 0; // Đếm số sách thực sự được áp sale

                foreach ($books_to_sale as $book) {
                    $calculated_sale_price = round($book['price'] * $discount_multiplier, 2);
                    if ($calculated_sale_price < $book['price'] && $calculated_sale_price > 0) {
                        $stmt_update->execute([$calculated_sale_price, $book['id']]);
                        $count_applied_actual++;
                    } else {
                        // Thử làm tròn xuống nếu làm tròn thường không đủ nhỏ
                        $calculated_sale_price = floor($book['price'] * $discount_multiplier);
                        if ($calculated_sale_price < $book['price'] && $calculated_sale_price > 0) {
                            $stmt_update->execute([$calculated_sale_price, $book['id']]);
                            $count_applied_actual++;
                        }
                    }
                }
            }
            $pdo->commit();

            if ($percentage > 0) {
                $message_text = $count_applied_actual . '冊の書籍に' . $percentage . '%の割引を適用しました。';
                $message = '<p style="color:green;">' . $message_text . '</p>';
            } else {
                $message_text = 'すべての書籍からセール価格を解除しました。';
                $message = '<p style="color:orange;">' . $message_text . '</p>';
            }
            // Giữ lại $message để hiển thị ngay
            // header('Location: manage_books.php?sale_applied=true&msg=' . urlencode($message_text)); // Bỏ chuyển hướng
            // exit(); // Bỏ exit

        } catch (\PDOException $e) {
            $pdo->rollBack();
            $message = '<p style="color:red;">エラー: セール適用中にデータベースエラーが発生しました: ' . $e->getMessage() . '</p>';
        }
    } else {
        $message = '<p style="color:red;">エラー: 無効な割引率または書籍数が選択されました。</p>';
    }
}

// 3. Xử lý XÓA sách
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    try {
        // Cần xóa các mục liên quan trong order_items/cart_items trước nếu có Foreign Key
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<p style="color:green;">書籍 ID: ' . $id . ' を削除しました。</p>';
        header('Location: manage_books.php?delete_success=true');
        exit();
    } catch (\PDOException $e) {
        $message = '<p style="color:red;">エラー: 書籍の削除に失敗しました。</p>';
    }
}


// --- HIỂN THỊ DANH SÁCH SÁCH VÀ TÌM KIẾM ---
$keyword = $_GET['q'] ?? '';
$sql = "SELECT * FROM books";
$params = [];

if ($keyword) {
    // Thêm điều kiện tìm kiếm theo Tiêu đề HOẶC Tác giả
    $sql .= " WHERE title LIKE ? OR author LIKE ?";
    $params = ["%$keyword%", "%$keyword%"];
}

$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Hiển thị thông báo sau khi chuyển hướng
if (isset($_GET['update_success'])) {
    $message = '<p style="color:green;">書籍の更新に成功しました！</p>';
}
if (isset($_GET['delete_success'])) {
    $message = '<p style="color:green;">書籍の削除に成功しました！</p>';
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>書籍管理 - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* CSS FIX STICKY VÀ FLEXBOX */
        html,
        body {
            height: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            overflow-x: hidden;
            /* Ngăn cuộn ngang không mong muốn */
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

        /* CẤU HÌNH SIDEBAR GỐC VÀ KHẮC PHỤC LỖI THU NHỎ (flex-shrink: 0) */
        .sidebar {
            width: 200px;
            background: #34495e;
            color: white;
            height: 100vh;
            padding-top: 20px;
            position: sticky;
            top: 0;
            box-sizing: border-box;
            flex-shrink: 0;
            z-index: 100;
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

        /* NEO TIÊU ĐỀ BẢNG (<thead>) */
        .admin-table th {
            background-color: #ecf0f1;
            color: #2c3e50;
            position: sticky;
            top: 105px;
            /* SỬA ĐỔI CHÍNH: Thay đổi từ 55px lên 105px để nằm dưới thanh tìm kiếm */
            z-index: 50;
            -webkit-position: sticky;
            min-width: 100px;
        }

        .admin-table img {
            width: 50px;
            height: 70px;
            object-fit: contain;
        }

        .action-btn {
            padding: 5px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }

        .edit-btn {
            background: #3498db;
            color: white;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }

        .form-add {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .form-add input[type="text"],
        .form-add input[type="number"],
        .form-add textarea,
        .form-add input[type="file"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 0;
        }

        /* ĐIỀU CHỈNH NÚT SUBMIT DỰA TRÊN CHẾ ĐỘ */
        .form-add button {
            background: #1abc9c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
        }

        .form-add button.edit-submit {
            background: #f39c12;
            /* Màu cam cho nút Update */
        }

        /* CSS CHO FORM GỌN GÀNG (LABEL VÀ INPUT TRÊN MỘT HÀNG) */
        .form-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-add label {
            width: 120px;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
            margin-top: 0;
        }

        .form-add textarea {
            width: 100%;
            margin-bottom: 15px;
        }

        /* THANH TÌM KIẾM CỐ ĐỊNH (FIX NHẢY TRANG) */
        .search-bar-sticky {
            position: sticky;
            top: 0;
            z-index: 60;
            background: #f4f4f4;
            padding-top: 15px;
            /* Giảm padding */
            padding-bottom: 15px;
            /* Giảm padding */
            margin-bottom: 0;
            border-bottom: 1px solid #ddd;
        }

        .search-controls {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 0;
        }

        .search-form {
            display: flex;
            max-width: 400px;
            border: 1px solid #3498db;
            border-radius: 4px;
            overflow: hidden;
        }

        .search-form input[type="text"] {
            flex-grow: 1;
            padding: 8px 10px;
            border: none;
            outline: none;
        }

        .search-form button {
            background: #3498db;
            color: white;
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-form button:hover {
            background: #2980b9;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>書籍管理</h1>
        <span>ようこそ、<?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?> さん | <a href="logout.php">ログアウト</a></span>
    </div>

    <div class="container">
        <div class="sidebar">
            <a href="dashboard.php"><i class="fas fa-home"></i> ホーム</a>
            <a href="manage_books.php" style="background: #1abc9c;"><i class="fas fa-book"></i> 書籍管理</a>
            <a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> 注文管理</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> ユーザー管理</a>
        </div>

        <div class="content">
            <?= $message ?>
            <div class="form-add" id="form-edit-anchor">
                <?php if ($is_edit_mode): ?>
                    <h3>書籍 ID: <?= $book_to_edit['id'] ?> の編集</h3>
                    <a href="manage_books.php" style="color: #e74c3c; font-size: 14px; float: right;">
                        <i class="fas fa-times"></i> 編集をキャンセル
                    </a>
                <?php else: ?>
                    <h3>新しい書籍を追加</h3>
                <?php endif; ?>

                <form method="POST" action="manage_books.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?= $is_edit_mode ? 'edit' : 'add' ?>">
                    <?php if ($is_edit_mode): ?>
                        <input type="hidden" name="book_id" value="<?= $book_to_edit['id'] ?>">
                        <input type="hidden" name="current_image" value="<?= htmlspecialchars($book_to_edit['image']) ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <label for="title">タイトル:</label>
                        <input type="text" id="title" name="title" placeholder="書籍のタイトルを入力" required
                            value="<?= htmlspecialchars($book_to_edit['title'] ?? '') ?>">
                    </div>
                    <div class="form-row">
                        <label for="author">著者:</label>
                        <input type="text" id="author" name="author" placeholder="著者名を入力" required
                            value="<?= htmlspecialchars($book_to_edit['author'] ?? '') ?>">
                    </div>
                    <div class="form-row">
                        <label for="price">価格 (¥):</label>
                        <input type="number" id="price" name="price" placeholder="通常価格" step="0.01" required
                            value="<?= htmlspecialchars($book_to_edit['price'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <label for="sale_price">セール価格 (¥):</label>
                        <input type="number" id="sale_price" name="sale_price" placeholder="割引価格 (任意)" step="0.01"
                            value="<?= htmlspecialchars($book_to_edit['sale_price'] ?? '') ?>">
                    </div>
                    <div class="form-row">
                        <label for="year">出版年:</label>
                        <input type="number" id="year" name="published_year" placeholder="出版年を入力"
                            value="<?= htmlspecialchars($book_to_edit['published_year'] ?? '') ?>">
                    </div>
                    <label for="description">説明:</label>
                    <textarea id="description" name="description" placeholder="書籍の説明を入力"
                        rows="3"><?= htmlspecialchars($book_to_edit['description'] ?? '') ?></textarea>
                    <?php if ($is_edit_mode): ?>
                        <div style="margin-bottom: 15px; clear: both; margin-top: 10px;">
                            <label>現在の画像:</label>
                            <img src="../assets/images/<?= htmlspecialchars($book_to_edit['image']) ?>" alt="現在の画像"
                                style="width: 80px; height: 100px; object-fit: contain; border: 1px solid #ddd; padding: 5px; display: block;">
                        </div>
                    <?php endif; ?>
                    <label for="image">新しい画像 (任意):</label>
                    <input type="file" id="image" name="image" accept="image/*" style="width: auto;">

                    <button type="submit" class="<?= $is_edit_mode ? 'edit-submit' : '' ?>">
                        <i class="fas <?= $is_edit_mode ? 'fa-sync-alt' : 'fa-plus' ?>"></i>
                        <?= $is_edit_mode ? '書籍情報を更新' : '書籍を追加' ?>
                    </button>
                </form>
            </div>
            <div class="form-add" style="margin-top: 40px; border-top: 2px dashed #ccc;">
                <h3>ランダムセールを作成</h3>
                <form method="POST" action="manage_books.php">
                    <input type="hidden" name="action" value="apply_random_sale">
                    <div class="form-row">
                        <label for="sale_percentage">割引率:</label>
                        <select name="sale_percentage" id="sale_percentage" required>
                            <option value="5">5% OFF</option>
                            <option value="10">10% OFF</option>
                            <option value="15">15% OFF</option>
                            <option value="0">セールを解除</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="num_books">書籍数:</label>
                        <select name="num_books" id="num_books" required>
                            <option value="10">ランダム 10冊</option>
                            <option value="20">ランダム 20冊</option>
                            <option value="30">ランダム 30冊</option>
                            <option value="all">すべての書籍</option>
                        </select>
                    </div>
                    <p style="font-size: 13px; color: #777;">注意：既存のセール価格は上書きされます。</p>
                    <button type="submit" style="background-color: #e67e22;"
                        onclick="return confirm('ランダムセールを適用しますか？既存のセール価格はリセットされます。');">
                        <i class="fas fa-random"></i> ランダムセールを適用
                    </button>
                </form>
            </div>
            <div class="search-bar-sticky" id="book-list-anchor">
                <div class="search-controls">
                    <form method="GET" action="manage_books.php#book-list-anchor" class="search-form">
                        <input type="text" name="q" placeholder="タイトル/著者で検索..."
                            value="<?= htmlspecialchars($keyword) ?>">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    <?php if ($keyword): ?>
                        <a href="manage_books.php" style="color: #e74c3c; font-size: 14px;">
                            <i class="fas fa-undo"></i> 検索をクリア (<?= count($books) ?> 件の結果)
                        </a>
                    <?php endif; ?>
                </div>

                <h3 style="margin-top: 10px; margin-bottom: 10px;">登録済み書籍一覧
                    <?php if (!$keyword): ?>
                        (全 <?= count($books) ?> 冊)
                    <?php endif; ?>
                </h3>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>著者</th>
                        <th>価格</th>
                        <th>画像</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($books) > 0): ?>
                        <?php foreach ($books as $book): ?>
                            <tr id="book-<?= $book['id'] ?>">
                                <td><?= htmlspecialchars($book['id']) ?></td>
                                <td><?= htmlspecialchars($book['title']) ?></td>
                                <td><?= htmlspecialchars($book['author']) ?></td>
                                <td> <?php if ($book['sale_price'] !== null && $book['sale_price'] > 0): ?>
                                        <span
                                            style="text-decoration: line-through; color: #999;">¥<?= number_format($book['price']) ?></span><br>
                                        <strong style="color: #e74c3c;">¥<?= number_format($book['sale_price']) ?></strong>
                                    <?php else: ?>
                                        ¥<?= number_format($book['price']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><img src="../assets/images/<?= htmlspecialchars($book['image']) ?>" alt=""></td>
                                <td>
                                    <a href="manage_books.php?action=edit&id=<?= $book['id'] ?>#form-edit-anchor"
                                        class="action-btn edit-btn">編集</a>
                                    <a href="manage_books.php?action=delete&id=<?= $book['id'] ?>" class="action-btn delete-btn"
                                        onclick="return confirm('本当に削除しますか？');">削除</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">
                                <?php if ($keyword): ?>
                                    キーワード "<?= htmlspecialchars($keyword) ?>" に一致する書籍は見つかりませんでした。
                                <?php else: ?>
                                    現在、書籍は登録されていません。
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>