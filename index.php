<?php
require 'config.php';

$books_per_page = 20;
$current_page = (int) ($_GET['page'] ?? 1);
if ($current_page < 1) {
  $current_page = 1;
}
$offset = ($current_page - 1) * $books_per_page;

$keyword = $_GET['q'] ?? '';
$base_sql_select = "SELECT * FROM books";
$base_sql_count = "SELECT COUNT(*) FROM books";
$where_clause = "";
$params = [];

if ($keyword) {
  $where_clause = " WHERE title LIKE ? OR author LIKE ?";
  $params = ["%$keyword%", "%$keyword%"];
}

$sql_count = $base_sql_count . $where_clause;
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_books = $stmt_count->fetchColumn();
$total_pages = ceil($total_books / $books_per_page);

$sql_select = $base_sql_select . $where_clause;
$sql_select .= " ORDER BY id DESC LIMIT ? OFFSET ?";

$params_with_limit = array_merge($params, [$books_per_page, $offset]);

$stmt = $pdo->prepare($sql_select);
$param_index = 1;
foreach ($params as $param) {
  $stmt->bindValue($param_index++, $param);
}
$stmt->bindValue($param_index++, $books_per_page, PDO::PARAM_INT);
$stmt->bindValue($param_index, $offset, PDO::PARAM_INT);

$stmt->execute();
$books = $stmt->fetchAll();

$keyword = trim($_GET['q'] ?? '');
$base_sql_select = "SELECT * FROM books";
$base_sql_count = "SELECT COUNT(*) FROM books";
$where_clause = "";
$params = [];

$is_sale_search = false;
$sale_keywords = [
  'sale',
  'se-ru',
  'セール',
  'せーる',
  'discount',
  'waribiki',
  'わりびき',
  '割引',
  'キャンペーン',
  'きやんぺーん'
];

if (in_array(strtolower($keyword), $sale_keywords)) {
  $is_sale_search = true;
  $where_clause = " WHERE sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price";
} elseif (!empty($keyword)) {
  $where_clause = " WHERE title LIKE ? OR author LIKE ?";
  $params = ["%$keyword%", "%$keyword%"];
}

$sql_count = $base_sql_count . $where_clause;
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_books = $stmt_count->fetchColumn();
$total_pages = ceil($total_books / $books_per_page);

$sql_select = $base_sql_select . $where_clause;
$sql_select .= " ORDER BY id DESC LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql_select);
$param_index = 1;
if (!$is_sale_search && !empty($keyword)) {
  foreach ($params as $param) {
    $stmt->bindValue($param_index++, $param);
  }
}
$stmt->bindValue($param_index++, $books_per_page, PDO::PARAM_INT);
$stmt->bindValue($param_index, $offset, PDO::PARAM_INT);

$stmt->execute();
$books = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>ホーム - HUNG BOOK STORE</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    main .product-card {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    main .product-card.visible {
      opacity: 1;
      transform: translateY(0);
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

    .slider-container {
      width: 100%;
      max-width: 1200px;
      margin-left: auto;
      margin-right: auto;
      margin-top: 10px;
      height: 428px;
      overflow: hidden;
      position: relative;
      border-radius: 10px;
    }

    .slides {
      display: flex;
      width: 300%;
      height: 100%;
      animation: slide-animation 15s infinite ease-in-out;
    }

    .slide {
      width: 100%;
      height: 100%;
    }

    .slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    @keyframes slide-animation {
      0% {
        margin-left: 0;
      }

      30% {
        margin-left: 0;
      }

      33.3% {
        margin-left: -100%;
      }

      63.3% {
        margin-left: -100%;
      }

      66.6% {
        margin-left: -200%;
      }

      95% {
        margin-left: -200%;
      }

      100% {
        margin-left: 0;
      }
    }

    .pagination {
      text-align: center;
      margin: 40px 0;
    }

    .pagination a,
    .pagination span {
      display: inline-block;
      padding: 8px 16px;
      margin: 0 4px;
      border: 1px solid #3bbdf9ff;
      color: var(--secondary-color);
      text-decoration: none;
      border-radius: var(--border-radius);
      transition: background-color 0.3s, color 0.3s;
    }

    .pagination a:hover {
      background-color: var(--secondary-color);
      color: white;
      border-color: var(--secondary-color);
    }

    .pagination span.current {
      background-color: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
      font-weight: bold;
    }

    .pagination span.disabled {
      color: #ccc;
      border-color: #eee;
      cursor: not-allowed;
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
          <input type="text" name="q" id="search-input" placeholder="書籍名、著者名、セールで検索..." value="<?= htmlspecialchars($keyword) ?>"
            autocomplete="off">
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

  <div class="slider-container">
    <div class="slides">
      <div class="slide">
        <img src="assets/images/banner.png" alt="Banner 1">
      </div>
      <div class="slide">
        <img src="assets/images/banner2.png" alt="Banner 2">
      </div>
      <div class="slide">
        <img src="assets/images/banner3.png" alt="Banner 3">
      </div>
    </div>
  </div>
  <main>
    <?php if (count($books) > 0): ?>
      <div class="product-list">
        <?php foreach ($books as $b): ?>
          <div class="product-card">
            <a href="product.php?id=<?= $b['id'] ?>">
              <img src="assets/images/<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['title']) ?>">
              <h3><?= htmlspecialchars($b['title']) ?></h3>
            </a>
            <p>著者：<?= htmlspecialchars($b['author']) ?></p>
            <p> <?php if ($b['sale_price'] !== null && $b['sale_price'] > 0 && $b['sale_price'] < $b['price']): ?>
                <span style="text-decoration: line-through; color: #999; font-size: 0.9em;">
                  ¥<?= number_format($b['price']) ?>
                </span>
                <strong style="color: #e74c3c; margin-left: 5px; font-size: 1.1em;">
                  ¥<?= number_format($b['sale_price']) ?>
                </strong>
                <span
                  style="background-color: #e74c3c; color: white; font-size: 0.8em; padding: 2px 5px; border-radius: 3px; margin-left: 5px; font-weight: bold;">
                  SALE!
                </span>
              <?php else: ?>
                <span style="font-size: 1.1em; font-weight: bold;">
                  ¥<?= number_format($b['price']) ?>
                </span>
              <?php endif; ?>
            </p>
            <a class="add-cart-btn" href="cart.php?action=add&id=<?= $b['id'] ?>">カートに追加</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="text-align:center;">商品が見つかりません。</p>
    <?php endif; ?>
  </main>
  <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php
      $query_params = [];
      if ($keyword) {
        $query_params['q'] = $keyword;
      }
      ?>

      <?php if ($current_page > 1): ?>
        <?php $query_params['page'] = $current_page - 1; ?>
        <a href="index.php?<?= http_build_query($query_params) ?>">&laquo; 前</a>
      <?php else: ?>
        <span class="disabled">&laquo; 前</span>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php if ($i == $current_page): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <?php $query_params['page'] = $i; ?>
          <a href="index.php?<?= http_build_query($query_params) ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($current_page < $total_pages): ?>
        <?php $query_params['page'] = $current_page + 1; ?>
        <a href="index.php?<?= http_build_query($query_params) ?>">次 &raquo;</a>
      <?php else: ?>
        <span class="disabled">次 &raquo;</span>
      <?php endif; ?>

    </div>
  <?php endif; ?>
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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const productCards = document.querySelectorAll('.product-card');

      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, observer) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('visible');
              observer.unobserve(entry.target);
            }
          });
        }, {
          rootMargin: '0px',
          threshold: 0.1
        });

        productCards.forEach(card => {
          observer.observe(card);
        });

      } else {
        productCards.forEach(card => {
          card.classList.add('visible');
        });
      }
    });
  </script>
</body>

</html>