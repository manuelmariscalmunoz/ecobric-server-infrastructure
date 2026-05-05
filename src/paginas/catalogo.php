<?php
require_once '../config/db.php';

// Obtener categorías para el filtro
$stmtCategorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC");
$categorias = $stmtCategorias->fetchAll();

// Variables para filtros
$whereClause = [];
$params = [];
$categoriaSeleccionada = isset($_GET['categoria']) ? (int) $_GET['categoria'] : 0;
$busqueda = isset($_GET['search']) ? trim($_GET['search']) : '';
$minPrecio = isset($_GET['min_precio']) && $_GET['min_precio'] !== '' ? (float) $_GET['min_precio'] : '';
$maxPrecio = isset($_GET['max_precio']) && $_GET['max_precio'] !== '' ? (float) $_GET['max_precio'] : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'nombre_asc';

if ($categoriaSeleccionada > 0) {
    $whereClause[] = "p.categoria_id = ?";
    $params[] = $categoriaSeleccionada;
}

if ($busqueda !== '') {
    $whereClause[] = "(p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $params[] = '%' . $busqueda . '%';
    $params[] = '%' . $busqueda . '%';
}

if ($minPrecio !== '') {
    $whereClause[] = "p.precio >= ?";
    $params[] = $minPrecio;
}

if ($maxPrecio !== '') {
    $whereClause[] = "p.precio <= ?";
    $params[] = $maxPrecio;
}

$whereString = "";
if (!empty($whereClause)) {
    $whereString = "WHERE " . implode(" AND ", $whereClause);
}

// Determinar el orden
$orderBy = "ORDER BY p.nombre ASC";
if ($orden === 'nombre_desc')
    $orderBy = "ORDER BY p.nombre DESC";
elseif ($orden === 'precio_asc')
    $orderBy = "ORDER BY p.precio ASC";
elseif ($orden === 'precio_desc')
    $orderBy = "ORDER BY p.precio DESC";

$stmtProductos = $pdo->prepare("SELECT p.*, c.nombre as categoria_nombre FROM productos p JOIN categorias c ON p.categoria_id = c.id $whereString $orderBy");
$stmtProductos->execute($params);
$productos = $stmtProductos->fetchAll();

include '../includes/header.php';
?>

<div class="page-header"
    style="background-color: var(--primary-dark); padding: 4rem 0; color: white; text-align: center;">
    <div class="container">
        <h1 style="color: white; margin-bottom: 0.5rem;">Catálogo de Productos</h1>
        <p>Encuentra los mejores materiales ecológicos para tu proyecto.</p>
    </div>
</div>

<section class="section-padding bg-light">
    <div class="container" style="display: flex; gap: 3rem;">

        <!-- Sidebar Filtros -->
        <aside style="width: 250px; flex-shrink: 0;">
            <div
                style="background: white; padding: 1.5rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm); position: sticky; top: 100px;">
                <h3
                    style="font-size: 1.2rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                    <i class="fa-solid fa-filter"></i> Filtros
                </h3>

                <form action="catalogo.php" method="GET">

                    <!-- Búsqueda -->
                    <div style="margin-bottom: 1rem;">
                        <label for="search"
                            style="display: block; font-weight: bold; margin-bottom: 0.5rem; font-size: 0.9rem;">Buscar</label>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($busqueda); ?>"
                            placeholder="Ej. Taladro, Pintura..."
                            style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.9rem;">
                    </div>

                    <!-- Categorías -->
                    <div style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-weight: bold; margin-bottom: 0.5rem; font-size: 0.9rem;">Categoría</label>
                        <select name="categoria"
                            style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.9rem;">
                            <option value="0">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $categoriaSeleccionada == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Precio -->
                    <div style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-weight: bold; margin-bottom: 0.5rem; font-size: 0.9rem;">Precio
                            (€)</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="number" name="min_precio" value="<?php echo htmlspecialchars($minPrecio); ?>"
                                placeholder="Min" step="0.01" min="0"
                                style="width: 50%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.9rem;">
                            <input type="number" name="max_precio" value="<?php echo htmlspecialchars($maxPrecio); ?>"
                                placeholder="Max" step="0.01" min="0"
                                style="width: 50%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.9rem;">
                        </div>
                    </div>

                    <!-- Ordenar por -->
                    <div style="margin-bottom: 1.5rem;">
                        <label
                            style="display: block; font-weight: bold; margin-bottom: 0.5rem; font-size: 0.9rem;">Ordenar
                            por</label>
                        <select name="orden"
                            style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.9rem;">
                            <option value="nombre_asc" <?php echo $orden == 'nombre_asc' ? 'selected' : ''; ?>>Nombre
                                (A-Z)</option>
                            <option value="nombre_desc" <?php echo $orden == 'nombre_desc' ? 'selected' : ''; ?>>Nombre
                                (Z-A)</option>
                            <option value="precio_asc" <?php echo $orden == 'precio_asc' ? 'selected' : ''; ?>>Precio
                                (Menor a Mayor)</option>
                            <option value="precio_desc" <?php echo $orden == 'precio_desc' ? 'selected' : ''; ?>>Precio
                                (Mayor a Menor)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.5rem;">
                        <i class="fa-solid fa-search"></i> Aplicar Filtros
                    </button>
                    <?php if ($categoriaSeleccionada > 0 || $busqueda !== '' || $minPrecio !== '' || $maxPrecio !== '' || $orden !== 'nombre_asc'): ?>
                        <a href="catalogo.php" class="btn btn-outline"
                            style="width: 100%; text-align: center; display: block; margin-top: 0.5rem; padding: 0.5rem; color: var(--text-muted);">
                            Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </aside>

        <!-- Main Content (Lista de productos) -->
        <div style="flex-grow: 1;">
            <?php if (count($productos) > 0): ?>
                <div class="product-grid">
                    <?php foreach ($productos as $producto): ?>
                        <div class="product-card">
                            <?php if ($producto['es_calculable_volumen']): ?>
                                <span class="product-badge"><i class="fa-solid fa-calculator"></i> Calculable</span>
                            <?php endif; ?>
                            <img src="<?php echo $producto['url_imagen'] ?? 'https://images.unsplash.com/photo-1621644781442-9fc7a08e16ea?w=500&auto=format&fit=crop'; ?>"
                                alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="product-image">
                            <div class="product-info">
                                <span class="product-category">
                                    <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                                </span>
                                <h3 class="product-title">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </h3>
                                <div class="product-price">
                                    <?php echo number_format($producto['precio'], 2, ',', '.'); ?> €
                                </div>
                                <div style="display:flex; gap:0.5rem; margin-top: 1rem;">
                                    <a href="producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-outline"
                                        style="flex:1; padding: 0.5rem; text-align: center;">Ver Detalle</a>
                                    <button class="btn btn-primary add-to-cart-btn" data-id="<?php echo $producto['id']; ?>"
                                        style="padding: 0.5rem;" title="Añadir al carrito">
                                        <i class="fa-solid fa-cart-plus"></i> <span style="font-size: 0.9rem;">Añadir</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div
                    style="text-align: center; padding: 4rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                    <i class="fa-solid fa-box-open" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3 style="color: var(--text-muted);">No se encontraron productos en esta categoría.</h3>
                    <a href="catalogo.php" class="btn btn-primary" style="margin-top: 1rem;">Ver todos los productos</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>