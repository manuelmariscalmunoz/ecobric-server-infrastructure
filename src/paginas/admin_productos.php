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

$mensaje = '';

// Lógica para Simular COMPRA a un proveedor para reabastecer stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'compra_proveedor') {
    $producto_id = $_POST['producto_id'];
    $cantidad_comprada = $_POST['cantidad'];

    // Obtenemos el precio de suministro vinculado a este producto (el primero que encontremos si hay varios)
    $stmt = $pdo->prepare("SELECT precio_suministro FROM producto_proveedor WHERE producto_id = ? LIMIT 1");
    $stmt->execute([$producto_id]);
    $precio_costo = $stmt->fetchColumn();

    if ($precio_costo !== false) {
        // En un caso real crearías una Orden de Compra. Nosotros inyectaremos un movimiento
        // El costo total = cantidad * precio_suministro
        $costo_total = $cantidad_comprada * $precio_costo;
        $nota = "Compra a Pveedor - Costo: " . $costo_total . "€";

        $pdo->beginTransaction();
        try {
            // 1. Aumentar stock del producto
            $stmtUpdate = $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            $stmtUpdate->execute([$cantidad_comprada, $producto_id]);

            // 2. Registrar movimiento como ENTRADA
            $stmtInsert = $pdo->prepare("INSERT INTO movimientos_inventario (producto_id, tipo_movimiento, cantidad, notas) VALUES (?, 'ENTRADA', ?, ?)");
            $stmtInsert->execute([$producto_id, $cantidad_comprada, $nota]);

            $pdo->commit();
            $mensaje = "<div class='alert alert-success'>Stock abastecido correctamente. La compra representa un gasto de <strong>" . number_format($costo_total, 2) . " €</strong> registrado.</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = "<div class='alert alert-danger'>Error en la base de datos al realizar compra: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-danger'>No se ha encontrado proveedor registrado para este producto. No se puede calcular el precio de suministro.</div>";
    }
}

// Lógica para Editar Producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'editar_producto') {
    $id = $_POST['edit_id'];
    $nombre = trim($_POST['edit_nombre']);
    $precio = floatval($_POST['edit_precio']);
    $categoria_id = intval($_POST['edit_categoria']);

    try {
        $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, precio = ?, categoria_id = ? WHERE id = ?");
        $stmt->execute([$nombre, $precio, $categoria_id, $id]);
        $mensaje = "<div class='alert alert-success'>Producto actualizado correctamente.</div>";
    } catch (Exception $e) {
        $mensaje = "<div class='alert alert-danger'>Error al actualizar: " . $e->getMessage() . "</div>";
    }
}

// Obtener categorías para el modal de edición y filtros
$stmtCats = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre");
$categorias_lista = $stmtCats->fetchAll();

// Obtener proveedores para filtros
$stmtProv = $pdo->query("SELECT id, nombre_empresa FROM proveedores ORDER BY nombre_empresa");
$proveedores_lista = $stmtProv->fetchAll();

// Obtener la lista de productos
$filtro_stock = isset($_GET['filtro']) ? $_GET['filtro'] : '';

$query = "SELECT p.id, p.nombre, p.stock, p.precio, p.es_calculable_volumen, p.categoria_id, c.nombre as categoria, 
                 GROUP_CONCAT(pr.nombre_empresa SEPARATOR ', ') as proveedores
          FROM productos p 
          JOIN categorias c ON p.categoria_id = c.id
          LEFT JOIN producto_proveedor pp ON p.id = pp.producto_id
          LEFT JOIN proveedores pr ON pp.proveedor_id = pr.id
          GROUP BY p.id";

if ($filtro_stock === 'bajo') {
    $query .= " HAVING p.stock < 5";
}

$query .= " ORDER BY p.stock ASC";
$stmt = $pdo->query($query);
$productos = $stmt->fetchAll();

$page_title = "Gestión de Productos";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inventario / Productos - Ecobric Admin</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
            color: #aaa;
        }

        .close:hover {
            color: #333;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <li><a href="admin_compras_proveedor.php"><i class="fa-solid fa-truck-fast"></i> Pedidos a Proveedor</a>
                </li>
                <li><a href="admin_productos.php" class="active"><i class="fa-solid fa-boxes-stacked"></i> Productos &
                        Stock</a></li>
                <li><a href="admin_usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a></li>
                <li><a href="#" id="btn-export-excel-modal"><i class="fa-solid fa-file-excel"></i> Reporte Excel</a>
                </li>
                <li><a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Volver a Tienda</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Almacén e Inventario</h1>
                <!-- Dejamos el de crear producto limpio (visualmente sin JS por ahora para enfocarnos en Compras a proveedor) -->
                <button class="btn btn-outline" style="border-color: transparent;"><i
                        class="fa-solid fa-layer-group"></i> Total Artículos:
                    <?php echo count($productos); ?>
                </button>
            </div>

            <?php echo $mensaje; ?>

            <div class="admin-panel-section">
                <div class="admin-panel-header" style="flex-wrap: wrap; gap: 1rem;">
                    <h2>Gestión de Stock Activo</h2>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; width: 100%; justify-content: flex-end;">
                        <select id="filterCategory" onchange="filterTable()" class="search-bar"
                            style="width: auto; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="">Todas las Categorías</option>
                            <?php foreach ($categorias_lista as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['nombre']); ?>">
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filterSupplier" onchange="filterTable()" class="search-bar"
                            style="width: auto; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="">Todos los Proveedores</option>
                            <?php foreach ($proveedores_lista as $prov): ?>
                                <option value="<?php echo htmlspecialchars($prov['nombre_empresa']); ?>">
                                    <?php echo htmlspecialchars($prov['nombre_empresa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filterStock" onchange="filterTable()" class="search-bar"
                            style="width: auto; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="">Cualquier Stock</option>
                            <option value="bajo">Bajo Stock (< 5)</option>
                            <option value="ok">En Stock (>= 5)</option>
                            <option value="agotado">Agotado (0)</option>
                        </select>
                        <div class="search-bar" style="margin: 0; min-width: 250px;">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Buscar artículo..."
                                onkeyup="filterTable()">
                        </div>
                    </div>
                </div>

                <table class="admin-table" id="productsTable">
                    <thead>
                        <tr>
                            <th>ID Producto</th>
                            <th>Articulo</th>
                            <th>Categoría</th>
                            <th>Proveedores</th>
                            <th>Precio Venta</th>
                            <th>Stock Actual</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td>
                                    <?php echo str_pad($p['id'], 3, "0", STR_PAD_LEFT); ?>
                                    <?php if ($p['es_calculable_volumen']): ?>
                                        <i class="fa-solid fa-calculator" title="Compatible Calculadora"
                                            style="color:var(--primary-light); font-size: 0.8em; margin-left:3px;"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="searchable-name"><strong><?php echo htmlspecialchars($p['nombre']); ?></strong>
                                </td>
                                <td class="searchable-cat"><span class="status-badge"
                                        style="background:#f4f6f9; color:var(--text-muted);"><?php echo htmlspecialchars($p['categoria']); ?></span>
                                </td>
                                <td class="searchable-prov" style="font-size: 0.85em; color: #666;">
                                    <?php echo htmlspecialchars($p['proveedores']); ?>
                                </td>
                                <td><?php echo number_format($p['precio'], 2); ?> €</td>
                                <td class="searchable-stock">
                                    <?php if ($p['stock'] == 0): ?>
                                        <span style="color:var(--danger); font-weight:bold;"><i class="fa-solid fa-xmark"></i>
                                            0</span>
                                    <?php elseif ($p['stock'] < 5): ?>
                                        <span style="color:var(--warning); font-weight:bold;"><i
                                                class="fa-solid fa-warning"></i> <?php echo $p['stock']; ?></span>
                                    <?php else: ?>
                                        <span
                                            style="color:var(--primary-color); font-weight:bold;"><?php echo $p['stock']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="display: flex; gap: 5px;">
                                    <button class="btn-action edit"
                                        onclick="openEditModal(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nombre']); ?>', <?php echo $p['precio']; ?>, <?php echo $p['categoria_id']; ?>)"
                                        title="Editar Producto"
                                        style="background: #e3f2fd; color: #1565c0; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn-action add"
                                        onclick="openBuyModal(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nombre']); ?>')"
                                        title="Comprar al Proveedor (+ Stock)"
                                        style="background: #e8f5e9; color: #2e7d32; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                                        <i class="fa-solid fa-truck-ramp-box"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Simular Compra -->
    <div id="buyModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="margin-bottom: 1.5rem; color:var(--primary-dark);">Re-estocar Producto</h2>
            <p id="productNameDisplay" style="margin-bottom: 1.5rem; font-weight:500;"></p>
            <form method="POST" action="">
                <input type="hidden" name="action" value="compra_proveedor">
                <input type="hidden" name="producto_id" id="compra_producto_id" value="">

                <div class="admin-form-group" style="margin-bottom: 2rem;">
                    <label>Unidades Entrantes (Proveedor a Almacén)</label>
                    <input type="number" name="cantidad" id="cantidad_comprada" required min="1" max="1000"
                        placeholder="Ej: 50">
                    <small style="color:var(--text-muted); margin-top:5px;"><i class="fa-solid fa-info-circle"></i> Esto
                        figurará como un GASTO basado en el costo asociado al proveedor en la Base de Datos.</small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;"><i
                        class="fa-solid fa-file-invoice-dollar"></i> Confirmar Compra y Gasto</button>
            </form>
        </div>
    </div>

    <!-- Modal Editar Producto -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2 style="margin-bottom: 1.5rem; color:var(--primary-dark);">Editar Producto</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="editar_producto">
                <input type="hidden" name="edit_id" id="edit_id" value="">

                <div class="admin-form-group" style="margin-bottom: 1rem;">
                    <label>Nombre del Artículo</label>
                    <input type="text" name="edit_nombre" id="edit_nombre" required
                        style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>

                <div class="admin-form-group" style="margin-bottom: 1rem;">
                    <label>Categoría</label>
                    <select name="edit_categoria" id="edit_categoria" required
                        style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <?php foreach ($categorias_lista as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form-group" style="margin-bottom: 2rem;">
                    <label>Precio de Venta al Público (€)</label>
                    <input type="number" name="edit_precio" id="edit_precio" step="0.01" required
                        placeholder="Ej: 25.50"
                        style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fa-solid fa-save"></i>
                    Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        function filterTable() {
            let inputSearch = document.getElementById("searchInput").value.toLowerCase();
            let filterCat = document.getElementById("filterCategory").value.toLowerCase();
            let filterSupp = document.getElementById("filterSupplier").value.toLowerCase();
            let filterStock = document.getElementById("filterStock").value;

            let table = document.getElementById("productsTable");
            let trs = table.getElementsByTagName("tr");

            for (let i = 1; i < trs.length; i++) {
                let tdName = trs[i].querySelector(".searchable-name");
                let tdCat = trs[i].querySelector(".searchable-cat");
                let tdProv = trs[i].querySelector(".searchable-prov");
                let tdStock = trs[i].querySelector(".searchable-stock");

                if (tdName && tdCat && tdProv && tdStock) {
                    let txtName = tdName.textContent || tdName.innerText;
                    let txtCat = tdCat.textContent || tdCat.innerText;
                    let txtProv = tdProv.textContent || tdProv.innerText;
                    let numStock = parseInt(tdStock.textContent || tdStock.innerText);

                    let matchSearch = txtName.toLowerCase().includes(inputSearch);
                    let matchCat = filterCat === "" || txtCat.toLowerCase().includes(filterCat);
                    let matchSupp = filterSupp === "" || txtProv.toLowerCase().includes(filterSupp);

                    let matchStock = true;
                    if (filterStock === "bajo") matchStock = numStock < 5 && numStock > 0;
                    else if (filterStock === "ok") matchStock = numStock >= 5;
                    else if (filterStock === "agotado") matchStock = numStock === 0;

                    if (matchSearch && matchCat && matchSupp && matchStock) {
                        trs[i].style.display = "";
                    } else {
                        trs[i].style.display = "none";
                    }
                }
            }
        }

        const buyModal = document.getElementById('buyModal');
        const editModal = document.getElementById('editModal');

        function openBuyModal(id, nombre) {
            document.getElementById('compra_producto_id').value = id;
            document.getElementById('productNameDisplay').innerText = nombre;
            document.getElementById('cantidad_comprada').value = '';
            buyModal.style.display = 'flex';
        }

        function closeModal() {
            buyModal.style.display = 'none';
        }

        function openEditModal(id, nombre, precio, cat_id) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_precio').value = precio;
            document.getElementById('edit_categoria').value = cat_id;
            editModal.style.display = 'flex';
        }

        function closeEditModal() {
            editModal.style.display = 'none';
        }

        // Cerrar modal al clikar fuera
        window.onclick = function (event) {
            if (event.target == buyModal) closeModal();
            if (event.target == editModal) closeEditModal();
        }
    </script>

    <?php include '../includes/admin_excel_modal.php'; ?>
</body>

</html>