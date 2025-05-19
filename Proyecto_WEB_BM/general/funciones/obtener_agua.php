<?php
// Configuración básica
date_default_timezone_set('America/Mexico_City');
session_start();

// Verificar usuario
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Usuario no autenticado']));
}

// Incluir conexión
require_once('../../habitos/conexion.php');

// Obtener vasos del día actual
$query = "SELECT COALESCE(vasos, 0) as vasos 
          FROM seguimiento_agua 
          WHERE usuario_id = ? AND fecha = CURDATE()
          LIMIT 1";

try {
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $vasos = ($result->num_rows > 0) ? (int)$result->fetch_assoc()['vasos'] : 0;
    
    echo json_encode(['success' => true, 'vasos' => $vasos]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener datos']);
}

$conexion->close();
?>
