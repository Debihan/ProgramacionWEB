<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar que se recibieron los datos necesarios
if (!isset($_POST['habito_id']) || !isset($_POST['completado'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// Incluir el archivo de conexión
include('../../habitos/conexion.php');

try {
    $habito_id = intval($_POST['habito_id']);
    $completado = intval($_POST['completado']);
    $usuario_id = $_SESSION['user_id'];
    $fecha_actual = date('Y-m-d');

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

    // Verificar si ya existe un registro para hoy
    $query = "SELECT id FROM registro_habitos WHERE habito_id = ? AND fecha = CURDATE()";
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("i", $habito_id);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Actualizar registro existente
        $query = "UPDATE registro_habitos SET completado = ? WHERE habito_id = ? AND fecha = CURDATE()";
        $stmt = $conexion->prepare($query);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("ii", $completado, $habito_id);
    } else {
        // Crear nuevo registro
        $query = "INSERT INTO registro_habitos (habito_id, fecha, completado) VALUES (?, CURDATE(), ?)";
        $stmt = $conexion->prepare($query);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("ii", $habito_id, $completado);
    }

    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar el registro: " . $stmt->error);
    }

    echo json_encode(['success' => true, 'message' => 'Hábito actualizado correctamente']);
    
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