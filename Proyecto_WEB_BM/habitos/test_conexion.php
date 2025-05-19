
<?php
// Esto es un archivo de prueba para la conexion, de momento está desactualizado
// Incluir el archivo de conexión
require_once 'conexion.php';

// Intentar realizar una consulta simple
try {
    // Consulta simple para verificar la conexión
    $query = "SHOW TABLES";
    $resultado = $conexion->query($query);
    
    if ($resultado) {
        echo "<h2>Conexión exitosa a la base de datos</h2>";
        echo "<h3>Tablas encontradas en la base de datos:</h3>";
        echo "<ul>";
        while ($row = $resultado->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "Error al ejecutar la consulta: " . $conexion->error;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 