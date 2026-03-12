<?php
require 'config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$user_id = $_SESSION['user_id'] ?? null;
$action = $_GET['action'] ?? '';
$id = (int) ($_GET['id'] ?? 0);

function loadCartFromDB($pdo, $user_id)
{
    if ($user_id) {
        $stmt_db = $pdo->prepare("
            SELECT ci.book_id AS id, b.title, b.image, ci.price, ci.original_price, ci.quantity
            FROM cart_items ci JOIN books b ON ci.book_id = b.id
            WHERE ci.user_id = ?
        ");
        $stmt_db->execute([$user_id]);

        $cart_items = [];
        foreach ($stmt_db->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (!isset($row['original_price']) || $row['original_price'] === null) {
                $stmt_orig = $pdo->prepare("SELECT price FROM books WHERE id = ?");
                $stmt_orig->execute([$row['id']]);
                $row['original_price'] = $stmt_orig->fetchColumn();
                if ($row['original_price'] === false || $row['original_price'] === null) {
                    $row['original_price'] = $row['price'];
                }
            }
            $cart_items[$row['id']] = $row;
        }
        $_SESSION['cart'] = $cart_items;
    }
}

if ($action === 'add' && $id > 0) {
    if ($user_id) {
        $stmt_book = $pdo->prepare("SELECT price, sale_price FROM books WHERE id = ?");
        $stmt_book->execute([$id]);
        $book_price_data = $stmt_book->fetch();

        if ($book_price_data) {
            $original_price = $book_price_data['price'];
            $price_to_use = ($book_price_data['sale_price'] !== null && $book_price_data['sale_price'] > 0 && $book_price_data['sale_price'] < $original_price)
                ? $book_price_data['sale_price']
                : $original_price;

            $stmt = $pdo->prepare("
                INSERT INTO cart_items (user_id, book_id, quantity, price, original_price)
                VALUES (?, ?, 1, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + 1
            ");
            $stmt->execute([$user_id, $id, $price_to_use, $original_price]);
        }
        loadCartFromDB($pdo, $user_id);


    } else {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $stmt_book_info = $pdo->prepare("SELECT id, title, price, sale_price, image FROM books WHERE id = ?");
            $stmt_book_info->execute([$id]);
            $book_info = $stmt_book_info->fetch();

            if ($book_info) {
                $original_price = $book_info['price'];
                $price_to_use = ($book_info['sale_price'] !== null && $book_info['sale_price'] > 0 && $book_info['sale_price'] < $original_price)
                    ? $book_info['sale_price']
                    : $original_price;

                $_SESSION['cart'][$id] = [
                    'id' => $book_info['id'],
                    'title' => $book_info['title'],
                    'price' => $price_to_use,
                    'original_price' => $original_price,
                    'image' => $book_info['image'],
                    'quantity' => 1
                ];
            }
        }
    }
    header('Location: cart.php');
    exit();

} elseif ($action === 'remove' && $id > 0) {
    if ($user_id) {
        $stmt_delete = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND book_id = ?");
        $stmt_delete->execute([$user_id, $id]);
        loadCartFromDB($pdo, $user_id);
    } else {
        unset($_SESSION['cart'][$id]);
    }
    header('Location: cart.php');
    exit();
}

loadCartFromDB($pdo, $user_id);
$cart_items = $_SESSION['cart'] ?? [];

$total = 0;
foreach ($cart_items as $item) {
    $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
}
$is_cart_empty = (count($cart_items) === 0);
?>

<!DOCTYPE html>
<html lang="ja" class="<?= $is_cart_empty ? 'cart-is-empty' : '' ?>">

<head>
    <meta charset="UTF-8">
    <title>ショッピングカート - HUNG BOOK STORE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .cart-is-empty {
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

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .cart-table th,
        .cart-table td {
            border: 1px solid var(--light-gray);
            padding: 15px;
            text-align: left;
            vertical-align: middle;
        }

        .cart-table th {
            background-color: var(--light-gray);
            color: var(--primary-color);
            font-weight: 500;
        }

        .cart-item-info {
            display: flex;
            align-items: center;
        }

        .cart-item-info img {
            width: 80px;
            height: 100px;
            object-fit: contain;
            margin-right: 15px;
            border: 1px solid var(--light-gray);
            padding: 5px;
            border-radius: 4px;
        }

        .cart-item-info a {
            color: var(--text-color);
            font-weight: 500;
        }

        .cart-item-info a:hover {
            color: var(--secondary-color);
        }

        .quantity-input {
            width: 60px;
            padding: 5px;
            text-align: center;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
        }

        .remove-btn {
            color: #e74c3c;
            font-size: 1.2em;
            cursor: pointer;
            transition: color 0.3s;
        }

        .remove-btn:hover {
            color: var(--primary-color);
        }

        .cart-summary {
            text-align: right;
            margin-top: 30px;
            padding: 20px;
            background-color: var(--card-background);
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
        }

        .cart-summary h3 {
            margin-right: 30px;
            font-size: 20px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .cart-summary p {
            margin-right: 30px;
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
        }

        .checkout-btn {
            display: inline-block;
            padding: 15px 30px;
            background: #27ae60;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 50px;
            transition: background-color 0.3s ease;
        }

        .checkout-btn:hover {
            background: #2ecc71;
            color: white;
        }

        .search-box {
            position: -webkit-sticky;
            position: sticky;
            border-radius: var(--border-radius);
            top: 100px;
            width: 485px;
            margin-top: 0px;
            z-index: 99;
            background-attachment: fixed;
            background-color: white;
            scroll-margin-top: 101px;
        }

        .search-box form {
            display: flex;
            justify-content: center;
            max-width: 500px;
            height: 60px;
            margin: 0 auto;
            box-shadow: var(--box-shadow);
            border-radius: 50px;
            border: 1px solid #3bbdf9ff;
            background: white;
            position: relative;
        }
    </style>
</head>

<body>
    <header>

        <div class="header-container">
            <div class="logo"><a href="index.php">
                    <img src="assets/images/logo.png" alt="オンライン書店ロゴ" style="height:70px;">
                </a></div>
            <div class="search-box" id="search-anchor">
                <form method="get" action="index.php#search-anchor">
                    <input type="text" name="q" id="search-input" placeholder="書籍名、著者名、セールで検索..."
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" autocomplete="off">
                    <button type="submit" style="
    padding-top: 6px;">検索</button>
                </form>
            </div>
            <div id="suggestions-box"></div>
            <nav>
                <a href="index.php">ホーム</a>
                <a href="cart.php">カート
                    <?php
                    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0):
                        ?>
                        (<?= count($_SESSION['cart']) ?>)
                    <?php endif; ?>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?></a>
                    <a href="logout.php">ログアウト</a>
                <?php else: ?>
                    <a href="login.php">ログイン</a>
                <?php endif; ?>
            </nav>

        </div>
    </header>

    <main>
        <h1>ショッピングカート</h1>

        <?php if (count($cart_items) > 0): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>商品</th>
                        <th>価格</th>
                        <th>数量</th>
                        <th>小計</th>
                        <th>削除</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-item-info">
                                    <a href="product.php?id=<?= $item['id'] ?? 0 ?>">
                                        <img src="assets/images/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>"
                                            alt="<?= htmlspecialchars($item['title'] ?? 'Book title') ?>">
                                    </a>
                                    <a
                                        href="product.php?id=<?= $item['id'] ?? 0 ?>"><?= htmlspecialchars($item['title'] ?? 'Unknown Book') ?></a>
                                </div>
                            </td>
                            <td data-price="<?= $item['price'] ?? 0 ?>">
                                <?php
                                $display_price = $item['price'] ?? 0;
                                $original_price = $item['original_price'] ?? $display_price;

                                if ($original_price > $display_price && $display_price > 0) {
                                    echo '<span style="text-decoration: line-through; color: #999; font-size: 0.9em;">';
                                    echo '¥' . number_format($original_price);
                                    echo '</span><br>';
                                    echo '<strong style="color: #e74c3c; font-size: 1.1em;">';
                                    echo '¥' . number_format($display_price);
                                    echo '</strong>';
                                    echo '<span style="background-color: #e74c3c; color: white; font-size: 0.8em; padding: 1px 4px; border-radius: 3px; margin-left: 5px; font-weight: bold; vertical-align: middle;">';
                                    echo 'SALE!';
                                    echo '</span>';
                                } else {
                                    echo '<span style="font-size: 1.1em; font-weight: bold;">';
                                    echo '¥' . number_format($display_price);
                                    echo '</span>';
                                }
                                ?>
                            </td>
                            <td> <input type="number" name="quantity[<?= $item['id'] ?? 0 ?>]"
                                    value="<?= $item['quantity'] ?? 1 ?>" min="1" class="quantity-input"
                                    data-book-id="<?= $item['id'] ?? 0 ?>">
                            </td>
                            <td>¥<?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) ?></td>
                            <td> <a href="cart.php?action=remove&id=<?= $item['id'] ?? 0 ?>" class="remove-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <h3>合計金額</h3>
                <p>¥<?= number_format($total) ?></p>
                <a href="checkout.php" class="checkout-btn">レジに進む</a>
            </div>

        <?php else: ?>
            <p style="text-align: center; margin-top: 50px; font-size: 1.2em;">カートに商品はありません。</p>
            <p style="text-align: center;"><a href="index.php" style="color: var(--secondary-color);">お買い物を続ける</a></p>
        <?php endif; ?>
    </main>



    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.querySelector('.cart-table tbody');
            const totalElement = document.querySelector('.cart-summary p');
            if (!totalElement) return;

            function calculateTotal() {
                let grandTotal = 0;
                if (!tableBody) return;

                tableBody.querySelectorAll('tr').forEach(row => {
                    const priceCell = row.cells[1];
                    const price = parseFloat(priceCell.dataset.price || 0);

                    const quantityInput = row.querySelector('.quantity-input');
                    const quantity = parseInt(quantityInput?.value || 1);

                    const subtotalCell = row.cells[3];

                    if (!isNaN(price) && !isNaN(quantity) && subtotalCell) {
                        const subtotal = price * quantity;
                        subtotalCell.innerText = '¥' + subtotal.toLocaleString('ja-JP');
                        grandTotal += subtotal;
                    } else if (subtotalCell) {
                        subtotalCell.innerText = '¥ -';
                    }
                });
                if (!isNaN(grandTotal)) {
                    totalElement.innerHTML = '¥' + grandTotal.toLocaleString('ja-JP');
                } else {
                    totalElement.innerHTML = '¥ -';
                }
            }

            function updateSession(id, quantity) {
                fetch('update_quantity.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, quantity: quantity })
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            console.log('Cart updated successfully (DB/Session).');
                            calculateTotal();
                        } else {
                            console.error('Cart update failed:', data.message);
                            alert('カートの更新に失敗しました。' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Network/Fetch error:', error);
                        alert('通信エラーが発生しました。ページを再読み込みしてください。');
                    });
            }

            if (tableBody) {
                tableBody.querySelectorAll('.quantity-input').forEach(input => {
                    let debounceUpdateTimeout;
                    input.addEventListener('change', function () {
                        clearTimeout(debounceUpdateTimeout);
                        const newQuantity = parseInt(this.value);
                        const productId = parseInt(this.dataset.bookId || 0);

                        if (!productId) return;

                        if (newQuantity < 1 || isNaN(newQuantity)) {
                            this.value = 1;
                            debounceUpdateTimeout = setTimeout(() => updateSession(productId, 1), 500);
                        } else {
                            debounceUpdateTimeout = setTimeout(() => updateSession(productId, newQuantity), 500);
                        }
                        calculateTotal();
                    });
                });
            }

            calculateTotal();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const suggestionsBox = document.getElementById('suggestions-box');
            const searchForm = searchInput.closest('form');
            let inputDebounceTimeout;
            let scrollDebounceTimeout;

            if (!searchInput || !suggestionsBox || !searchForm) {
                console.error('Lỗi: Không tìm thấy input, khung gợi ý hoặc form!');
                return;
            }

            function positionSuggestionsBox() {
                const formRect = searchForm.getBoundingClientRect();
                const headerRect = document.querySelector('header').getBoundingClientRect();
                suggestionsBox.style.left = `${formRect.left}px`;
                suggestionsBox.style.top = `${formRect.bottom}px`;
                suggestionsBox.style.width = `${formRect.width}px`;
            }

            searchInput.addEventListener('input', function () {
                const query = this.value.trim();
                clearTimeout(inputDebounceTimeout);

                if (query.length >= 1) {
                    inputDebounceTimeout = setTimeout(() => {
                        fetch(`search_suggestions.php?q=${encodeURIComponent(query)}`)
                            .then(response => {
                                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                                return response.json();
                            })
                            .then(suggestions => {
                                displaySuggestions(suggestions);
                            })
                            .catch(error => {
                                console.error('Lỗi khi lấy gợi ý:', error);
                                suggestionsBox.style.display = 'none';
                            });
                    }, 300);
                } else {
                    suggestionsBox.innerHTML = '';
                    suggestionsBox.style.display = 'none';
                }
            });

            function displaySuggestions(suggestions) {
                if (suggestions && Array.isArray(suggestions) && suggestions.length > 0) {
                    suggestionsBox.innerHTML = '';
                    suggestions.forEach(book => {
                        const link = document.createElement('a');
                        link.href = `product.php?id=${book.id}`;
                        link.classList.add('suggestion-item');
                        const img = document.createElement('img');
                        img.src = `assets/images/${book.image || 'default.jpg'}`;
                        img.alt = book.title;
                        img.classList.add('suggestion-image');

                        const infoDiv = document.createElement('div');
                        infoDiv.classList.add('suggestion-info');

                        const titleSpan = document.createElement('span');
                        titleSpan.textContent = book.title;
                        titleSpan.classList.add('suggestion-title');

                        const authorSpan = document.createElement('span');
                        authorSpan.textContent = book.author;
                        authorSpan.classList.add('suggestion-author');

                        const priceSpan = document.createElement('span');
                        priceSpan.classList.add('suggestion-price');
                        const originalPrice = parseFloat(book.price);
                        const salePrice = parseFloat(book.sale_price);
                        if (salePrice > 0 && salePrice < originalPrice) {
                            priceSpan.innerHTML = `
                  <span class="original">¥${originalPrice.toLocaleString('ja-JP')}</span>
                  <strong class="sale">¥${salePrice.toLocaleString('ja-JP')}<span
                  style="background-color: #e74c3c; color: white; font-size: 0.8em; padding: 2px 5px; border-radius: 3px; margin-left: 5px; font-weight: bold;">
                  SALE!
                </span></strong>
              `;
                        } else {
                            priceSpan.innerHTML = `<strong>¥${originalPrice.toLocaleString('ja-JP')}</strong>`;
                        }

                        infoDiv.appendChild(titleSpan);
                        infoDiv.appendChild(authorSpan);
                        infoDiv.appendChild(priceSpan);

                        link.appendChild(img);
                        link.appendChild(infoDiv);

                        suggestionsBox.appendChild(link);
                    });
                    positionSuggestionsBox();
                    suggestionsBox.style.display = 'block';
                } else {
                    suggestionsBox.innerHTML = '';
                    suggestionsBox.style.display = 'none';
                }
            }

            document.addEventListener('click', function (event) {
                if (!searchForm.contains(event.target) && !suggestionsBox.contains(event.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });

            window.addEventListener('resize', function () {
                if (suggestionsBox.style.display === 'block') {
                    positionSuggestionsBox();
                }
            });

            window.addEventListener('scroll', function () {
                if (suggestionsBox.style.display === 'block') {
                    clearTimeout(scrollDebounceTimeout);
                    scrollDebounceTimeout = setTimeout(positionSuggestionsBox, 10);
                }
            }, { passive: true });

        });
    </script>
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