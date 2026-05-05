<?php
require_once '../config/db.php';

session_start();
$mensaje = '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Obtener dirección por defecto para rellenar el formulario o usar en checkout
$direccion_defecto_form = '';
if (isset($_SESSION['user_id'])) {
    $stmtAddr = $pdo->prepare("SELECT direccion_predeterminada FROM usuarios WHERE id = ?");
    $stmtAddr->execute([$_SESSION['user_id']]);
    $rowAddr = $stmtAddr->fetch();
    if ($rowAddr) {
        $direccion_defecto_form = $rowAddr['direccion_predeterminada'];
    }
}

// Lógica de borrar producto del carrito
if (isset($_GET['remove'])) {
    $id_remove = (int) $_GET['remove'];
    if (isset($_SESSION['cart'][$id_remove])) {
        unset($_SESSION['cart'][$id_remove]);
        header("Location: carrito.php");
        exit;
    }
}

// Lógica del pago final (Checkout)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?msg=Debes iniciar sesión para comprar");
        exit;
    }

    if (empty($_SESSION['cart'])) {
        $mensaje = "El carrito está vacío.";
    } else {
        $metodo = $_POST['metodo_pago'] ?? 'Tarjeta';
        $total = $_POST['total_pagar'] ?? 0;
        $direccion_envio = trim($_POST['direccion_envio'] ?? '');
        $guardar_direccion = isset($_POST['guardar_direccion']) ? true : false;

        if (empty($direccion_envio)) {
            $mensaje = "Por favor, introduce una dirección de envío válida.";
            $stockValido = false; // Forzar fallo para no procesar
        } else {
            $pdo->beginTransaction();
            try {
                // VERIFICAR STOCK PRIMERO
                $stockValido = true;
                $erroresStock = [];
                foreach ($_SESSION['cart'] as $item) {
                    $stmtCheck = $pdo->prepare("SELECT stock, nombre FROM productos WHERE id = ? FOR UPDATE");
                    $stmtCheck->execute([$item['id']]);
                    $prodDb = $stmtCheck->fetch();
                    if (!$prodDb || $prodDb['stock'] < $item['quantity']) {
                        $stockValido = false;
                        $disponible = $prodDb ? $prodDb['stock'] : 0;
                        $erroresStock[] = "El producto '{$item['name']}' solo tiene $disponible unidades en stock.";
                    }
                }

                if (!$stockValido) {
                    $pdo->rollBack();
                    $mensaje = implode("<br>", $erroresStock);
                } else {
                    // Guardar como predeterminada si marcó la casilla
                    if ($guardar_direccion) {
                        $stmtAddrUpdate = $pdo->prepare("UPDATE usuarios SET direccion_predeterminada = ? WHERE id = ?");
                        $stmtAddrUpdate->execute([$direccion_envio, $_SESSION['user_id']]);
                    }

                    // 1. Insertar Pedido
                    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, monto_total, metodo_pago, direccion_envio, estado) VALUES (?, ?, ?, ?, 'PAGADO')");
                    $stmt->execute([$_SESSION['user_id'], $total, $metodo, $direccion_envio]);
                    $pedido_id = $pdo->lastInsertId();

                    // 2. Insertar Productos y descontar Stock
                    $stmtProd = $pdo->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                    $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

                    $itemsHtml = "";
                    foreach ($_SESSION['cart'] as $item) {
                        $stmtProd->execute([$pedido_id, $item['id'], $item['quantity'], $item['price']]);
                        $stmtStock->execute([$item['quantity'], $item['id']]);
                        $subt = number_format($item['price'] * $item['quantity'], 2, ',', '.');
                        $precioUnit = number_format($item['price'], 2, ',', '.');
                        $itemsHtml .= "<li><strong>{$item['name']}</strong> - {$item['quantity']} uds. x {$precioUnit} &euro; = {$subt} &euro;</li>";
                    }

                    $pdo->commit();

                    // Configurar Email con Función Global MAILER
                    require_once '../includes/mailer.php';

                    $stmtUser = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
                    $stmtUser->execute([$_SESSION['user_id']]);
                    $user = $stmtUser->fetch();

                    $asunto = "Confirmación de Pedido #" . str_pad($pedido_id, 5, '0', STR_PAD_LEFT) . " - Ecobric";

                    $html_correo = "
                        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                            <div style='background-color: #2e7d32; color: white; padding: 20px; text-align: center;'>
                                <h2 style='margin: 0;'>¡Gracias por tu compra, {$user['nombre']}!</h2>
                            </div>
                            <div style='padding: 20px;'>
                                <p style='font-size: 16px;'>Tu pedido <strong>#" . str_pad($pedido_id, 5, '0', STR_PAD_LEFT) . "</strong> ha sido procesado con éxito.</p>
                                <h3 style='border-bottom: 2px solid #2e7d32; padding-bottom: 5px; color: #2e7d32;'>Detalles del Pedido:</h3>
                                <ul style='line-height: 1.6;'>
                                    {$itemsHtml}
                                </ul>
                                <div style='background-color: #f0f4f8; padding: 15px; border-radius: 4px; margin-top: 15px;'>
                                    <h4 style='margin: 0 0 10px 0; color: #2e7d32;'>Enviar a:</h4>
                                    <p style='margin: 0;'><b>" . htmlspecialchars($direccion_envio) . "</b></p>
                                </div>
                                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 4px; text-align: right; margin-top: 20px;'>
                                    <h3 style='margin: 0; color: #333;'>Total Pagado: " . number_format($total, 2, ',', '.') . " &euro;</h3>
                                </div>
                                <p style='text-align: center; margin-top: 20px; font-weight: bold; color: #555;'>
                                    <span style='color: #2e7d32;'>&#127757;</span> ¡Has ayudado al planeta eligiendo materiales de bioconstrucción!
                                </p>
                            </div>
                            <div style='background-color: #eee; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                                &copy; " . date('Y') . " Ecobric. Proyecto ASIR. Todos los derechos reservados.<br>
                                <a href='mailto:ecobricsoporte@gmail.com' style='color: #2e7d32; text-decoration: none;'>ecobricsoporte@gmail.com</a>
                            </div>
                        </div>
                    ";

                    // Enviar correo real usando la configuración global
                    $emailEnviado = enviarCorreo($user['email'], $asunto, $html_correo);

                    // Limpiar carrito
                    $_SESSION['cart'] = [];
                    $mensajeEmail = $emailEnviado ? " Hemos enviado los detalles a tu email." : " (Error al enviar el email de confirmación).";
                    $mensaje = "¡Pedido #" . str_pad($pedido_id, 5, '0', STR_PAD_LEFT) . " realizado con éxito! " . $mensajeEmail;
                }
            } catch (\Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $mensaje = "Error al procesar el pedido. Detalle técnico: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}

include '../includes/header.php';

// Cálculos del carrito para el HTML
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
$envio = ($subtotal > 0) ? 25.00 : 0;
$total = $subtotal + $envio;
?>

<div class="page-header" style="background-color: var(--text-dark); padding: 4rem 0; color: white; text-align: center;">
    <div class="container">
        <h1 style="color: white; margin-bottom: 0.5rem;"><i class="fa-solid fa-shopping-cart"></i> Mi Carrito</h1>
        <p>Revisa tus materiales antes de proceder al pago.</p>
    </div>
</div>

<section class="section-padding">
    <div class="container">

        <?php if ($mensaje): ?>
            <div
                style="background-color: #d4edda; color: #155724; padding: 1.5rem; text-align: center; font-size: 1.2rem; border-radius: 8px; margin-bottom: 2rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                <?php echo htmlspecialchars($mensaje); ?>
                <br>
                <a href="../index.php" class="btn btn-primary" style="margin-top: 1rem;"><i
                        class="fa-solid fa-arrow-left"></i> Volver a la tienda</a>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;" class="cart-grid">

            <!-- Lista de Materiales -->
            <div>
                <h3 style="margin-bottom: 1.5rem;">Cesta de la Compra</h3>

                <?php if (empty($_SESSION['cart']) && !$mensaje): ?>
                    <div
                        style="text-align: center; padding: 4rem; background: white; border-radius: 8px; box-shadow: var(--shadow-sm);">
                        <i class="fa-solid fa-basket-shopping"
                            style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h4 style="color: var(--text-muted);">Tu carrito está vacío</h4>
                        <a href="catalogo.php" class="btn btn-primary" style="margin-top: 1rem;">Explorar Catálogo</a>
                    </div>
                <?php else: ?>
                    <table
                        style="width: 100%; border-collapse: collapse; background: white; box-shadow: var(--shadow-sm); border-radius: var(--border-radius); overflow: hidden;">
                        <thead style="background-color: var(--primary-light); color: white;">
                            <tr>
                                <th style="padding: 1rem; text-align: left;">Producto</th>
                                <th style="padding: 1rem; text-align: center;">Cantidad</th>
                                <th style="padding: 1rem; text-align: right;">Precio Unit.</th>
                                <th style="padding: 1rem; text-align: right;">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                            style="width: 50px; height: 50px; border-radius: 4px; object-fit: cover;">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: center; font-weight: bold;">
                                        <?php echo $item['quantity']; ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <?php echo number_format($item['price'], 2, ',', '.'); ?> €
                                    </td>
                                    <td style="padding: 1rem; text-align: right; font-weight: bold;">
                                        <?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?> €
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <a href="carrito.php?remove=<?php echo $id; ?>" style="color: var(--danger);"><i
                                                class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Resumen y PAGO -->
            <div>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <div
                        style="background: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
                        <h3
                            style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">
                            Resumen de Compra</h3>

                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span style="color: var(--text-muted);">Subtotal</span>
                            <span style="font-weight: bold;"><?php echo number_format($subtotal, 2, ',', '.'); ?> €</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span style="color: var(--text-muted);">Envío (Provincia Madrid)</span>
                            <span style="font-weight: bold;"><?php echo number_format($envio, 2, ',', '.'); ?> €</span>
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--text-dark); font-size: 1.3rem; font-weight: bold; color: var(--primary-dark);">
                            <span>Total (IVA Inc.)</span>
                            <span><?php echo number_format($total, 2, ',', '.'); ?> €</span>
                        </div>

                        <form action="carrito.php" method="POST" style="margin-top: 2rem;">
                            <input type="hidden" name="checkout" value="1">
                            <input type="hidden" name="total_pagar" value="<?php echo $total; ?>">

                            <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;"><i
                                    class="fa-solid fa-map-location-dot"></i> Dirección de Envío:</label>
                            <input type="text" name="direccion_envio" required
                                placeholder="Ej: Calle Gran Vía 12, 5º Dcha, Madrid"
                                value="<?php echo htmlspecialchars($direccion_defecto_form ?? ''); ?>"
                                style="width: 100%; padding: 0.8rem; margin-bottom: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">

                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                                <input type="checkbox" id="guardar_direccion" name="guardar_direccion" value="1" <?php echo !empty($direccion_defecto_form) ? 'checked' : ''; ?>>
                                <label for="guardar_direccion"
                                    style="font-size: 0.9rem; color: var(--text-muted); cursor: pointer;">Guardar como
                                    predeterminada</label>
                            </div>

                            <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;"><i
                                    class="fa-solid fa-credit-card"></i> Método de Pago:</label>
                            <select name="metodo_pago"
                                style="width: 100%; padding: 0.8rem; margin-bottom: 1.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                                <option value="Tarjeta de Crédito">Tarjeta de Crédito / Débito</option>
                                <option value="Transferencia">Transferencia Bancaria</option>
                                <option value="PayPal">PayPal</option>
                            </select>

                            <button type="submit" class="btn btn-primary"
                                style="width: 100%; text-align: center; font-size: 1.2rem; padding: 1rem;">Comprar y
                                Finalizar</button>
                        </form>
                    </div>

                    
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>