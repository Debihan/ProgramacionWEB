<?php
session_start();

// Verificación más robusta de administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    // Guardar mensaje de error en la sesión
    $_SESSION['error'] = "Acceso denegado. Se requieren privilegios de administrador.";
    header("Location: dashboard.php");
    exit();
}

// Incluir el archivo de conexión
include('../habitos/conexion.php');

// Verificación adicional en la base de datos
$query = "SELECT rol_id FROM usuarios WHERE id = ? AND estatus_id = 1";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario || $usuario['rol_id'] != 1) {
    $_SESSION['error'] = "Acceso denegado. Se requieren privilegios de administrador.";
    header("Location: dashboard.php");
    exit();
}

// Procesar eliminación de usuario
if (isset($_POST['eliminar_usuario'])) {
    $usuario_id = $_POST['usuario_id'];
    $query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Usuario eliminado correctamente.";
    } else {
        $_SESSION['error'] = "Error al eliminar el usuario.";
    }
    header("Location: usuarios.php");
    exit();
}

// Procesar registro de nuevo usuario
if (isset($_POST['registrar_usuario'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol_id = $_POST['rol_id'];
    $estatus_id = 1; // Activo por defecto

    $query = "INSERT INTO usuarios (nombre, correo, password, rol_id, estatus_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("sssii", $nombre, $correo, $password, $rol_id, $estatus_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Usuario registrado correctamente.";
    } else {
        $_SESSION['error'] = "Error al registrar el usuario.";
    }
    header("Location: usuarios.php");
    exit();
}

// Procesar edición de usuario
if (isset($_POST['editar_usuario'])) {
    $usuario_id = $_POST['usuario_id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol_id = $_POST['rol_id'];
    $estatus_id = $_POST['estatus_id'];

    $query = "UPDATE usuarios SET nombre = ?, correo = ?, rol_id = ?, estatus_id = ? WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssiii", $nombre, $correo, $rol_id, $estatus_id, $usuario_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Usuario actualizado correctamente.";
    } else {
        $_SESSION['error'] = "Error al actualizar el usuario.";
    }
    header("Location: usuarios.php");
    exit();
}

// Procesar cambios de estado
if (isset($_POST['cambiar_estado'])) {
    $usuario_id = $_POST['usuario_id'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    $query = "UPDATE usuarios SET estatus_id = ? WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $nuevo_estado, $usuario_id);
    $stmt->execute();
}

// Procesar cambios de rol
if (isset($_POST['cambiar_rol'])) {
    $usuario_id = $_POST['usuario_id'];
    $nuevo_rol = $_POST['nuevo_rol'];
    
    $query = "UPDATE usuarios SET rol_id = ? WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $nuevo_rol, $usuario_id);
    $stmt->execute();
}

// Obtener lista de usuarios con búsqueda
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$where = "";
if (!empty($busqueda)) {
    $where = "WHERE u.nombre LIKE ? OR u.correo LIKE ?";
}

$query = "SELECT u.*, r.nombre as rol_nombre, e.descripcion as estado 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id 
          JOIN estatus e ON u.estatus_id = e.id 
          $where
          ORDER BY u.fecha_creacion DESC";

$stmt = $conexion->prepare($query);
if (!empty($busqueda)) {
    $busqueda = "%$busqueda%";
    $stmt->bind_param("ss", $busqueda, $busqueda);
}
$stmt->execute();
$result = $stmt->get_result();

// Obtener roles y estados para los selectores
$query_roles = "SELECT * FROM roles";
$roles = $conexion->query($query_roles);

$query_estados = "SELECT * FROM estatus";
$estados = $conexion->query($query_estados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Panel de Administración</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <i class="bi bi-person-circle me-2"></i>
                        <span class="fs-4">Admin Panel</span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link text-white">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="usuarios.php" class="nav-link active">
                                <i class="bi bi-people me-2"></i>
                                Usuarios
                            </a>
                        </li>
                       
                    </ul>
                    <hr>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-2"></i>
                            <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                            
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Usuarios</h1>
                </div>

                <!-- Search Bar -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <form action="" method="GET" class="d-flex">
                            <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar por nombre o correo..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarModal">
                            <i class="bi bi-person-plus"></i> Nuevo Usuario
                        </button>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($usuario = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                <select name="nuevo_rol" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <?php while ($rol = $roles->fetch_assoc()): ?>
                                                        <option value="<?php echo $rol['id']; ?>" <?php echo ($rol['id'] == $usuario['rol_id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($rol['nombre']); ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                                <input type="hidden" name="cambiar_rol" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                <select name="nuevo_estado" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <?php 
                                                    $estados->data_seek(0);
                                                    while ($estado = $estados->fetch_assoc()): 
                                                    ?>
                                                        <option value="<?php echo $estado['id']; ?>" <?php echo ($estado['id'] == $usuario['estatus_id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($estado['descripcion']); ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                                <input type="hidden" name="cambiar_estado" value="1">
                                            </form>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detallesModal<?php echo $usuario['id']; ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarModal<?php echo $usuario['id']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#eliminarModal<?php echo $usuario['id']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal de Detalles -->
                                    <div class="modal fade" id="detallesModal<?php echo $usuario['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detalles del Usuario</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>ID:</strong> <?php echo $usuario['id']; ?></p>
                                                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
                                                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['correo']); ?></p>
                                                    <p><strong>Rol:</strong> <?php echo htmlspecialchars($usuario['rol_nombre']); ?></p>
                                                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($usuario['estado']); ?></p>
                                                    <p><strong>Fecha de Registro:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal de Editar -->
                                    <div class="modal fade" id="editarModal<?php echo $usuario['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Editar Usuario</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nombre</label>
                                                            <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Correo</label>
                                                            <input type="email" class="form-control" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Rol</label>
                                                            <select name="rol_id" class="form-select" required>
                                                                <?php 
                                                                $roles->data_seek(0);
                                                                while ($rol = $roles->fetch_assoc()): 
                                                                ?>
                                                                    <option value="<?php echo $rol['id']; ?>" <?php echo ($rol['id'] == $usuario['rol_id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($rol['nombre']); ?>
                                                                    </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Estado</label>
                                                            <select name="estatus_id" class="form-select" required>
                                                                <?php 
                                                                $estados->data_seek(0);
                                                                while ($estado = $estados->fetch_assoc()): 
                                                                ?>
                                                                    <option value="<?php echo $estado['id']; ?>" <?php echo ($estado['id'] == $usuario['estatus_id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($estado['descripcion']); ?>
                                                                    </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" name="editar_usuario" class="btn btn-primary">Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal de Eliminar -->
                                    <div class="modal fade" id="eliminarModal<?php echo $usuario['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmar Eliminación</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>¿Está seguro que desea eliminar al usuario <?php echo htmlspecialchars($usuario['nombre']); ?>?</p>
                                                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                        <button type="submit" name="eliminar_usuario" class="btn btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Registrar -->
    <div class="modal fade" id="registrarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="rol_id" class="form-select" required>
                                <?php 
                                $roles->data_seek(0);
                                while ($rol = $roles->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $rol['id']; ?>">
                                        <?php echo htmlspecialchars($rol['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="registrar_usuario" class="btn btn-success">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 