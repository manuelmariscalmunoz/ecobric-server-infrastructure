<?php
require_once 'config/db.php';

// Obtener algunos productos destacados
$stmt = $pdo->query("SELECT p.*, c.nombre as categoria_nombre FROM productos p JOIN categorias c ON p.categoria_id = c.id LIMIT 4");
$productosDestacados = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero"
    style="background-image: linear-gradient(rgba(46,125,50,0.8), rgba(0,80,5,0.8)), url('https://images.unsplash.com/photo-1589939705384-5185137a7f0f?q=80&w=2070&auto=format&fit=crop');">
    <div class="container">
        <h1>Construcción Sostenible a tu Alcance</h1>
        <p>Herramientas ecológicas, madera certificada y pinturas naturales para un futuro más verde. Reduce el impacto
            ambiental de tus obras con Ecobric.</p>
        <div class="hero-actions">
            <a href="paginas/catalogo.php" class="btn btn-primary">Ver Catálogo</a>
            <a href="paginas/calculadora.php" class="btn btn-outline"
                style="color:white; border-color:white;">Calculadora de
                Materiales</a>
        </div>
    </div>
</section>

<!-- Beneficios -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-header">
            <h2>Por qué elegir Ecobric</h2>
        </div>
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; text-align: center;">
            <div
                style="padding: 2rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                <i class="fa-solid fa-leaf"
                    style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.2rem;">100% Ecológico</h3>
                <p>Materiales con baja huella de carbono y procesos de fabricación sostenibles.</p>
            </div>
            <div class="feature-box"
                style="text-align: center; padding: 2rem; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); transition: var(--transition);">
                <i class="fa-solid fa-layer-group"
                    style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1.5rem;"></i>
                <h3>Envíos a toda la región</h3>
                <p style="margin-bottom: 1rem;">Envíos rápidos y seguros a toda la región de Castilla-La Mancha.</p>
            </div>
            <div
                style="padding: 2rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                <i class="fa-solid fa-calculator"
                    style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.2rem;">Suministro Exacto</h3>
                <p>Usa nuestra calculadora para pedir exactamente lo que necesitas, sin desperdicios.</p>
                <a href="paginas/calculadora.php" class="btn btn-outline">Probar Ahora</a>
            </div>
        </div>
    </div>
</section>

<!-- Productos Destacados -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <h2>Productos Destacados</h2>
            <p
                style="text-align: center; color: var(--text-muted); max-width: 600px; margin: 0 auto; margin-top: 1rem;">
                Descubre nuestros materiales más vendidos, elegidos por constructores comprometidos con el medio
                ambiente.</p>
        </div>

        <div class="product-grid">
            <?php foreach ($productosDestacados as $producto): ?>
                <div class="product-card">
                    <?php if ($producto['es_calculable_volumen']): ?>
                        <span class="product-badge"><i class="fa-solid fa-calculator"></i> Calculable</span>
                    <?php endif; ?>
                    <!-- Usamos una imagen genérica si no hay URL -->
                    <img src="<?php echo $producto['url_imagen'] ?? 'https://images.unsplash.com/photo-1621644781442-9fc7a08e16ea?w=500&auto=format&fit=crop'; ?>"
                        alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="product-image">
                    <div class="product-info">
                        <span class="product-category">
                            <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                        </span>
                        <h3 class="product-title">
                            <?php echo htmlspecialchars($producto['nombre']); ?>
                        </h3>
                        <div style="margin-bottom: 1rem; flex-grow: 1;">
                            <p style="color: var(--text-muted); font-size: 0.9rem;">
                                <?php echo mb_strimwidth(htmlspecialchars($producto['descripcion']), 0, 80, '...'); ?>
                            </p>
                        </div>
                        <div class="product-price">
                            <?php echo number_format($producto['precio'], 2, ',', '.'); ?> €
                        </div>
                        <div style="display:flex; gap:0.5rem; mt-auto">
                            <a href="paginas/producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-outline"
                                style="flex:1; padding: 0.5rem;">Ver Detalle</a>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="paginas/catalogo.php" class="btn btn-accent">Ver Catálogo Completo <i
                    class="fa-solid fa-arrow-right" style="margin-left: 5px;"></i></a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>