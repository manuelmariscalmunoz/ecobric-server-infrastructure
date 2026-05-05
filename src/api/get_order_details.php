<?php
require_once '../config/db.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$pedido_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($pedido_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido.']);
    exit;
}

try {
    // Verificar que el pedido existe y (si no es admin) le pertenece al usuario
    if ($_SESSION['user_role'] != 1) {
        $stmtCheck = $pdo->prepare("SELECT id FROM pedidos WHERE id = ? AND usuario_id = ?");
        $stmtCheck->execute([$pedido_id, $_SESSION['user_id']]);
        if (!$stmtCheck->fetch()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver este pedido.']);
            exit;
        }
    }

    // Obtener detalles del pedido (productos)
    $stmt = $pdo->prepare("
        SELECT dp.cantidad, dp.precio_unitario, p.nombre, ped.direccion_envio
        FROM detalles_pedido dp
        JOIN productos p ON dp.producto_id = p.id
        JOIN pedidos ped ON dp.pedido_id = ped.id
        WHERE dp.pedido_id = ?
    ");
    $stmt->execute([$pedido_id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $direccion_envio = count($detalles) > 0 ? $detalles[0]['direccion_envio'] : 'No especificada';

    echo json_encode(['success' => true, 'detalles' => $detalles, 'direccion_envio' => $direccion_envio]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener los detalles del pedido.']);
}
?>