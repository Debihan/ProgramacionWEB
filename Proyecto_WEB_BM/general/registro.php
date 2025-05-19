<?php
session_start();

// Si el usuario ya está logueado, redirigir a su página principal
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Incluir el archivo de conexión
include('../habitos/conexion.php');

$error = '';
$success = '';

// Procesar el registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones
    if (empty($nombre) || empty($correo) || empty($password) || empty($confirm_password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif (strlen($password) < 3) { // de momento 3 para probar, cambiar luego
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el correo ya existe
        $query = "SELECT id FROM usuarios WHERE correo = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Este correo electrónico ya está registrado.";
        } else {
            // Crear el usuario
            $password_hash = password_hash($password, PASSWORD_DEFAULT); // Funcion segura de php para encriptar contraserñas
            $rol_id = 2; // Rol de usuario normal
            $estatus_id = 1; // Activo por defecto

            // Query para insertar nuevo usuario en la base de datos :)
            $query = "INSERT INTO usuarios (nombre, correo, password, rol_id, estatus_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("sssii", $nombre, $correo, $password_hash, $rol_id, $estatus_id);

            // Una vez registrar -> al loguear se toma el hash de la base de datos (listo)

            if ($stmt->execute()) {
                // Guardar mensaje de éxito en sesión
                $_SESSION['success'] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
                // Redirigir despues de que el registro haya sido exitoso :)
                header("Location: login.php");
                exit();
            } else {
                $error = "Error al registrar el usuario. Por favor, intente nuevamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Hábitos y Asociados</title>
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
        <h1 class="login-title">Crear Cuenta</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingresa tu nombre completo" required
                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                <div class="invalid-feedback">
                    Por favor ingrese su nombre.
                </div>
            </div>

            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@correo.com" required
                       value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
                <div class="invalid-feedback">
                    Por favor ingrese un correo electrónico válido.
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Crea una contraseña segura" required>
                <div class="invalid-feedback">
                    Por favor ingrese una contraseña.
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirma tu contraseña" required>
                <div class="invalid-feedback">
                    Por favor confirme su contraseña.
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom mb-3">
                <i class="bi bi-person-plus me-2"></i>Registrarse
            </button>

            <div class="forgot-password">
                <span>¿Ya tienes cuenta? </span>
                <a href="login.php">Inicia sesión</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación del formulario
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Validación de contraseñas coincidentes
        document.getElementById('confirm_password').addEventListener('input', function() {
            if (this.value !== document.getElementById('password').value) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
