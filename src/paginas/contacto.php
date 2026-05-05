<?php
require_once '../includes/mailer.php';
$mensaje_estado = '';
$mensaje_clase = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = htmlspecialchars($_POST['nombre'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $asunto = htmlspecialchars($_POST['asunto'] ?? '');
    $mensaje = htmlspecialchars($_POST['mensaje'] ?? '');

    if (!empty($nombre) && !empty($email) && !empty($mensaje)) {

        $cuerpo = "<h3>Nuevo Mensaje de Contacto</h3>
                   <p><strong>Nombre:</strong> $nombre</p>
                   <p><strong>Email:</strong> $email</p>
                   <p><strong>Asunto:</strong> $asunto</p>
                   <p><strong>Mensaje:</strong><br>$mensaje</p>";

        // Enviar al soporte
        $enviadoSoporte = enviarCorreo('ecobricsoporte@gmail.com', "Contacto: $asunto", $cuerpo);

        // Opcional: Enviar auto-respuesta al cliente
        $cuerpoCliente = "<h3>Hola $nombre,</h3><p>Hemos recibido tu consulta sobre <strong>$asunto</strong>. Nuestro equipo te responderá lo antes posible.</p><p>Gracias por contactar con Ecobric.</p>";
        enviarCorreo($email, "Hemos recibido tu consulta - Ecobric", $cuerpoCliente);

        if ($enviadoSoporte) {
            $mensaje_estado = "Gracias por tu mensaje, $nombre. Hemos recibido tu consulta y te responderemos a $email lo antes posible.";
            $mensaje_clase = "alert-success";
        } else {
            $mensaje_estado = "Hubo un error al enviar el mensaje. Por favor, inténtalo de nuevo más tarde.";
            $mensaje_clase = "alert-danger";
        }
    } else {
        $mensaje_estado = "Por favor, completa todos los campos requeridos.";
        $mensaje_clase = "alert-danger";
    }
}

include '../includes/header.php';
?>

<div class="page-header"
    style="background-color: var(--accent-color); padding: 4rem 0; color: white; text-align: center;">
    <div class="container">
        <h1 style="color: white; margin-bottom: 0.5rem;">Contacta con Ecobric</h1>
        <p>¿Tienes dudas sobre bioconstrucción? Escríbenos.</p>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 4rem;">

            <!-- Info Contacto -->
            <div>
                <h3 style="font-size: 1.8rem; margin-bottom: 1.5rem;">Información de Contacto</h3>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Nuestro equipo de expertos en materiales
                    ecológicos está listo para asesorarte. Respondemos en menos de 24 horas laborables.</p>

                <ul style="list-style: none;">
                    <li style="margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 1rem;">
                        <i class="fa-solid fa-map-marker-alt"
                            style="color: var(--primary-color); font-size: 1.5rem; margin-top: 5px;"></i>
                        <div>
                            <h4 style="margin-bottom: 0.2rem;">Dirección</h4>
                            <p style="color: var(--text-muted);">Tres Olivos - La Piedad,<br>Toledo, 45600, Talavera de
                                la Reina,<br>
                                España</p>
                        </div>
                    </li>
                    <li style="margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 1rem;">
                        <i class="fa-solid fa-phone"
                            style="color: var(--primary-color); font-size: 1.5rem; margin-top: 5px;"></i>
                        <div>
                            <h4 style="margin-bottom: 0.2rem;">Teléfonos</h4>
                            <p style="color: var(--text-muted);">Comercial: +34 912 345 678<br>Técnico: +34 912 345 679
                            </p>
                        </div>
                    </li>
                    <li style="margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 1rem;">
                        <i class="fa-solid fa-envelope"
                            style="color: var(--primary-color); font-size: 1.5rem; margin-top: 5px;"></i>
                        <div>
                            <h4 style="margin-bottom: 0.2rem;">Correo Electrónico</h4>
                            <p style="color: var(--text-muted);">ecobricsoporte@gmail.com</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Formulario Contacto -->
            <div
                style="background: white; padding: 2.5rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Envíanos un Mensaje</h3>

                <?php if ($mensaje_estado): ?>
                    <div
                        style="padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; <?php echo $mensaje_clase == 'alert-success' ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                        <?php echo $mensaje_estado; ?>
                    </div>
                <?php endif; ?>

                <form action="contacto.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <label for="nombre" style="font-weight: 500;">Nombre Completo <span
                                    style="color: var(--danger);">*</span></label>
                            <input type="text" id="nombre" name="nombre" required
                                style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none; transition: var(--transition);"
                                onfocus="this.style.borderColor='var(--primary-color)'"
                                onblur="this.style.borderColor='var(--border-color)'">
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <label for="email" style="font-weight: 500;">Correo Electrónico <span
                                    style="color: var(--danger);">*</span></label>
                            <input type="email" id="email" name="email" required
                                style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none; transition: var(--transition);"
                                onfocus="this.style.borderColor='var(--primary-color)'"
                                onblur="this.style.borderColor='var(--border-color)'">
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="asunto" style="font-weight: 500;">Asunto</label>
                        <select id="asunto" name="asunto"
                            style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none; background-color: white;">
                            <option value="Duda General">Duda General</option>
                            <option value="Presupuesto">Solicitud de Presupuesto</option>
                            <option value="Soporte Tecnico">Soporte Técnico de Materiales</option>
                            <option value="Distribucion">Quiero ser Distribuidor</option>
                        </select>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="mensaje" style="font-weight: 500;">Mensaje <span
                                style="color: var(--danger);">*</span></label>
                        <textarea id="mensaje" name="mensaje" rows="5" required
                            style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none; transition: var(--transition); resize: vertical;"
                            onfocus="this.style.borderColor='var(--primary-color)'"
                            onblur="this.style.borderColor='var(--border-color)'"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"
                        style="align-self: flex-start; padding: 1rem 2rem; font-size: 1.1rem;">
                        <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Enviar Mensaje
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>