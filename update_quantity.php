<?php
require 'config.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = (int) ($data['id'] ?? 0);
$quantity = (int) ($data['quantity'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

if ($user_id) {
    if ($quantity > 0) {
        $stmt = $pdo->prepare("
            UPDATE cart_items 
            SET quantity = ? 
            WHERE user_id = ? AND book_id = ?
        ");
        $stmt->execute([$quantity, $user_id, $id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$user_id, $id]);
    }

    $stmt_db = $pdo->prepare("SELECT ci.book_id AS id, b.title, b.image, ci.price, ci.quantity 
                             FROM cart_items ci JOIN books b ON ci.book_id = b.id 
                             WHERE ci.user_id = ?");
    $stmt_db->execute([$user_id]);
    $cart_items = [];
    foreach ($stmt_db->fetchAll() as $row) {
        $cart_items[$row['id']] = $row;
    }
    $_SESSION['cart'] = $cart_items;


} elseif (isset($_SESSION['cart'])) {
    if ($quantity > 0) {
        $_SESSION['cart'][$id]['quantity'] = $quantity;
    } else {
        unset($_SESSION['cart'][$id]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Cart not initialized']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Quantity updated in DB/Session']);
?>