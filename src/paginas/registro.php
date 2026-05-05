<?php
require_once '../config/db.php';
require_once '../includes/mailer.php';
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];

    if ($pass !== $pass_confirm) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = "error";
    } elseif (strlen($pass) < 8 || !preg_match('/[A-Z]/', $pass) || !preg_match('/[a-z]/', $pass) || !preg_match('/[0-9]/', $pass) || !preg_match('/[^a-zA-Z0-9]/', $pass)) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres, incluir una mayúscula, una minúscula, un número y un carácter especial.";
        $tipo_mensaje = "error";
    } else {
        // Verificar existencia de email
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            $mensaje = "El correo ya está registrado en Ecobric.";
            $tipo_mensaje = "error";
        } else {
            // Hash Password
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $codigo_verificacion = mt_rand(100000, 999999); // Código de 6 dígitos

            $stmt = $pdo->prepare("INSERT INTO usuarios (rol_id, nombre, email, contrasena, token_verificacion, esta_verificado) VALUES (?, ?, ?, ?, ?, ?)");
            // Rol_id 2 = Cliente. esta_verificado = 0 para requerir verificación por código
            if ($stmt->execute([2, $nombre, $email, $hash, $codigo_verificacion, 0])) {

                // Enviar correo de verificación
                $cuerpo = "
                <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-top: 5px solid #2e7d32;'>
                        <h2 style='color: #2e7d32; text-align: center;'>¡Bienvenido a Ecobric, $nombre!</h2>
                        <p style='font-size: 16px;'>Gracias por unirte a la Revolución Verde.</p>
                        <p style='font-size: 16px;'>Para activar tu cuenta, por favor introduce el siguiente código de verificación en la web:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <span style='font-size: 32px; font-weight: bold; background-color: #e8f5e9; color: #2e7d32; padding: 10px 20px; border-radius: 5px; letter-spacing: 5px;'>$codigo_verificacion</span>
                        </div>
                        <p style='font-size: 14px; color: #777;'>Si no has solicitado este registro, puedes ignorar este mensaje.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='font-size: 12px; text-align: center; color: #999;'>&copy; 2026 Ecobric. Todos los derechos reservados.</p>
                    </div>
                </div>";

                enviarCorreo($email, "Verifica tu cuenta en Ecobric", $cuerpo);

                session_start();
                $_SESSION['email_verificacion'] = $email;
                header("Location: verificar.php");
                exit;
            } else {
                $mensaje = "Ocurrió un error en el registro.";
                $tipo_mensaje = "error";
            }
        }
    }
}

include '../includes/header.php';
?>

<div
    style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background-color: var(--bg-light); padding: 3rem 0;">
    <div
        style="background: white; padding: 3rem; border-radius: var(--border-radius); box-shadow: var(--shadow-md); width: 100%; max-width: 500px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <i class="fa-solid fa-leaf" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
            <h2 style="color: var(--primary-dark);">Únete a la Revolución Verde</h2>
            <p style="color: var(--text-muted);">Crea tu cuenta en Ecobric.</p>
        </div>

        <?php if ($mensaje): ?>
            <div
                style="padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; text-align: center; <?php echo $tipo_mensaje == 'exito' ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <?php if ($tipo_mensaje !== 'exito'): ?>
            <form method="POST" action="registro.php" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div>
                    <label for="nombre" style="font-weight: 500; display: block; margin-bottom: 0.5rem;">Nombre
                        Completo</label>
                    <input type="text" id="nombre" name="nombre" required
                        style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none;">
                </div>

                <div>
                    <label for="email" style="font-weight: 500; display: block; margin-bottom: 0.5rem;">Correo
                        Electrónico</label>
                    <input type="email" id="email" name="email" required
                        style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none;">
                </div>

                <div>
                    <label for="password"
                        style="font-weight: 500; display: block; margin-bottom: 0.5rem;">Contraseña</label>
                    <input type="password" id="password" name="password" required minlength="8"
                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}"
                        title="Debe contener al menos 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial."
                        style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none;">
                </div>

                <div>
                    <label for="password_confirm" style="font-weight: 500; display: block; margin-bottom: 0.5rem;">Confirmar
                        Contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                        style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none;">
                </div>

                <button type="submit" class="btn btn-accent"
                    style="width: 100%; font-size: 1.1rem; margin-top: 0.5rem;">Registrarse</button>
            </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem; color: var(--text-muted);">
            ¿Ya tienes cuenta? <a href="login.php" style="font-weight: bold; color: var(--primary-color);">Inicia
                sesión</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>