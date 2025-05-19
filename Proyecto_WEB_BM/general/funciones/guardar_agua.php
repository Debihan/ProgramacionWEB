<?php
// Configuración básica
date_default_timezone_set('America/Mexico_City');
session_start();

// Establecer el tipo de contenido
header('Content-Type: application/json');

// Verificar usuario
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

try {
    // Incluir conexión
    require_once('../../habitos/conexion.php');

    // Obtener y validar datos
    $vasos = isset($_POST['vasos']) ? intval($_POST['vasos']) : 0;
    $usuario_id = $_SESSION['user_id'];
    $fecha = date('Y-m-d');

    // Validar que los vasos no sean negativos
    if ($vasos < 0) {
        throw new Exception('La cantidad de vasos no puede ser negativa');
    }

    // Insertar o actualizar
    $query = "REPLACE INTO seguimiento_agua (usuario_id, fecha, vasos) 
              VALUES (?, ?, ?)";

    $stmt = $conexion->prepare($query);
    $stmt->bind_param("isi", $usuario_id, $fecha, $vasos);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Guardado correctamente',
            'vasos' => $vasos
        ]);
    } else {
        throw new Exception('Error al ejecutar la consulta');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conexion)) {
        $conexion->close();
    }
}
?>