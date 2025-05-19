<?php
// Archivo de momento para hash de admin y usuario de prueba
// Contraseñas actuales
$admin_password = '1234';
$user_password = 'penudo';

// Generar hashes
$admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
$user_hash = password_hash($user_password, PASSWORD_DEFAULT);

// Mostrar los hashes generados
echo "Hash para admin (1234): " . $admin_hash . "\n";
echo "Hash para usuario (penudo): " . $user_hash . "\n";

// Verificar que los hashes funcionan
echo "\nVerificación de hashes:\n";
echo "Admin (1234): " . (password_verify($admin_password, $admin_hash) ? "Correcto" : "Incorrecto") . "\n";
echo "Usuario (penudo): " . (password_verify($user_password, $user_hash) ? "Correcto" : "Incorrecto") . "\n";
?> 