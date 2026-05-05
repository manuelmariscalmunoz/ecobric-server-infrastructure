<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Calcular número de items en carrito
$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

$base_path = strpos($_SERVER['SCRIPT_NAME'], '/paginas/') !== false ? '../' : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecobric - Materiales Ecológicos</title>
    <!-- Fuentes de Google: Una rústica para títulos, otra moderna limpia para textos -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <!-- Iconos de FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/styles.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="main-header">
        <div class="container header-container">
            <a href="<?php echo $base_path; ?>index.php" class="logo"
                style="display: flex; align-items: center; gap: 10px;">
                <img src="<?php echo $base_path; ?>img/logo.png" alt="Ecobric Logo"
                    style="height: 40px; width: auto; object-fit: contain;">
                <span>Ecobric</span>
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo $base_path; ?>index.php">Inicio</a></li>
                    <li><a href="<?php echo $base_path; ?>paginas/catalogo.php">Catálogo</a></li>
                    <li><a href="<?php echo $base_path; ?>paginas/calculadora.php">Calculadora
                    <li><a href="<?php echo $base_path; ?>paginas/nosotros.php">Nosotros</a></li>
                    <li><a href="<?php echo $base_path; ?>paginas/contacto.php">Contacto</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <!-- Google Translate Widget -->
                <div id="google_translate_element"
                    style="display:inline-block; margin-right:15px; vertical-align: middle;"></div>
                <script type="text/javascript">
                    function googleTranslateElementInit() {
                        new google.translate.TranslateElement({
                            pageLanguage: 'es',
                            includedLanguages: 'es,en',
                            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                            autoDisplay: false
                        }, 'google_translate_element');
                    }
                </script>
                <script type="text/javascript"
                    src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
                        <a href="<?php echo $base_path; ?>paginas/admin_dashboard.php" class="btn btn-outline"
                            style="padding: 0.4rem 0.8rem; font-size: 0.9rem; margin-right: 10px;">
                            <i class="fa-solid fa-gauge-high"></i> Panel ERP
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $base_path; ?>paginas/perfil.php" class="btn-icon" title="Mi Perfil"><i
                            class="fa-solid fa-user"></i></a>
                    <a href="<?php echo $base_path; ?>paginas/logout_process.php"
                        onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?');" class="btn-icon"
                        title="Cerrar Sesión"><i class="fa-solid fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="<?php echo $base_path; ?>paginas/login.php" class="btn-icon" title="Iniciar Sesión"><i
                            class="fa-solid fa-user"></i></a>
                <?php endif; ?>
                <a href="<?php echo $base_path; ?>paginas/carrito.php" class="btn-icon cart-icon"
                    style="margin-right: 15px;">
                    <i class="fa-solid fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Botón menú móvil (dentro de header-actions) -->
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Abrir menú"><i
                        class="fa-solid fa-bars"></i></button>
            </div>
        </div>
    </header>
    <main class="main-content">

        <!-- Lógica del Menú Móvil Global -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btn = document.getElementById('mobileMenuBtn');
                const nav = document.querySelector('.main-nav');

                if (btn && nav) {
                    btn.addEventListener('click', () => {
                        nav.classList.toggle('active');
                        // Cambiar icono entre Hamburguesa y Equis
                        const icon = btn.querySelector('i');
                        if (nav.classList.contains('active')) {
                            icon.classList.remove('fa-bars');
                            icon.classList.add('fa-times');
                        } else {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    });
                }
            });
        </script>