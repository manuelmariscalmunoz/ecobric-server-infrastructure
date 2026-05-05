<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Procesar Actualización de Contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Verificar actual
    $stmt = $pdo->prepare("SELECT contrasena FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($current_pass, $user['contrasena'])) {
        if ($new_pass !== $confirm_pass) {
            $error = "La nueva contraseña no coincide.";
        } elseif (strlen($new_pass) < 8 || !preg_match('/[A-Z]/', $new_pass) || !preg_match('/[a-z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass) || !preg_match('/[^a-zA-Z0-9]/', $new_pass)) {
            $error = "La contraseña debe tener al menos 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial.";
        } else {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
            if ($update->execute([$hash, $user_id])) {
                $success = "Contraseña actualizada exitosamente.";
            } else {
                $error = "Error al actualizar la base de datos.";
            }
        }
    } else {
        $error = "La contraseña actual es incorrecta.";
    }
}

// Procesar Actualización de Dirección
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_address'])) {
    $nueva_direccion = trim($_POST['direccion_predeterminada']);
    $stmtAddrUpdate = $pdo->prepare("UPDATE usuarios SET direccion_predeterminada = ? WHERE id = ?");
    if ($stmtAddrUpdate->execute([$nueva_direccion, $user_id])) {
        $success = "Dirección predeterminada actualizada.";
    } else {
        $error = "Error al actualizar la dirección.";
    }
}

// Obtener datos de usuario actuales
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch();

// Cargar pedidos (Usando creado_en y monto_total)
$stmtPedidos = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY creado_en DESC");
$stmtPedidos->execute([$user_id]);
$misPedidos = $stmtPedidos->fetchAll();

include '../includes/header.php';
?>

<div class="page-header" style="background-color: var(--primary-light); padding: 3rem 0; color: white;">
    <div class="container">
        <h1><i class="fa-solid fa-user-circle"></i> Mi Perfil</h1>
        <p>Gestiona tu cuenta y revisa tus pedidos.</p>
    </div>
</div>

<section class="section-padding" style="background-color: var(--bg-light); min-height: 60vh;">
    <div class="container">

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 3rem;">

            <!-- Datos Personales y Contraseña -->
            <div>
                <div
                    style="background: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
                    <h3
                        style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        Mis Datos Completos</h3>
                    <p><strong>Nombre:</strong>
                        <?php echo htmlspecialchars($userData['nombre']); ?>
                    </p>
                    <p><strong>Email:</strong>
                        <?php echo htmlspecialchars($userData['email']); ?>
                    </p>
                    <p><strong>Estado:</strong> <span style="color: var(--primary-color); font-weight: bold;">Verificado
                            <i class="fa-solid fa-check-circle"></i></span></p>
                </div>

                <div
                    style="background: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
                    <h3
                        style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        Dirección Predeterminada de Envío</h3>

                    <form method="POST" action="perfil.php" style="display: flex; flex-direction: column; gap: 1rem;">
                        <input type="hidden" name="update_address" value="1">
                        <div>
                            <textarea name="direccion_predeterminada" rows="3"
                                placeholder="Ej: Calle Mayor 12, Código Postal, Ciudad"
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none; font-family: inherit; resize: vertical;"><?php echo htmlspecialchars($userData['direccion_predeterminada'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-outline" style="align-self: flex-start;">Guardar
                            Dirección</button>
                    </form>
                </div>

                <div
                    style="background: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                    <h3
                        style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        Cambiar Contraseña</h3>

                    <?php if ($success): ?>
                        <div
                            style="padding: 1rem; background-color: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div
                            style="padding: 1rem; background-color: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="perfil.php" style="display: flex; flex-direction: column; gap: 1rem;">
                        <input type="hidden" name="update_password" value="1">
                        <div>
                            <label style="font-weight: 500;">Contraseña Actual</label>
                            <input type="password" name="current_password" required
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none;">
                        </div>
                        <div>
                            <label style="font-weight: 500;">Nueva Contraseña</label>
                            <input type="password" name="new_password" required minlength="8"
                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}"
                                title="Debe contener al menos 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial."
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none;">
                        </div>
                        <div>
                            <label style="font-weight: 500;">Confirmar Nueva</label>
                            <input type="password" name="confirm_password" required minlength="8"
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none;">
                        </div>
                        <button type="submit" class="btn btn-outline"
                            style="align-self: flex-start; margin-top: 0.5rem;">Actualizar</button>
                    </form>
                </div>
            </div>

            <!-- Historial de Pedidos -->
            <div>
                <div
                    style="background: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                    <h3
                        style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        <i class="fa-solid fa-box-open"></i> Historial de Pedidos
                    </h3>

                    <?php if (count($misPedidos) > 0): ?>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr
                                        style="background-color: var(--bg-light); border-bottom: 1px solid var(--border-color);">
                                        <th style="padding: 1rem; text-align: left;">ID Pedido</th>
                                        <th style="padding: 1rem; text-align: left;">Fecha</th>
                                        <th style="padding: 1rem; text-align: right;">Total</th>
                                        <th style="padding: 1rem; text-align: center;">Método</th>
                                        <th style="padding: 1rem; text-align: center;">Estado</th>
                                        <th style="padding: 1rem; text-align: center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($misPedidos as $ped): ?>
                                        <tr style="border-bottom: 1px solid var(--border-color);">
                                            <td style="padding: 1rem;"><strong>#
                                                    <?php echo str_pad($ped['id'], 5, '0', STR_PAD_LEFT); ?>
                                                </strong></td>
                                            <td style="padding: 1rem; color: var(--text-muted);">
                                                <?php echo date('d/m/Y H:i', strtotime($ped['creado_en'])); ?>
                                            </td>
                                            <td style="padding: 1rem; text-align: right; font-weight: bold;">
                                                <?php echo number_format($ped['monto_total'], 2, ',', '.'); ?> €
                                            </td>
                                            <td style="padding: 1rem; text-align: center;">
                                                <?php echo htmlspecialchars($ped['metodo_pago']); ?>
                                            </td>
                                            <td style="padding: 1rem; text-align: center;">
                                                <span
                                                    style="background-color: #e8f5e9; color: #2e7d32; padding: 0.3rem 0.6rem; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">
                                                    <?php echo htmlspecialchars($ped['estado']); ?>
                                                </span>
                                            </td>
                                            <td style="padding: 1rem; text-align: center;">
                                                <button class="btn btn-outline"
                                                    style="padding: 0.3rem 0.6rem; font-size: 0.85rem;"
                                                    onclick="verDetalles(<?php echo $ped['id']; ?>)">
                                                    <i class="fa-solid fa-eye"></i> Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                            <i class="fa-solid fa-receipt" style="font-size: 3rem; margin-bottom: 1rem; color: #ccc;"></i>
                            <p>Aún no has realizado ninguna compra con Ecobric.</p>
                            <a href="catalogo.php" class="btn btn-primary" style="margin-top: 1rem;">Ir al Catálogo</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Modal Detalles Pedido -->
<div id="modalDetalles"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div
        style="background: white; padding: 2rem; border-radius: var(--border-radius); width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
        <div
            style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1rem;">
            <h2 style="margin: 0;">Detalles del Pedido <span id="modalPedidoId"></span></h2>
            <button onclick="cerrarModalDetalles()"
                style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <div id="modalContenido">
            <p style="text-align: center; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i>
                Cargando detalles...</p>
        </div>
    </div>
</div>

<script>
    function verDetalles(pedidoId) {
        document.getElementById('modalDetalles').style.display = 'flex';
        document.getElementById('modalPedidoId').innerText = "#" + String(pedidoId).padStart(5, '0');
        document.getElementById('modalContenido').innerHTML = '<p style="text-align: center; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Cargando detalles...</p>';

        fetch('../api/get_order_details.php?id=' + pedidoId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<div style="background-color: #f0f4f8; padding: 10px; border-radius: 4px; margin-bottom: 15px;">' +
                        '<h4 style="margin: 0 0 5px 0; color: #2e7d32; font-size: 0.9rem;">Enviado a:</h4>' +
                        '<p style="margin: 0; font-size: 0.95rem;">' + data.direccion_envio + '</p></div>' +
                        '<table style="width: 100%; border-collapse: collapse;">' +
                        '<thead style="background: var(--bg-light); border-bottom: 1px solid var(--border-color);">' +
                        '<tr><th style="padding: 0.5rem; text-align: left;">Producto</th>' +
                        '<th style="padding: 0.5rem; text-align: center;">Cant.</th>' +
                        '<th style="padding: 0.5rem; text-align: right;">Precio</th>' +
                        '<th style="padding: 0.5rem; text-align: right;">Subtotal</th></tr></thead><tbody>';
                    let total = 0;
                    data.detalles.forEach(item => {
                        let subtotal = item.cantidad * item.precio_unitario;
                        total += subtotal;
                        html += '<tr style="border-bottom: 1px solid #eee;">' +
                            '<td style="padding: 0.5rem;">' + item.nombre + '</td>' +
                            '<td style="padding: 0.5rem; text-align: center;">' + item.cantidad + '</td>' +
                            '<td style="padding: 0.5rem; text-align: right;">' + parseFloat(item.precio_unitario).toFixed(2) + ' €</td>' +
                            '<td style="padding: 0.5rem; text-align: right; font-weight: bold;">' + subtotal.toFixed(2) + ' €</td>' +
                            '</tr>';
                    });
                    html += '</tbody></table>';

                    document.getElementById('modalContenido').innerHTML = html;
                } else {
                    document.getElementById('modalContenido').innerHTML = '<p style="color: var(--danger); text-align: center;">' + data.message + '</p>';
                }
            })
            .catch(error => {
                document.getElementById('modalContenido').innerHTML = '<p style="color: var(--danger); text-align: center;">Error de conexión.</p>';
            });
    }

    function cerrarModalDetalles() {
        document.getElementById('modalDetalles').style.display = 'none';
    }
</script>

<?php include '../includes/footer.php'; ?>