<?php
// Iniciar sesión
session_start();

// Seguridad para manejo de intentos de sesi[on]
$max_intentos = 3;         // Número máximo de intentos permitidos
$tiempo_bloqueo = 180;     // 3 minutos de bloqueo (en segundos)

// Tomar el archivo de conexión
require_once('habitos/conexion.php');

// Inicializar variables de intentos si no existen
if (!isset($_SESSION['intentos_login'])) {
    $_SESSION['intentos_login'] = 0;
    $_SESSION['ultimo_intento'] = 0;
}

// Verificar si el usuario está bloqueado
if (isset($_SESSION['bloqueado_hasta']) && $_SESSION['bloqueado_hasta'] > time()) {
    $tiempo_restante = ceil(($_SESSION['bloqueado_hasta'] - time()) / 60);
    $_SESSION['error'] = "Demasiados intentos fallidos. Por favor, intente nuevamente en $tiempo_restante minutos.";
    header("Location: general/login.php");
    exit();
}

// Si ha pasado el tiempo de bloqueo, reiniciar contador
if (isset($_SESSION['ultimo_intento']) && (time() - $_SESSION['ultimo_intento'] > $tiempo_bloqueo)) {
    $_SESSION['intentos_login'] = 0;
    unset($_SESSION['bloqueado_hasta']);
}

// Comprueba si se envian datos desde el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar si se ha excedido el número de intentos
    if ($_SESSION['intentos_login'] >= $max_intentos) {
        $_SESSION['bloqueado_hasta'] = time() + $tiempo_bloqueo;
        $tiempo_restante = ceil($tiempo_bloqueo / 60);
        $_SESSION['error'] = "Demasiados intentos fallidos. Por favor, intente nuevamente en $tiempo_restante minutos.";
        header("Location: general/login.php");
        exit();
    }

    // Obtener y limpiar los datos del formulario
    $correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Validar correo
    // Se valida el correo de acuerdo a un estandar de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['intentos_login']++; //En caso de cumplir aumenta los intentos fallidos
        $_SESSION['ultimo_intento'] = time();
        $_SESSION['error'] = "Por favor, ingrese un correo electrónico válido.";
        header("Location: general/login.php");
        exit();
    }

    // Consultar en la base de datos
    $query = "SELECT * FROM usuarios WHERE correo = ? AND estatus_id = 1";  // Solo usuarios activos
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verifica la contraseña
        if (password_verify($password, $row['password'])) {
            // Restablecer contador de intentos
            unset($_SESSION['intentos_login']);
            unset($_SESSION['bloqueado_hasta']);
            unset($_SESSION['ultimo_intento']);
            
            // Regenerar ID de sesión
            session_regenerate_id(true);
            
            // Iniciar sesión
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nombre'] = htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8');
            $_SESSION['correo'] = $row['correo'];
            $_SESSION['rol_id'] = (int)$row['rol_id'];

            // Redirige a la pagina según el rol
            $redirect = ($row['rol_id'] == 1) ? 'admin/dashboard.php' : 'general/index.php';
            header("Location: $redirect");
            exit();
        }
    }

    // Incrementar contador de intentos fallidos
    $_SESSION['intentos_login']++;
    $_SESSION['ultimo_intento'] = time();
    
    // Calcular intentos restantes
    $intentos_restantes = $max_intentos - $_SESSION['intentos_login'];
    
    // Mostrar mensaje según los intentos restantes
    if ($intentos_restantes > 0) {
        $_SESSION['error'] = "Credenciales incorrectas. Te quedan $intentos_restantes intentos.";
    } else {
        $_SESSION['error'] = "Demasiados intentos fallidos. Por favor, intente nuevamente en " . ($tiempo_bloqueo / 60) . " minutos.";
        $_SESSION['bloqueado_hasta'] = time() + $tiempo_bloqueo;
    }
    header("Location: general/login.php");
    exit();
} else {
    // Si alguien intenta acceder directamente a este archivo
    header("Location: general/login.php");
    exit();
}
?>