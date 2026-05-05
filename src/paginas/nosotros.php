<?php
require_once '../config/db.php';
include '../includes/header.php';
?>

<div class="page-header"
    style="background: linear-gradient(rgba(46,125,50,0.9), rgba(0,80,5,0.9)), url('https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=1600&auto=format&fit=crop') center/cover; padding: 6rem 0; color: white; text-align: center;">
    <div class="container">
        <h1 style="color: white; font-size: 3rem; margin-bottom: 1rem;">Sobre Ecobric</h1>
        <p style="font-size: 1.2rem; max-width: 800px; margin: 0 auto;">Nuestra misión, visión y compromiso con el medio
            ambiente.</p>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
            <div>
                <img src="https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?w=800&auto=format&fit=crop"
                    alt="Equipo Ecobric"
                    style="width: 100%; border-radius: var(--border-radius); box-shadow: var(--shadow-lg);">
            </div>
            <div>
                <h2 style="color: var(--primary-dark); font-size: 2.2rem; margin-bottom: 1.5rem;">¿Quiénes Somos?</h2>
                <p style="margin-bottom: 1rem; font-size: 1.1rem;">Somos <strong>Ecobric</strong>, una iniciativa nacida
                    de la necesidad de reducir la inmensa huella de carbono que genera el sector de la construcción
                    tradicional.</p>
                <p style="margin-bottom: 1.5rem; font-size: 1.1rem;">Nuestro catálogo se especializa en materiales de
                    bioconstrucción: desde herramientas básicas, hasta nuestras pinturas naturales más contributivas al
                    medioambiente.</p>

                <h3 style="color: var(--accent-color); margin-top: 2rem; margin-bottom: 1rem;">Nuestra Misión</h3>
                <p style="margin-bottom: 1.5rem;">Proveer a arquitectos, constructores y aficionados al bricolaje de
                    alternativas viables, económicas y 100% respetuosas con el planeta.</p>

                <h3 style="color: var(--accent-color); margin-bottom: 1rem;">Nuestra Visión</h3>
                <p>Convertirnos en el principal proveedor de materiales ecológicos a nivel regional, demostrando que
                    construir de forma sostenible no solo es ético, sino también rentable y duradero.</p>
            </div>
        </div>
    </div>
</section>

<section class="bg-light section-padding">
    <div class="container">
        <div class="section-header">
            <h2>Nuestros Valores</h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; text-align: center;">
            <div
                style="padding: 2rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                <i class="fa-solid fa-earth-americas"
                    style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">Sostenibilidad Real</h3>
                <p style="color: var(--text-muted);">Auditamos a nuestros proveedores para garantizar que sus procesos
                    no dañan ecosistemas locales.</p>
            </div>
            <div
                style="padding: 2rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                <i class="fa-solid fa-handshake-angle"
                    style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">Comunidad</h3>
                <p style="color: var(--text-muted);">Apoyamos proyectos de autoconstrucción comunitaria y brindamos
                    asesoría técnica.</p>
            </div>
            <div
                style="padding: 2rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                <i class="fa-solid fa-shield-halved"
                    style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">Garantía de Calidad</h3>
                <p style="color: var(--text-muted);">Lo ecológico no está reñido con la durabilidad. Nuestros materiales
                    cumplen todas las normativas.</p>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>