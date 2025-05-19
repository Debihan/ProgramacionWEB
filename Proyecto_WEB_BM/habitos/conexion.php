<?php
// Configuración server infinityfree
efine('DB_HOST', 'sql205.infinityfree.com');
define('DB_USER', 'if0_38202890');
define('DB_PASS', 'Do1qxVVjQRRv');
define('DB_NAME', 'if0_38202890_habitos');

// Server local.....
// Borrado de momento :)

// Crear conexión
try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar la conexión
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }
    
    // Establecer el conjunto de caracteres
    $conexion->set_charset("utf8");
    
} catch (Exception $e) {
    // Manejo de errores
    error_log("Error de conexión: " . $e->getMessage());
    die("Lo sentimos, ha ocurrido un error al conectar. Por favor, intente más tarde.");
}

// Función para cerrar la conexión
function cerrarConexion() {
    global $conexion;
    if ($conexion) {
        $conexion->close();
    }
}

// Registrar la función para que se ejecute al finalizar el script
register_shutdown_function('cerrarConexion');
?>