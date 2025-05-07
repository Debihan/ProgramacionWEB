<?php
    
    if (isset($_POST["nombre"],$_POST["edad"], $_POST["correo"])){
        
        $nombre = $_POST["nombre"];
        $edad = $_POST["edad"];
        $correo = $_POST["correo"];

        if (!empty($nombre) && !empty($edad) && !empty($correo)) {
            // Mostrar mensaje para el usuario
            echo "<h2> Procesado :) $nombre </h2>";
            echo "<p>Edad registrada: $edad </p>";
            echo "<p>Correo registrado: $correo </p>";
            echo "<hr>";

            echo "Gracias por enviar";

            // Almacenamos la información en un archivo de texto
            $archivo = fopen("solicitud.txt", "a");
            $fecha = date("Y-m-d H:i:s");
            $contenido = "[$fecha] Nombre: $nombre | Edad: $edad | Correo: $correo\n";
            fwrite ($archivo, $contenido);
            fclose ($archivo);
        } else {
            echo "Todos los campos son obligatorios!";
        }

    } else {
        echo "Error: Todos los campos son obligatorios.";
    }