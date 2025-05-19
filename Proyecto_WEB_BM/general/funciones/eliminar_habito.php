<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar que se recibió el ID del hábito
if (!isset($_POST['habito_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// Incluir el archivo de conexión
include('../../habitos/conexion.php');

try {
    $habito_id = intval($_POST['habito_id']);
    $usuario_id = $_SESSION['user_id'];

    // Verificar que el hábito pertenece al usuario
    $query = "SELECT id FROM habitos WHERE id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("ii", $habito_id, $usuario_id);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Hábito no encontrado");
    }

    // Eliminar el hábito
    $query = "DELETE FROM habitos WHERE id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("ii", $habito_id, $usuario_id);
    if (!$stmt->execute()) {
        throw new Exception("Error al eliminar el hábito: " . $stmt->error);
    }

    echo json_encode(['success' => true, 'message' => 'Hábito eliminado correctamente']);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conexion)) {
        $conexion->close();
    }
}
?> 