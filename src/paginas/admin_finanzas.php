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

$mes_actual = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio_actual = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

// Obtener Ingresos (Pedidos pagados del mes)
$stmt_ingresos = $pdo->prepare("SELECT p.id as pedido_id, u.nombre as cliente, p.monto_total, p.creado_en 
                                FROM pedidos p 
                                JOIN usuarios u ON p.usuario_id = u.id 
                                WHERE p.estado = 'PAGADO' AND MONTH(p.creado_en) = ? AND YEAR(p.creado_en) = ?
                                ORDER BY p.creado_en DESC");
$stmt_ingresos->execute([$mes_actual, $anio_actual]);
$ingresos = $stmt_ingresos->fetchAll();

$total_ingresos = 0;
foreach ($ingresos as $ingreso) {
    $total_ingresos += $ingreso['monto_total'];
}

// Obtener Gastos (Movimientos INVENTARIO ENTRADA y multiplicar por su precio de suministro)
$stmt_gastos = $pdo->prepare("SELECT mi.id as mov_id, pr.nombre as producto, mi.cantidad, 
                                     mi.fecha_movimiento,
                                     (SELECT precio_suministro FROM producto_proveedor pp WHERE pp.producto_id = mi.producto_id LIMIT 1) as precio_costo,
                                     (SELECT prov.nombre_empresa FROM proveedores prov JOIN producto_proveedor pp ON prov.id = pp.proveedor_id WHERE pp.producto_id = mi.producto_id LIMIT 1) as nombre_proveedor
                              FROM movimientos_inventario mi 
                              JOIN productos pr ON mi.producto_id = pr.id 
                              WHERE mi.tipo_movimiento = 'ENTRADA' AND mi.notas != 'Inventario inicial' 
                              AND MONTH(mi.fecha_movimiento) = ? AND YEAR(mi.fecha_movimiento) = ?
                              ORDER BY mi.fecha_movimiento DESC");
$stmt_gastos->execute([$mes_actual, $anio_actual]);
$gastos = $stmt_gastos->fetchAll();

$total_gastos = 0;
foreach ($gastos as $key => $gasto) {
    $costo = floatval($gasto['precio_costo'] ?? 0);
    $total_gasto_unidad = $gasto['cantidad'] * $costo;
    $gastos[$key]['gasto_total'] = $total_gasto_unidad;
    $total_gastos += $total_gasto_unidad;
}

$balance = $total_ingresos - $total_gastos;

$page_title = "Reporte Financiero ERP";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finanzas - Ecobric Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <style>
        .finance-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }
        .balance-positive { color: #2e7d32; }
        .balance-negative { color: #d32f2f; }
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
                <li><a href="admin_compras_proveedor.php"><i class="fa-solid fa-truck-fast"></i> Pedidos a Proveedor</a></li>
                <li><a href="admin_productos.php"><i class="fa-solid fa-boxes-stacked"></i> Productos & Stock</a></li>
                <li><a href="admin_usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a></li>
                <li><a href="#" id="btn-export-excel-modal"><i class="fa-solid fa-file-excel"></i> Reporte Excel</a></li>
                <li><a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Volver a Tienda</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Detalles Financieros (<?php 
                    $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                    echo $meses[intval($mes_actual)] . " " . $anio_actual; 
                ?>)</h1>
                
                <!-- Filtro de fechas rápido -->
                <form method="GET" action="" style="display: flex; gap: 10px;">
                    <select name="mes" class="search-bar" style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php echo $mes_actual == $i ? 'selected' : ''; ?>>
                                <?php echo $meses[$i]; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="anio" class="search-bar" style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <?php for($y=2025; $y<=date('Y')+1; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo $anio_actual == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;"><i class="fa-solid fa-filter"></i> Filtrar</button>
                </form>
            </div>

            <!-- Resumen Contable Grandioso -->
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 3rem;">
                <div class="stat-card" style="background: #f1f8e9; border: 2px solid #8bc34a;">
                    <div class="stat-icon" style="background-color: #c5e1a5; color:#33691e;"><i class="fa-solid fa-cart-arrow-down"></i></div>
                    <div class="stat-info">
                        <h3>Total Ingresos (Ventas)</h3>
                        <p style="font-size: 2rem; color: #2e7d32;">+<?php echo number_format($total_ingresos, 2, ',', '.'); ?> €</p>
                    </div>
                </div>
                
                <div class="stat-card" style="background: #ffebee; border: 2px solid #ef5350;">
                    <div class="stat-icon" style="background-color: #ffcdd2; color:#b71c1c;"><i class="fa-solid fa-truck-ramp-box"></i></div>
                    <div class="stat-info">
                        <h3>Total Gastos (Costes Prov.)</h3>
                        <p style="font-size: 2rem; color: #d32f2f;">-<?php echo number_format($total_gastos, 2, ',', '.'); ?> €</p>
                    </div>
                </div>

                <div class="stat-card" style="border: 3px solid var(--primary-color);">
                    <div class="stat-icon" style="background-color: var(--primary-light); color:white;"><i class="fa-solid fa-scale-balanced"></i></div>
                    <div class="stat-info">
                        <h3>Balance Neto Mensual</h3>
                        <p style="font-size: 2.2rem;" class="<?php echo $balance >= 0 ? 'balance-positive' : 'balance-negative'; ?>">
                            <?php echo $balance >= 0 ? '+' : ''; ?><?php echo number_format($balance, 2, ',', '.'); ?> €
                        </p>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Desglose Ingresos -->
                <div class="finance-card">
                    <h2 style="color: #2e7d32; border-bottom: 2px solid #c5e1a5; padding-bottom: 1rem; margin-bottom: 1rem;">
                        <i class="fa-solid fa-arrow-up-right-dots"></i> Desglose Ingresos (<?php echo count($ingresos); ?> pedidos)
                    </h2>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="admin-table" style="font-size: 0.9rem;">
                            <thead>
                                <tr>
                                    <th>Ref. Pedido</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ingresos as $ingreso): ?>
                                <tr>
                                    <td>#<?php echo str_pad($ingreso['pedido_id'], 5, "0", STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($ingreso['cliente']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($ingreso['creado_en'])); ?></td>
                                    <td style="color: #2e7d32; font-weight: bold;">+<?php echo number_format($ingreso['monto_total'], 2); ?> €</td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($ingresos)): ?>
                                    <tr><td colspan="4" style="text-align: center;">Sin ingresos este mes.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Desglose Gastos -->
                <div class="finance-card">
                    <h2 style="color: #d32f2f; border-bottom: 2px solid #ffcdd2; padding-bottom: 1rem; margin-bottom: 1rem;">
                        <i class="fa-solid fa-arrow-down-right-dots"></i> Desglose Gastos (<?php echo count($gastos); ?> compras)
                    </h2>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="admin-table" style="font-size: 0.9rem;">
                            <thead>
                                <tr>
                                    <th>Ref. Mov.</th>
                                    <th>Material / Unidades</th>
                                    <th>Proveedor</th>
                                    <th>Precio Ud.</th>
                                    <th>Total Gasto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($gastos as $gasto): 
                                    $nombre_prov = $gasto['nombre_proveedor'] ? htmlspecialchars($gasto['nombre_proveedor']) : '<i>No Asignado</i>';
                                    $precio_ud = floatval($gasto['precio_costo'] ?? 0);
                                ?>
                                <tr>
                                    <td>MOV-<?php echo str_pad($gasto['mov_id'], 4, "0", STR_PAD_LEFT); ?><br><small style="color:#888;"><?php echo date('d/m/Y', strtotime($gasto['fecha_movimiento'])); ?></small></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($gasto['producto']); ?></strong><br>
                                        <span style="color:var(--text-muted); font-size: 0.85em;">+<?php echo $gasto['cantidad'];?> uds ingresadas</span>
                                    </td>
                                    <td><?php echo $nombre_prov; ?></td>
                                    <td><?php echo number_format($precio_ud, 2); ?> €</td>
                                    <td style="color: #d32f2f; font-weight: bold;">-<?php echo number_format($gasto['gasto_total'], 2); ?> €</td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($gastos)): ?>
                                    <tr><td colspan="5" style="text-align: center;">Sin reabastecimientos (gastos) este mes.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
    
    <?php include '../includes/admin_excel_modal.php'; ?>
</body>
</html>
