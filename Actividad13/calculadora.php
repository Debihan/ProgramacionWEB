<?php

    $num1 = $_GET['num1'];
    $num2 = $_GET['num2'];
    $opc = $_GET['opc'];

    if (is_numeric($num1) && is_numeric($num2)) { // validar si se usan numeros :)

        $num1 = floatval($num1);
        $num2 = floatval($num2);

        switch($opc){
            case 'sumar':
                $resultado = $num1 + $num2;
                break;
        
            case 'restar':
                $resultado = $num1 - $num2;
                break;

            case 'multiplicar':
                $resultado = $num1 * $num2;
                break;

            case 'dividir':
                if($num2 == 0){
                    $resultado = "No se puede dividir entre 0";
                }else{
                    $resultado = $num1 / $num2;
                }
                break;

            default:
                $resultado = "Invalido";
        }
        

    } else {
        $resultado = "Por favor ingresa valores numericos";
    }

    echo "<h3>Resultado: $resultado</h3>";



?>