<?php
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: catalogo.php");
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.nombre as categoria_nombre FROM productos p JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    echo "<h1>Producto no encontrado</h1><a href='catalogo.php'>Volver al catálogo</a>";
    exit;
}

include '../includes/header.php';
?>

<div class="page-header"
    style="background-color: var(--bg-light); padding: 4rem 0; text-align: left; border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">
            <a href="../index.php" style="color: var(--primary-color);">Inicio</a> &gt;
            <a href="catalogo.php" style="color: var(--primary-color);">Catálogo</a> &gt;
            <span>
                <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
            </span>
        </div>
        <h1 style="color: var(--primary-dark); margin-bottom: 0.5rem;">
            <?php echo htmlspecialchars($producto['nombre']); ?>
        </h1>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;">
            <!-- Imagen -->
            <div>
                <img src="<?php echo $producto['url_imagen'] ?? 'https://images.unsplash.com/photo-1621644781442-9fc7a08e16ea'; ?>"
                    alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                    style="width: 100%; border-radius: var(--border-radius); box-shadow: var(--shadow-md); object-fit: cover;">
            </div>

            <!-- Detalles -->
            <div style="display: flex; flex-direction: column;">
                <span class="product-category" style="margin-bottom: 1rem; display: inline-block;">
                    <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                </span>

                <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 2rem; color: var(--text-dark);">
                    <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
                </p>

                <?php if ($producto['es_calculable_volumen']): ?>
                    <div
                        style="background-color: var(--primary-light); padding: 1rem; border-radius: 4px; color: white; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; width: fit-content;">
                        <i class="fa-solid fa-calculator"></i> Este material se puede calcular por volumen en nuestra <a
                            href="calculadora.php"
                            style="color: white; font-weight: bold; text-decoration: underline;">Calculadora</a>.
                    </div>
                <?php endif; ?>

                <div style="font-size: 2.5rem; font-weight: bold; color: var(--accent-color); margin-bottom: 0.5rem;">
                    <?php echo number_format($producto['precio'], 2, ',', '.'); ?> € <span
                        style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">(IVA inc.)</span>
                </div>

                <?php if ($producto['stock'] == 0): ?>
                    <p style="color: var(--danger); font-weight: bold; margin-bottom: 2rem;">
                        <i class="fa-solid fa-box-open"></i> Sin Stock
                    </p>
                <?php elseif ($producto['stock'] < 5): ?>
                    <p style="color: #d97706; font-weight: bold; margin-bottom: 2rem;">
                        <i class="fa-solid fa-box"></i> ¡Poco stock: solo quedan <?php echo $producto['stock']; ?> unidades!
                    </p>
                <?php else: ?>
                    <p style="color: var(--primary-color); font-weight: bold; margin-bottom: 2rem;">
                        <i class="fa-solid fa-box"></i> En Stock
                    </p>
                <?php endif; ?>

                <div style="display: flex; gap: 1rem; align-items: stretch; margin-top: auto;">
                    <input type="number" id="cantidad" value="1" min="1"
                        max="<?php echo $producto['stock'] > 0 ? $producto['stock'] : 1; ?>"
                        style="width: 80px; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; text-align: center; font-size: 1.2rem;"
                        <?php echo $producto['stock'] == 0 ? 'disabled' : ''; ?>>
                    <button class="btn btn-primary add-to-cart-btn" data-id="<?php echo $producto['id']; ?>"
                        style="flex-grow: 1; font-size: 1.2rem;" <?php echo $producto['stock'] == 0 ? 'disabled style="background-color: #ccc; cursor: not-allowed;"' : ''; ?>>
                        <i class="fa-solid fa-cart-plus"></i> Añadir al Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>