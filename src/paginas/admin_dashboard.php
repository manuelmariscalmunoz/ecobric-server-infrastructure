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

// Lógica para exportar Excel se manejará en otro archivo API

// ----- Estadísticas Rápidas -----
// Total Usuarios
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol_id = 2");
$total_clientes = $stmt->fetchColumn();

// Stock Bajo (menos de 5 unidades)
$stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock < 5");
$stock_bajo = $stmt->fetchColumn();

// Gastos e Ingresos del mes actual
$mes_actual = date('m');
$anio_actual = date('Y');

// Total Ingresos (pedidos pagados del mes)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(monto_total), 0) FROM pedidos WHERE estado = 'PAGADO' AND MONTH(creado_en) = ? AND YEAR(creado_en) = ?");
$stmt->execute([$mes_actual, $anio_actual]);
$ingresos_mes = $stmt->fetchColumn();

// Total Gastos (Entradas por compras a proveedores). Asumiremos que el monto gastado se guardará en notas temporalmente o en una nueva columna. 
// Para ser exactos, si simulamos compras, añadiremos la columna costo_total en movimientos_inventario o extraeremos de la relación producto_proveedor.
// Por ahora, aproximaremos el gasto a: SUM(cantidad * precio proveedor) si tuviéramos ese dato en movimientos.
// En un paso posterior ajustaremos la tabla si es necesario. Por ahora un placeholder de consulta realista:
// Buscamos todas las ENTRADAS (excepto el inicial que pusimos como 'Inventario inicial') y las valoramos (Placeholder).
$stmt = $pdo->prepare("SELECT COALESCE(SUM(cantidad * (SELECT precio_suministro FROM producto_proveedor pp WHERE pp.producto_id = mi.producto_id LIMIT 1)), 0) 
                        FROM movimientos_inventario mi 
                        WHERE tipo_movimiento = 'ENTRADA' AND notas != 'Inventario inicial' AND MONTH(fecha_movimiento) = ? AND YEAR(fecha_movimiento) = ?");
$stmt->execute([$mes_actual, $anio_actual]);
$gastos_mes = $stmt->fetchColumn();


$page_title = "Panel de Administración";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ecobric Admin</title>
    <!-- Fuentes de Google -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Iconos de FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos -->
    <link rel="stylesheet" href="../css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    
    <!-- Navbar superior genérico -->
    <?php include '../includes/header.php'; ?>

    <div class="admin-dashboard">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2>EcoAdmin</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="admin_dashboard.php" class="active"><i class="fa-solid fa-chart-line"></i> Resumen</a></li>
                <li><a href="admin_compras_proveedor.php"><i class="fa-solid fa-truck-fast"></i> Pedidos a Proveedor</a></li>
                <li><a href="admin_productos.php"><i class="fa-solid fa-boxes-stacked"></i> Productos & Stock</a></li>
                <li><a href="admin_usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a></li>
                <li><a href="#" id="btn-export-excel-modal"><i class="fa-solid fa-file-excel"></i> Reporte Excel</a></li>
                <li><a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Volver a Tienda</a></li>
            </ul>
        </aside>

        <!-- Contenido principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Panel de Resumen</h1>
                <div>
                    <span style="color: var(--text-muted);"><i class="fa-regular fa-calendar"></i> <?php echo date('d M Y'); ?></span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <a href="admin_usuarios.php" style="text-decoration: none; color: inherit;">
                    <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                        <div class="stat-info">
                            <h3>Clientes Totales</h3>
                            <p><?php echo $total_clientes; ?></p>
                        </div>
                    </div>
                </a>
                
                <a href="admin_productos.php?filtro=bajo" style="text-decoration: none; color: inherit;">
                    <div class="stat-card warning" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                        <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="stat-info">
                            <h3>Stock Crítico (< 5)</h3>
                            <p><?php echo $stock_bajo; ?></p>
                        </div>
                    </div>
                </a>

                <a href="admin_finanzas.php" style="text-decoration: none; color: inherit;">
                    <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                        <div class="stat-icon" style="background-color: #e3f2fd; color:#1565c0;"><i class="fa-solid fa-arrow-trend-up"></i></div>
                        <div class="stat-info">
                            <h3>Ingresos Venta (Mes)</h3>
                            <p><?php echo number_format($ingresos_mes, 2, ',', '.'); ?> €</p>
                        </div>
                    </div>
                </a>

                <a href="admin_finanzas.php" style="text-decoration: none; color: inherit;">
                    <div class="stat-card accent" style="border-top-color: #d32f2f; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                        <div class="stat-icon" style="background-color: #ffebee; color:#d32f2f;"><i class="fa-solid fa-arrow-trend-down"></i></div>
                        <div class="stat-info">
                            <h3>Gastos Proveedor (Mes)</h3>
                            <p><?php echo number_format($gastos_mes, 2, ',', '.'); ?> €</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Actividad Reciente o Resumen (Opcional) -->
            <div class="admin-panel-section">
                <div class="admin-panel-header">
                    <h2>Últimos Pedidos de Clientes</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nº Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT p.id, u.nombre, p.creado_en, p.estado, p.monto_total 
                                             FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id 
                                             ORDER BY p.creado_en DESC LIMIT 5");
                        while ($row = $stmt->fetch()):
                            $badgeClass = ($row['estado'] == 'PAGADO') ? 'status-active' : (($row['estado'] == 'PENDIENTE') ? 'status-warning' : 'status-inactive');
                        ?>
                        <tr>
                            <td>#<?php echo str_pad($row['id'], 5, "0", STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['creado_en'])); ?></td>
                            <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo $row['estado']; ?></span></td>
                            <td><?php echo number_format($row['monto_total'], 2); ?> €</td>
                            <td>
                                <button class="btn btn-outline" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;" onclick="verDetallesAdmin(<?php echo $row['id']; ?>)">
                                    <i class="fa-solid fa-eye"></i> Ver
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($stmt->rowCount() == 0): ?>
                            <tr><td colspan="5" style="text-align: center;">No hay pedidos recientes.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    <?php include '../includes/admin_excel_modal.php'; ?>

    <!-- Modal Detalles Pedido Admin -->
    <div id="modalDetallesAdmin" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: var(--border-radius); width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1rem;">
                <h2 style="margin: 0;">Detalles Admin Pedido <span id="modalAdminPedidoId"></span></h2>
                <button onclick="cerrarModalDetallesAdmin()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            <div id="modalAdminContenido">
                <p style="text-align: center; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Cargando detalles...</p>
            </div>
        </div>
    </div>

    <script>
        function verDetallesAdmin(pedidoId) {
            document.getElementById('modalDetallesAdmin').style.display = 'flex';
            document.getElementById('modalAdminPedidoId').innerText = "#" + String(pedidoId).padStart(5, '0');
            document.getElementById('modalAdminContenido').innerHTML = '<p style="text-align: center; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Cargando detalles...</p>';
            
            fetch('../api/get_order_details.php?id=' + pedidoId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div style="background-color: #f0f4f8; padding: 10px; border-radius: 4px; margin-bottom: 15px;">' +
                                   '<h4 style="margin: 0 0 5px 0; color: #2e7d32; font-size: 0.9rem;">Destino de Envío:</h4>' +
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
                        
                        document.getElementById('modalAdminContenido').innerHTML = html;
                    } else {
                        document.getElementById('modalAdminContenido').innerHTML = '<p style="color: var(--danger); text-align: center;">' + data.message + '</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('modalAdminContenido').innerHTML = '<p style="color: var(--danger); text-align: center;">Error de conexión.</p>';
                });
        }

        function cerrarModalDetallesAdmin() {
            document.getElementById('modalDetallesAdmin').style.display = 'none';
        }
    </script>
</body>
</html>
