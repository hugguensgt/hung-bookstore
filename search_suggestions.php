<?php
require 'config.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$suggestions = [];

if (strlen($query) >= 1) {

    $is_sale_search = false;
    $sale_keywords = [
        'sale', 'セール', 'せーる',
        'discount', '割引', 'わりびき',
        '特価', 'とっか',
        'お買い得', 'おかいどく',
        '値下げ', 'ねさげ',
        'キャンペーン', 'きやんぺーん'
    ];

    if (in_array(strtolower($query), $sale_keywords)) {
        $is_sale_search = true;
    }

    try {
        if ($is_sale_search) {
            $stmt = $pdo->prepare(
                "SELECT id, title, author, price, sale_price, image FROM books
                 WHERE sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price
                 ORDER BY RAND()
                 LIMIT 5"
            );
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare(
                "SELECT id, title, author, price, sale_price, image FROM books
                 WHERE title LIKE ? OR author LIKE ?
                 ORDER BY title ASC
                 LIMIT 5"
            );
            $stmt->execute(["%$query%", "%$query%"]);
        }
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (\PDOException $e) {
        error_log("Search suggestion error: " . $e->getMessage());
        $suggestions = [];
    }
}

echo json_encode($suggestions);
?>