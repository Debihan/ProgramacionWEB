<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Incluir el archivo de conexión
include('../../habitos/conexion.php');

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

// Verificar que se han enviado los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['user_id'];
    $fecha = $_POST['fecha'];
    $hora_dormir = $_POST['hora_dormir'];
    $hora_despertar = $_POST['hora_despertar'];
    $calidad = $_POST['calidad'];
    $comentarios = isset($_POST['comentarios']) ? $_POST['comentarios'] : null;

    // Verificar si ya existe un registro para esta fecha
    $stmt = $conexion->prepare("SELECT id FROM seguimiento_sueno WHERE usuario_id = ? AND fecha = ?");
    $stmt->bind_param("is", $usuario_id, $fecha);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Si existe, actualizar el registro existente
        $id = $resultado->fetch_assoc()['id'];
        $query = "UPDATE seguimiento_sueno SET 
            hora_dormir = ?, 
            hora_despertar = ?, 
            calidad = ?, 
            comentarios = ? 
            WHERE id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ssssi", $hora_dormir, $hora_despertar, $calidad, $comentarios, $id);
    } else {
        // Si no existe, insertar nuevo registro
        $query = "INSERT INTO seguimiento_sueno (usuario_id, fecha, hora_dormir, hora_despertar, calidad, comentarios) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("isssss", $usuario_id, $fecha, $hora_dormir, $hora_despertar, $calidad, $comentarios);
    }

    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Registro de sueño guardado exitosamente']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error al guardar el registro: ' . $conexion->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conexion->close();
