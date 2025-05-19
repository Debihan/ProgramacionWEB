<?php
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: ../general/login.php");
    exit();
}

// Incluir el archivo de conexión
include('../habitos/conexion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Hábitos y Asociados</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos de bootstrap -->
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
                    <!-- Enlaces-->
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link active">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="usuarios.php" class="nav-link text-white">
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

            <!-- Contenido principal-->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Tarjetas de estadísticas generales -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card card-dashboard bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Usuarios</h5>
                                <h2 class="card-text">
                                    <?php
                                    $query = "SELECT COUNT(*) as total FROM usuarios";
                                    $result = $conexion->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card card-dashboard bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Hábitos Activos</h5>
                                <h2 class="card-text">
                                    <?php
                                    $query = "SELECT COUNT(*) as total FROM habitos";
                                    $result = $conexion->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card card-dashboard bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Completados Hoy</h5>
                                <h2 class="card-text">
                                    <?php
                                    $query = "SELECT COUNT(*) as total FROM registro_habitos WHERE fecha = CURDATE() AND completado = 1";
                                    $result = $conexion->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card card-dashboard bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Usuarios Activos</h5>
                                <h2 class="card-text">
                                    <?php
                                    $query = "SELECT COUNT(*) as total FROM usuarios WHERE estatus_id = 1";
                                    $result = $conexion->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ..... -->
                <!-- Tabla para mostrar los ultimos usuarios que han sido registrados -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Últimos Usuarios Registrados</h5>
                    </div>
                    <div class="card-body">

                        <!-- Tabla de Usuarios -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Fecha Registro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT u.*, r.nombre as rol_nombre, e.descripcion as estado -- Toma todas las columnas de usuarios
                                             FROM usuarios u 
                                             JOIN roles r ON u.rol_id = r.id 
                                             JOIN estatus e ON u.estatus_id = e.id 
                                             ORDER BY u.fecha_creacion DESC LIMIT 5"; //Ordenar por fechas de creación
                                    $result = $conexion->query($query); // Ejecuta la consulta usando la conexion a la base
                                    while ($row = $result->fetch_assoc()) { // Mostrar resultados
                                        echo "<tr>";
                                        echo "<td>" . $row['id'] . "</td>";
                                        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['rol_nombre']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($row['fecha_creacion'])) . "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
