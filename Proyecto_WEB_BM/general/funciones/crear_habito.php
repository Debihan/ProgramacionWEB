<?php
session_start();
include('../../habitos/conexion.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $frecuencia = $_POST['frecuencia'] ?? null;
    $meta = $_POST['meta'] ?? 1;
    $categoria = $_POST['categoria'] ?? null;
    $usuario_id = $_SESSION['user_id'];
    $fecha_registro = $_POST['fecha_registro'] ?? null;
    $hora_registro = $_POST['hora_registro'] ?? null;

    // Validar campos requeridos
    if (empty($nombre)) {
        header("Location: ../index.php?error=campos_requeridos");
        exit;
    }

    // Validar meta solo si se especifica frecuencia
    if ($frecuencia) {
        if ($frecuencia === 'semanal' && (!isset($meta) || $meta < 1 || $meta > 7)) {
            header("Location: ../index.php?error=meta_semanal");
            exit;
        }
        if ($frecuencia === 'mensual' && (!isset($meta) || $meta < 1 || $meta > 30)) {
            header("Location: ../index.php?error=meta_mensual");
            exit;
        }
    }

    try {
        // Insertar en la base de datos
        $query = "INSERT INTO habitos (
                    usuario_id, nombre, descripcion, 
                    frecuencia, meta, categoria_id, 
                    fecha_registro, hora_registro, 
                    fecha_creacion
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                 
        $stmt = $conexion->prepare($query);
        $stmt->bind_param(
            "isssiisss",
            $usuario_id,
            $nombre,
            $descripcion,
            $frecuencia,
            $meta,
            $categoria,
            $fecha_registro,
            $hora_registro,
            date('Y-m-d H:i:s')
        );

        if ($stmt->execute()) {
            header("Location: ../index.php?success=1");
        } else {
            header("Location: ../index.php?error=db");
        }
    } catch (Exception $e) {
        error_log("Error al crear hábito: " . $e->getMessage());
        header("Location: ../index.php?error=db");
    }
} else {
    header("Location: ../index.php");
}
exit;
?>