<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: ../index.php");
    exit();
}

$mensaje = "";

// PROCESAR COMPRA MASIVA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'comprar_bloque') {
    $proveedor_id = intval($_POST['proveedor_id']);
    $cantidades = $_POST['cantidades'] ?? []; // Array de producto_id => cantidad

    $total_gasto = 0;
    $productos_comprados = 0;

    // Iniciar transacción para que sea atómico
    $pdo->beginTransaction();
    try {
        foreach ($cantidades as $prod_id => $cantidad) {
            $cant = intval($cantidad);
            if ($cant > 0) {
                // Obtener coste unitario específico para ESTE proveedor y ESTE producto
                $stmtPrecio = $pdo->prepare("SELECT precio_suministro FROM producto_proveedor WHERE producto_id = ? AND proveedor_id = ?");
                $stmtPrecio->execute([$prod_id, $proveedor_id]);
                $precio_unidad = $stmtPrecio->fetchColumn();

                if ($precio_unidad !== false) {
                    $costo_fila = $cant * floatval($precio_unidad);
                    $total_gasto += $costo_fila;
                    $productos_comprados++;

                    // 1. Aumentar Stock Físico
                    $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
                    $stmtStock->execute([$cant, $prod_id]);

                    // 2. Registrar el movimiento financiero/inventario
                    $nota = "Pedido Masivo Proveedor #$proveedor_id - Costo: " . number_format($costo_fila, 2, '.', '') . "€";
                    $stmtMov = $pdo->prepare("INSERT INTO movimientos_inventario (producto_id, tipo_movimiento, cantidad, notas) VALUES (?, 'ENTRADA', ?, ?)");
                    $stmtMov->execute([$prod_id, $cant, $nota]);
                }
            }
        }
        $pdo->commit();
        if ($productos_comprados > 0) {
            $mensaje = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> <strong>Pedido Procesado:</strong> Se han abastecido $productos_comprados artículos diferentes. Gasto total registrado: <strong>" . number_format($total_gasto, 2) . " €</strong>.</div>";
        } else {
            $mensaje = "<div class='alert alert-warning'>No se indicaron cantidades válidas para comprar.</div>";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensaje = "<div class='alert alert-danger'>Error al procesar albarán masivo: " . $e->getMessage() . "</div>";
    }
}

// OBTENER LISTA DE PROVEEDORES PARA EL SELECTOR
$stmtProv = $pdo->query("SELECT id, nombre_empresa FROM proveedores ORDER BY nombre_empresa");
$proveedores = $stmtProv->fetchAll();

// OBTENER PROVEEDOR SELECCIONADO (Si hay)
$proveedor_seleccionado = isset($_GET['prov_id']) ? intval($_GET['prov_id']) : (count($proveedores) > 0 ? $proveedores[0]['id'] : 0);

// CARGAR CATÁLOGO DE ESTE PROVEEDOR
$productos_proveedor = [];
if ($proveedor_seleccionado > 0) {
    $stmtProd = $pdo->prepare("SELECT p.id, p.nombre, p.stock, c.nombre as categoria, pp.precio_suministro 
                               FROM productos p 
                               JOIN producto_proveedor pp ON p.id = pp.producto_id 
                               JOIN categorias c ON p.categoria_id = c.id
                               WHERE pp.proveedor_id = ? 
                               ORDER BY c.nombre, p.nombre");
    $stmtProd->execute([$proveedor_seleccionado]);
    $productos_proveedor = $stmtProd->fetchAll();
}

$page_title = "Pedidos a Proveedores";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Masivos - Ecobric Admin</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <style>
        .supplier-selector {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .order-cart {
            position: sticky;
            top: 20px;
            background: #f8f9fa;
            border: 2px solid var(--primary-color);
            padding: 1.5rem;
            border-radius: var(--border-radius);
        }

        .qty-input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="admin-dashboard">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2>EcoAdmin</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Resumen</a></li>
                <li><a href="admin_compras_proveedor.php" class="active"><i class="fa-solid fa-truck-fast"></i> Pedidos
                        a Proveedor</a></li>
                <li><a href="admin_productos.php"><i class="fa-solid fa-boxes-stacked"></i> Productos & Stock</a></li>
                <li><a href="admin_usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a></li>
                <li><a href="#" id="btn-export-excel-modal"><i class="fa-solid fa-file-excel"></i> Reporte Excel</a>
                </li>
                <li><a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Volver a Tienda</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Abastecimiento B2B (Albaranes)</h1>
            </div>

            <?php echo $mensaje; ?>

            <!-- SELECTOR DE PROVEEDOR -->
            <div class="supplier-selector">
                <div style="flex-grow: 1;">
                    <h3 style="margin-bottom: 0.5rem; color: var(--primary-dark);"><i class="fa-solid fa-industry"></i>
                        Seleccione un Proveedor para ver su catálogo:</h3>
                    <form method="GET" action="" style="display: flex; gap: 10px;">
                        <select name="prov_id" class="search-bar"
                            style="max-width: 400px; padding: 0.8rem; font-size: 1.1rem; border: 2px solid var(--border-color); border-radius: 6px;"
                            onchange="this.form.submit()">
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?php echo $prov['id']; ?>" <?php echo $prov['id'] == $proveedor_seleccionado ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prov['nombre_empresa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button type="submit" class="btn btn-outline">Cargar</button></noscript>
                    </form>
                </div>
            </div>

            <!-- FORMULARIO DE COMPRA MASIVA -->
            <?php if ($proveedor_seleccionado > 0 && count($productos_proveedor) > 0): ?>
                <form method="POST" action="" id="massOrderForm">
                    <input type="hidden" name="action" value="comprar_bloque">
                    <input type="hidden" name="proveedor_id" value="<?php echo $proveedor_seleccionado; ?>">

                    <div style="display: grid; grid-template-columns: 2.5fr 1fr; gap: 2rem; align-items: start;">

                        <!-- Lista de Productos -->
                        <div class="admin-panel-section" style="margin: 0;">
                            <table class="admin-table">
                                <thead style="background: var(--primary-dark); color: white;">
                                    <tr>
                                        <th>Ref.</th>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th style="text-align: right;">Costo Proveedor</th>
                                        <th>Stock Actual</th>
                                        <th style="text-align: center;">Cantidad a Pedir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos_proveedor as $p): ?>
                                        <tr>
                                            <td><small style="color: #666;">#
                                                    <?php echo str_pad($p['id'], 3, "0", STR_PAD_LEFT); ?>
                                                </small></td>
                                            <td><strong>
                                                    <?php echo htmlspecialchars($p['nombre']); ?>
                                                </strong></td>
                                            <td><span class="status-badge" style="background:#f4f6f9; color:var(--text-muted);">
                                                    <?php echo htmlspecialchars($p['categoria']); ?>
                                                </span></td>
                                            <td style="text-align: right; color: #d32f2f; font-weight: 500;">
                                                <?php echo number_format($p['precio_suministro'], 2); ?> €
                                                <input type="hidden" id="costo_<?php echo $p['id']; ?>"
                                                    value="<?php echo $p['precio_suministro']; ?>">
                                            </td>
                                            <td>
                                                <?php if ($p['stock'] < 5): ?>
                                                    <span style="color:var(--danger); font-weight:bold;"><i
                                                            class="fa-solid fa-warning"></i>
                                                        <?php echo $p['stock']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color:var(--primary-color); font-weight:bold;">
                                                        <?php echo $p['stock']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <input type="number" name="cantidades[<?php echo $p['id']; ?>]"
                                                    id="cant_<?php echo $p['id']; ?>" class="qty-input" min="0" value=""
                                                    placeholder="0" oninput="calculateTotal()">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Resumen del Pedido (Carrito Float) -->
                        <div class="order-cart">
                            <h2
                                style="color: var(--primary-dark); border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">
                                <i class="fa-solid fa-boxes-packing"></i> Resumen de Albarán
                            </h2>

                            <div
                                style="margin-bottom: 15px; font-size: 1.1rem; display: flex; justify-content: space-between;">
                                <span>Artículos elegidos:</span>
                                <strong id="summary-items">0 uds</strong>
                            </div>

                            <div
                                style="margin-bottom: 25px; font-size: 1.3rem; display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px; border-radius: 8px; border-left: 5px solid #d32f2f;">
                                <span>Coste Estimado:</span>
                                <strong id="summary-cost" style="color: #d32f2f; font-size: 1.6rem;">0.00 €</strong>
                            </div>

                            <button type="submit" class="btn btn-primary" id="btn-submit-order"
                                style="width: 100%; font-size: 1.2rem; padding: 1rem; background-color: #d32f2f; border-color: #b71c1c;"
                                disabled>
                                <i class="fa-solid fa-signature"></i> Autorizar Compra
                            </button>

                            <p style="text-align: center; color: var(--text-muted); font-size: 0.85rem; margin-top: 15px;">
                                <i class="fa-solid fa-info-circle"></i> Al autorizar, el stock se sumará inmediatamente al
                                almacén y el importe se reflejará en Finanzas como Gasto.
                            </p>
                        </div>
                    </div>
                </form>
            <?php elseif ($proveedor_seleccionado > 0): ?>
                <div class="alert alert-warning">
                    <i class="fa-solid fa-box-open"></i> Este proveedor no tiene productos asignados en nuestro catálogo
                    actualmente.
                </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- Script de Totales Dinámicos -->
    <script>
        function calculateTotal() {
            let totalItems = 0;
            let totalCost = 0.0;
            let inputs = document.querySelectorAll('.qty-input');

            inputs.forEach(function (input) {
                let qty = parseInt(input.value) || 0;
                if (qty > 0) {
                    let id = input.id.split('_')[1];
                    let costoStr = document.getElementById('costo_' + id).value;
                    let costo = parseFloat(costoStr) || 0;

                    totalItems += qty;
                    totalCost += (qty * costo);
                }
            });

            document.getElementById('summary-items').innerText = totalItems + " uds";

            // Format Number API
            let formatter = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' });
            document.getElementById('summary-cost').innerText = formatter.format(totalCost);

            // Enable/Disable Button
            let btn = document.getElementById('btn-submit-order');
            if (totalItems > 0) {
                btn.disabled = false;
                btn.style.opacity = 1;
            } else {
                btn.disabled = true;
                btn.style.opacity = 0.5;
            }
        }
    </script>

    <?php include '../includes/admin_excel_modal.php'; ?>
</body>

</html>