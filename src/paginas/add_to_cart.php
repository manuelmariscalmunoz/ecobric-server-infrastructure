<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;

if ($id > 0) {
    // Consultar stock y datos del producto
    $stmt = $pdo->prepare("SELECT nombre, precio, url_imagen, rendimiento_por_m3, stock FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch();

    if ($prod) {
        $currentCartQty = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id]['quantity'] : 0;
        $newTotalQty = $currentCartQty + $qty;

        if ($newTotalQty > $prod['stock']) {
            echo json_encode(['success' => false, 'message' => "No hay suficiente stock. (Stock disponible: {$prod['stock']})"]);
            exit;
        }

        // Si ya existe en el carrito, sumar qty
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$id] = [
                'id' => $id,
                'name' => $prod['nombre'],
                'price' => $prod['precio'],
                'image' => $prod['url_imagen'],
                'quantity' => $qty,
                'rendimiento' => $prod['rendimiento_por_m3']
            ];
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
        exit;
    }
}

$totalItems = array_sum(array_column($_SESSION['cart'], 'quantity'));

echo json_encode(['success' => true, 'total_items' => $totalItems]);
exit;
?>