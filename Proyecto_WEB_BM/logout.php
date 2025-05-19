<?php
// Iniciar la sesión
session_start();

// Limpia todas las variables de sesión
// Similar a session_unset() 
$_SESSION = array();

// Destruye la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirige al login
header("Location: general/login.php");
exit();
?> 