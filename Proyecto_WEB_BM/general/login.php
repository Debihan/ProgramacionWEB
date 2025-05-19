<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Hábitos y Asociados</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="login-page">
    <div class="position-absolute top-0 start-0 m-3">
        <a href="../index.html" class="text-decoration-none text-white"><i class="fas fa-arrow-left me-2"></i>Volver</a>
    </div>
    <div class="login-container">
        <div class="logo-container">
            <img src="../img/enh_complete.png" alt="Logo Hábitos y Asociados">
        </div>
        <h1 class="login-title">Inicio de Sesión</h1>

        <?php
        session_start();
        // Mostrar mensajes de error
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        // Mostrar mensaje de éxito después del registro
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <!-- Formulario de Login -->
        <form action="../login_session.php" method="POST">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary-custom mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
            </button>
            <!--
            <div class="forgot-password">
                <a href="#">¿Olvidaste tu contraseña?</a>
            </div>
            -->
            <a href="../general/registro.php" class="btn btn-secondary-custom">
                <i class="bi bi-person-plus me-2"></i>Registrarse
            </a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>