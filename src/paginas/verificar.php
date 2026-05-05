<?php
require_once '../config/db.php';
session_start();

$email = $_SESSION['email_verificacion'] ?? '';

if (!$email) {
    header("Location: login.php");
    exit;
}

$error = '';
$exito = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigoIngresado = trim($_POST['codigo']);

    // Verificar código
    $stmt = $pdo->prepare("SELECT id, token_verificacion FROM usuarios WHERE email = ? AND esta_verificado = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['token_verificacion'] === $codigoIngresado) {
        $update = $pdo->prepare("UPDATE usuarios SET esta_verificado = 1, token_verificacion = NULL WHERE id = ?");
        if ($update->execute([$user['id']])) {
            unset($_SESSION['email_verificacion']);
            $exito = "¡Cuenta verificada correctamente! Serás redirigido al login en breve.";
            header("refresh:3;url=login.php");
        } else {
            $error = "Ocurrió un error al actualizar la cuenta.";
        }
    } else {
        $error = "Código incorrecto. Por favor, revisa tu correo.";
    }
}

include '../includes/header.php';
?>

<div
    style="min-height: 70vh; display: flex; align-items: center; justify-content: center; background-color: var(--bg-light); padding: 3rem 0;">
    <div
        style="background: white; padding: 3rem; border-radius: var(--border-radius); box-shadow: var(--shadow-md); width: 100%; max-width: 450px; text-align: center;">

        <i class="fa-solid fa-envelope-open-text"
            style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
        <h2 style="color: var(--primary-dark); margin-bottom: 1rem;">Verifica tu Correo</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Hemos enviado un código de 6 dígitos a <strong>
                <?php echo htmlspecialchars($email); ?>
            </strong></p>

        <?php if ($error): ?>
            <div
                style="padding: 1rem; background-color: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 1.5rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($exito): ?>
            <div
                style="padding: 1rem; background-color: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo htmlspecialchars($exito); ?>
            </div>
        <?php else: ?>
            <form action="verificar.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div>
                    <input type="text" name="codigo" required placeholder="Ej: 123456" maxlength="6"
                        style="width: 100%; padding: 1rem; font-size: 1.5rem; letter-spacing: 5px; text-align: center; border: 2px dashed var(--border-color); border-radius: 4px; outline: none; transition: var(--transition);"
                        onfocus="this.style.borderColor='var(--primary-color)'"
                        onblur="this.style.borderColor='var(--border-color)'">
                </div>
                <button type="submit" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem;">Verificar
                    Cuenta</button>
            </form>
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>