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

// Lógica para Guardar o Editar Usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'save') {
        $nombre = mb_convert_case($_POST['nombre'], MB_CASE_TITLE, "UTF-8");
        $email = strtolower($_POST['email']);
        $rol_id = $_POST['rol_id'];
        $id = $_POST['usuario_id'];

        if (empty($id)) {
            // Nuevo usuario (contraseña dummy fuerte para cumplir requisitos)
            $password = password_hash('Temp@1234', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (rol_id, nombre, email, contrasena, esta_verificado) VALUES (?, ?, ?, ?, 1)");
            if ($stmt->execute([$rol_id, $nombre, $email, $password])) {
                $mensaje = "<div class='alert alert-success'>Usuario creado correctamente. La contraseña temporal es 'Temp@1234'.</div>";
            }
        } else {
            // Editar usuario existente
            $stmt = $pdo->prepare("UPDATE usuarios SET rol_id = ?, nombre = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$rol_id, $nombre, $email, $id])) {
                $mensaje = "<div class='alert alert-success'>Usuario actualizado.</div>";
            }
        }
    } elseif ($_POST['action'] == 'delete') {
        $id = $_POST['eliminar_id'];
        if ($id != $_SESSION['user_id']) { // Evitar borrarse a sí mismo
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            if ($stmt->execute([$id])) {
                $mensaje = "<div class='alert alert-success'>Usuario eliminado.</div>";
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>No puedes eliminar tu propio usuario activo.</div>";
        }
    }
}

// Obtener la lista de usuarios
$stmt = $pdo->query("SELECT u.*, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.creado_en DESC");
$usuarios = $stmt->fetchAll();

// Roles para el combo box
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();

$page_title = "Gestión de Usuarios";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Usuarios - Ecobric Admin</title>
    <!-- Mismas cabeceras -->
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
                <li><a href="admin_productos.php"><i class="fa-solid fa-boxes-stacked"></i> Productos & Stock</a></li>
                <li><a href="admin_usuarios.php" class="active"><i class="fa-solid fa-users"></i> Usuarios</a></li>
                <li><a href="#" id="btn-export-excel-modal"><i class="fa-solid fa-file-excel"></i> Reporte Excel</a>
                </li>
                <li><a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Volver a Tienda</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Gestión de Usuarios</h1>
                <button class="btn btn-primary" onclick="openModal()"><i class="fa-solid fa-plus"></i> Nuevo
                    Usuario</button>
            </div>

            <?php echo $mensaje; ?>

            <div class="admin-panel-section">
                <div class="admin-panel-header">
                    <h2>Directorio</h2>
                    <div class="search-bar">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar por nombre o email..."
                            onkeyup="filterTable()">
                    </div>
                </div>

                <table class="admin-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado (Verificado)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <?php echo $u['id']; ?>
                                </td>
                                <td class="searchable">
                                    <?php echo htmlspecialchars($u['nombre']); ?>
                                </td>
                                <td class="searchable">
                                    <?php echo htmlspecialchars($u['email']); ?>
                                </td>
                                <td><span
                                        class="status-badge <?php echo $u['rol_id'] == 1 ? 'status-warning' : 'status-active'; ?>">
                                        <?php echo strtoupper($u['rol_nombre']); ?>
                                    </span></td>
                                <td>
                                    <?php echo $u['esta_verificado'] ? '<i class="fa-solid fa-check" style="color:var(--primary-color);"></i> Sí' : '<i class="fa-solid fa-xmark" style="color:var(--danger);"></i> No'; ?>
                                </td>
                                <td>
                                    <button class="btn-action edit"
                                        onclick="editUser(<?php echo htmlspecialchars(json_encode($u)); ?>)"
                                        title="Editar"><i class="fa-solid fa-pencil"></i></button>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn-action delete" onclick="confirmDelete(<?php echo $u['id']; ?>)"
                                            title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Formulario Usuario -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle" style="margin-bottom: 1.5rem;">Nuevo Usuario</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="usuario_id" id="usuario_id" value="">

                <div class="admin-form-group" style="margin-bottom: 1rem;">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" id="nombre" required>
                </div>

                <div class="admin-form-group" style="margin-bottom: 1rem;">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="admin-form-group" style="margin-bottom: 2rem;">
                    <label>Rol del Sistema</label>
                    <select name="rol_id" id="rol_id" required>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['id']; ?>">
                                <?php echo ucfirst($rol['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar Usuario</button>
            </form>
        </div>
    </div>

    <!-- Formulario invisible para borrar -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="eliminar_id" id="eliminar_id" value="">
    </form>

    <script>
        function filterTable() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();
            let table = document.getElementById("usersTable");
            let trs = table.getElementsByTagName("tr");

            for (let i = 1; i < trs.length; i++) {
                let cellName = trs[i].getElementsByTagName("td")[1];
                let cellEmail = trs[i].getElementsByTagName("td")[2];
                if (cellName || cellEmail) {
                    let txtName = cellName.textContent || cellName.innerText;
                    let txtEmail = cellEmail.textContent || cellEmail.innerText;
                    if (txtName.toLowerCase().indexOf(filter) > -1 || txtEmail.toLowerCase().indexOf(filter) > -1) {
                        trs[i].style.display = "";
                    } else {
                        trs[i].style.display = "none";
                    }
                }
            }
        }

        const modal = document.getElementById('userModal');

        function openModal() {
            document.getElementById('modalTitle').innerText = 'Nuevo Usuario';
            document.getElementById('usuario_id').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('email').value = '';
            document.getElementById('rol_id').value = '2'; // default cliente
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function editUser(user) {
            document.getElementById('modalTitle').innerText = 'Editar Usuario';
            document.getElementById('usuario_id').value = user.id;
            document.getElementById('nombre').value = user.nombre;
            document.getElementById('email').value = user.email;
            document.getElementById('rol_id').value = user.rol_id;
            modal.style.display = 'flex';
        }

        function confirmDelete(id) {
            if (confirm("Alerta: ¿Realmente deseas eliminar a este usuario de la base de datos? Esto anulará sus pedidos pendientes.")) {
                document.getElementById('eliminar_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Cerrar modal al clikar fuera
        window.onclick = function (event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

    <?php include '../includes/admin_excel_modal.php'; ?>
</body>

</html>