<?php
require 'config.php';
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) {
  die('本が見つかりません');
}

$related_books = [];
$related_heading = '';
$total_limit = 4;

$stmt_author = $pdo->prepare(
  "SELECT * FROM books
     WHERE author = ? AND id != ?
     ORDER BY RAND()
     LIMIT ?"
);

$stmt_author->bindValue(1, $book['author']);
$stmt_author->bindValue(2, $id, PDO::PARAM_INT);
$stmt_author->bindValue(3, $total_limit, PDO::PARAM_INT);

$stmt_author->execute();
$related_books = $stmt_author->fetchAll();

if (count($related_books) > 0) {
  $related_heading = 'この著者（' . htmlspecialchars($book['author']) . '）の他の作品'; // Tiêu đề: Tác phẩm khác của tác giả
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($book['title']) ?> - HUNG BOOK STORE</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    .related-products {
      max-width: 1200px;
      margin: 55px auto;
      padding: 0 15px;
    }

    .related-products h2 {
      text-align: center;
      font-size: 24px;
      color: var(--primary-color);
      margin-bottom: 30px;
      border-bottom: 2px solid var(--light-gray);
      padding-bottom: 10px;
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
    <div class="product-detail">
      <img src="assets/images/<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">

      <div class="product-info">
        <h2><?= htmlspecialchars($book['title']) ?></h2>
        <p><strong>著者：</strong><?= htmlspecialchars($book['author']) ?></p>
        <p><strong>出版年：</strong><?= htmlspecialchars($book['published_year']) ?></p>
        <p><strong>価格：</strong>
          <?php if ($book['sale_price'] !== null && $book['sale_price'] > 0 && $book['sale_price'] < $book['price']): ?>
            <span style="text-decoration: line-through; color: #999; font-size: 0.9em;">
              ¥<?= number_format($book['price']) ?>
            </span>
            <strong style="color: #e74c3c; margin-left: 5px; font-size: 1.2em;">
              ¥<?= number_format($book['sale_price']) ?>
            </strong>
            <span
              style="background-color: #e74c3c; color: white; font-size: 0.8em; padding: 2px 5px; border-radius: 3px; margin-left: 5px; font-weight: bold; vertical-align: middle;">
              SALE!
            </span>
          <?php else: ?>
            <span style="font-size: 1.2em; font-weight: bold;">
              ¥<?= number_format($book['price']) ?>
            </span>
          <?php endif; ?>
        </p>
        <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
        <a href="cart.php?action=add&id=<?= $book['id'] ?>" class="add-cart-btn"
          style="border-radius: 30px; padding: 10px 20px;">カートに追加</a>
      </div>
    </div>

    <?php if (count($related_books) > 0 && $related_heading): ?>
      <div class="related-products">

        <h2><?= $related_heading ?></h2>

        <div class="product-list">

          <?php foreach ($related_books as $related_book): ?>
            <div class="product-card">
              <a href="product.php?id=<?= $related_book['id'] ?>">
                <img src="assets/images/<?= htmlspecialchars($related_book['image']) ?>"
                  alt="<?= htmlspecialchars($related_book['title']) ?>">
                <h3><?= htmlspecialchars($related_book['title']) ?></h3>
              </a>
              <p>著者：<?= htmlspecialchars($related_book['author']) ?></p>
              <p>¥<?= number_format($related_book['price']) ?></p>
              <a class="add-cart-btn" href="cart.php?action=add&id=<?= $related_book['id'] ?>">カートに追加</a>
            </div>
          <?php endforeach; ?>

        </div>
      </div>
    <?php endif; ?>
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
</body>

</html>