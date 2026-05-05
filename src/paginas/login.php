<?php
require_once '../config/db.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // Lógica básica de Auth
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['contrasena'])) {
        if ($user['esta_verificado']) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_role'] = $user['rol_id'];
            header("Location: ../index.php");
            exit;
        } else {
            $error = "Debes verificar tu correo electrónico antes de iniciar sesión.";
        }
    } else {
        $error = "Credenciales incorrectas.";
    }
}

include '../includes/header.php';
?>

<div
    style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background-color: var(--bg-light); padding: 2rem 0;">
    <div
        style="background: white; padding: 3rem; border-radius: var(--border-radius); box-shadow: var(--shadow-md); width: 100%; max-width: 450px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <i class="fa-solid fa-leaf" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
            <h2 style="color: var(--primary-dark);">Bienvenido a Ecobric</h2>
            <p style="color: var(--text-muted);">Inicia sesión para gestionar tus pedidos.</p>
        </div>

        <?php if ($error): ?>
            <div
                style="padding: 1rem; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div>
                <label for="email" style="font-weight: 500; display: block; margin-bottom: 0.5rem;">Correo
                    Electrónico</label>
                <input type="email" id="email" name="email" required
                    style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none;">
            </div>

            <div>
                <label for="password"
                    style="font-weight: 500; display: block; margin-bottom: 0.5rem;">Contraseña</label>
                <input type="password" id="password" name="password" required
                    style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; outline: none;">
            </div>

            <button type="submit" class="btn btn-primary"
                style="width: 100%; font-size: 1.1rem; margin-top: 0.5rem;">Entrar</button>
        </form>

        <div style="text-align: center; margin-top: 2rem; color: var(--text-muted);">
            ¿No tienes cuenta? <a href="registro.php" style="font-weight: bold; color: var(--primary-color);">Regístrate
                aquí</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>