<?php
// Iniciar sesión y verificar autenticación
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Incluir el archivo de conexión a la base de datos
include('../habitos/conexion.php');

// Obtener el período seleccionado (por defecto: día)
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'dia';

// Función para calcular las fechas de inicio y fin segun el periodo seleccionado
function getFechasPeriodo($periodo) {
    $hoy = new DateTime();
    $inicio = new DateTime();
    $fin = new DateTime();
    
    // De momento suspendido.....
    switch($periodo) {
        case 'semana':
            // Para la semana: desde el lunes hasta el domingo
            $inicio->modify('monday this week');
            $fin->modify('sunday this week');
            break;
        case 'mes':
            // Para el mes: desde el primer día hasta el último día del mes
            $inicio->modify('first day of this month');
            $fin->modify('last day of this month');
            break;
        default: // día
            // Para el día: solo el día actual
            $inicio->modify('today');
            $fin->modify('today');
    }
    
    return ['inicio' => $inicio->format('Y-m-d'), 'fin' => $fin->format('Y-m-d')];
}

// Obtener las fechas según el periodo seleccionado
$fechas = getFechasPeriodo($periodo);

// Consultas para consumo de agua.....
// Obtener consumo de agua de hoy
$vasos_hoy = 0;
$meta_diaria = 8; // 8 vasos = 2L (la que será meta diaria)
$sql_agua = "SELECT IFNULL(vasos, 0) as vasos FROM seguimiento_agua WHERE usuario_id = ? AND fecha = CURDATE()"; /*solo para fecha actual*/
$stmt_agua = $conexion->prepare($sql_agua);         // Prepara la conexion
$stmt_agua->bind_param("i", $_SESSION['user_id']);  // Asocia el ID
$stmt_agua->execute();
$result_agua = $stmt_agua->get_result();            // Obtiene resulado

if ($result_agua->num_rows > 0) {
    $row_agua = $result_agua->fetch_assoc();        //Esto obtiene la fila de resultados
    $vasos_hoy = (int)$row_agua['vasos'];           //Convierte en enteros los resultados 
}
// Hacer que calcule resultado (porcentaje).....
$porcentaje_agua = min(100, round(($vasos_hoy / $meta_diaria) * 100));

// Consulta para estadísticas generales diarias.....
$query = "SELECT 
    COUNT(CASE WHEN rh.completado = 1 THEN 1 END) as completados,
    COUNT(CASE WHEN rh.completado = 0 OR rh.completado IS NULL THEN 1 END) as pendientes,
    COUNT(DISTINCT h.id) as total
FROM habitos h
LEFT JOIN registro_habitos rh ON h.id = rh.habito_id 
WHERE h.usuario_id = ?
AND h.frecuencia = 'diaria'";

// Consulta para obtener el porcentaje del día actual
$query_porcentaje_dia = "SELECT 
    COUNT(CASE WHEN rh.completado = 1 THEN 1 END) as completados,
    COUNT(*) as total
FROM registro_habitos rh
JOIN habitos h ON rh.habito_id = h.id
WHERE h.usuario_id = ?
AND DATE(rh.fecha) = CURDATE()
AND h.frecuencia = 'diaria'";
$stmt_porcentaje_dia = $conexion->prepare($query_porcentaje_dia);
$stmt_porcentaje_dia->bind_param("i", $_SESSION['user_id']);
$stmt_porcentaje_dia->execute();
$resultado_porcentaje_dia = $stmt_porcentaje_dia->get_result();
$porcentaje_dia = $resultado_porcentaje_dia->fetch_assoc();
$tasa_exito_dia = $porcentaje_dia['total'] > 0 ? round(($porcentaje_dia['completados'] / $porcentaje_dia['total']) * 100) : 0;

// Consulta para obtener datos para el gráfico de promedio de hábitos
$query_promedio = "SELECT 
    DATE(rh.fecha) as fecha,
    COUNT(CASE WHEN rh.completado = 1 THEN 1 END) as completados,
    COUNT(*) as total,
    ROUND((COUNT(CASE WHEN rh.completado = 1 THEN 1 END) / COUNT(*) * 100), 2) as porcentaje
FROM registro_habitos rh
JOIN habitos h ON rh.habito_id = h.id
WHERE h.usuario_id = ?
AND DATE(rh.fecha) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(rh.fecha)
ORDER BY DATE(rh.fecha) ASC";

// Consulta para obtener estadísticas de sueño
$query_sueno = "SELECT 
    AVG(duracion) as promedio_horas,
    COUNT(CASE WHEN calidad = 'Buena' THEN 1 END) as buenos,
    COUNT(CASE WHEN calidad = 'Regular' THEN 1 END) as regulares,
    COUNT(CASE WHEN calidad = 'Mala' THEN 1 END) as malos,
    MAX(duracion) as max_horas,
    MIN(duracion) as min_horas
FROM seguimiento_sueno
WHERE usuario_id = ?
AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$stmt_sueno = $conexion->prepare($query_sueno);
$stmt_sueno->bind_param("i", $_SESSION['user_id']);
$stmt_sueno->execute();
$resultado_sueno = $stmt_sueno->get_result();
$estadisticas_sueno = $resultado_sueno->fetch_assoc();

// Calcular porcentajes de calidad
$total_calidad = $estadisticas_sueno['buenos'] + $estadisticas_sueno['regulares'] + $estadisticas_sueno['malos'];
$porcentaje_bueno = $total_calidad > 0 ? round(($estadisticas_sueno['buenos'] / $total_calidad) * 100) : 0;
$porcentaje_regular = $total_calidad > 0 ? round(($estadisticas_sueno['regulares'] / $total_calidad) * 100) : 0;
$porcentaje_malo = $total_calidad > 0 ? round(($estadisticas_sueno['malos'] / $total_calidad) * 100) : 0;
$stmt_promedio = $conexion->prepare($query_promedio);
$stmt_promedio->bind_param("i", $_SESSION['user_id']);
$stmt_promedio->execute();
$resultado_promedio = $stmt_promedio->get_result();

// Convertir los datos a formato JSON para Chart.js
$labels = [];
$data = [];
$porcentajes = [];

// Obtener los últimos 7 días en orden descendente
$labels = [];
$data = [];
$porcentajes = [];

// Primero obtener todos los datos disponibles
$datos = [];
while ($row = $resultado_promedio->fetch_assoc()) {
    $datos[$row['fecha']] = [
        'completados' => $row['completados'],
        'porcentaje' => $row['porcentaje']
    ];
}

// Ahora obtener los últimos 7 días en orden descendente (incluyendo hoy)
$fecha = new DateTime();
for ($i = 0; $i < 7; $i++) {
    // Primero procesamos la fecha actual antes de modificar
    $fecha_str = $fecha->format('Y-m-d');
    
    // Agregar la fecha al inicio del array para que aparezca a la derecha
    array_unshift($labels, $fecha->format('d/m'));
    
    // Si hay datos para esta fecha, usarlos
    if (isset($datos[$fecha_str])) {
        array_unshift($data, $datos[$fecha_str]['completados']);
        array_unshift($porcentajes, $datos[$fecha_str]['porcentaje']);
    } else {
        // Si no hay datos, poner 0
        array_unshift($data, 0);
        array_unshift($porcentajes, 0);
    }
    
    // Después de procesar, retroceder un día para la próxima iteración
    $fecha->modify('-1 day');
}

$json_data = json_encode([
    'labels' => $labels,
    'data' => $data,
    'porcentajes' => $porcentajes
]);
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Calcular la tasa de éxito como porcentaje
// UTIlizar para tarjetas
$tasa_exito = $stats['total'] > 0 ? round(($stats['completados'] / $stats['total']) * 100) : 0;

// Consulta para obtener la racha actual de días consecutivos con hábitos completados
// Toma los dias consecutivos en los que el usuario realiza al menos 1 habito
$query = "WITH dias_con_habitos AS (
    SELECT DISTINCT DATE(rh.fecha) as fecha
    FROM registro_habitos rh
    JOIN habitos h ON rh.habito_id = h.id
    WHERE h.usuario_id = ?  -- Filtra ID de usaurio
    AND rh.completado = 1   -- Solo cuanta con habitos completados
    GROUP BY DATE(rh.fecha)
    ORDER BY DATE(rh.fecha) DESC
),
dias_consecutivos AS (
    SELECT 
        fecha,
        @dias_consecutivos := IF(DATEDIFF(@fecha_anterior, fecha) = 1, @dias_consecutivos + 1, 1) as racha, -- Lleva cuenta de racha actual
        @fecha_anterior := fecha
    FROM dias_con_habitos, 
        (SELECT @dias_consecutivos := 0, @fecha_anterior := NULL) as vars
    ORDER BY fecha DESC -- Importante para manejar la racha de manera correcta (filtrar en orden)
)
SELECT COALESCE(MAX(racha), 0) as racha -- Obtiene la racha mas larga que haya registrado, si no hay registros muestra 0
FROM dias_consecutivos";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$racha_actual = $stmt->get_result()->fetch_assoc()['racha'];

// Consulta para obtener la mejor racha histórica (habitos)
$query = "SELECT COUNT(*) as racha, h.nombre as nombre_habito -- Cuenta cuantos habitos se completaron en un día
FROM registro_habitos rh
JOIN habitos h ON rh.habito_id = h.id -- JOIN uniendo registro_habitos con habitos
WHERE h.usuario_id = ?
AND rh.completado = 1
GROUP BY DATE(rh.fecha)
ORDER BY COUNT(*) DESC
LIMIT 1";   // Solo selecciona 1 día (el que mas habitos tiene)
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$mejor_racha = $stmt->get_result()->fetch_assoc();
$mejor_racha = $mejor_racha ? $mejor_racha : ['racha' => 0, 'nombre_habito' => 'Ninguno'];

// Intento de contar racha (cuenta total de días diferentes con al mneos 1 habito)
$query = "SELECT COUNT(*) as racha
FROM (
    SELECT fecha, COUNT(*) as completados
    FROM registro_habitos rh
    JOIN habitos h ON rh.habito_id = h.id
    WHERE h.usuario_id = ? AND rh.completado = 1
    GROUP BY fecha
    ORDER BY fecha DESC
) as dias_consecutivos
WHERE completados > 0";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$racha = $stmt->get_result()->fetch_assoc()['racha'];



// Consulta para obtener el historial detallado de hábitos
$query = "SELECT 
    h.id,
    h.nombre,
    h.frecuencia,
    h.meta,
    h.fecha_registro,
    h.hora_registro,
    h.fecha_creacion,
    c.nombre as categoria_nombre,
    c.color as categoria_color,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM registro_habitos rh3 
            WHERE rh3.habito_id = h.id 
            AND rh3.fecha = DATE(h.fecha_registro)
            AND rh3.completado = 1
        ) THEN 'completado'
        WHEN h.fecha_registro >= CURDATE() THEN 'pendiente'
        ELSE 'atrasado'
    END as estado,
    (SELECT MAX(fecha) FROM registro_habitos rh4 WHERE rh4.habito_id = h.id) as ultimo_registro
FROM habitos h
LEFT JOIN categorias_habitos c ON h.categoria_id = c.id
WHERE h.usuario_id = ?
ORDER BY h.fecha_registro DESC, h.nombre";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", 
    $_SESSION['user_id']
);

$stmt->execute();
$historial = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Hábitos - Enhabita</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <div class="logo-container">
                    <img src="../img/enhabita_white2.png" alt="Logo" style="width: 70px; height: 70px; object-fit: contain;">
                </div>
                <span class="text-white">Enhabita</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="gestion-habitos.php">Gestión de Hábitos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Title and Filter Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h1 class="text-primary">Mi Progreso</h1>
            </div>
            <div class="col-md-6">
                <div class="btn-group float-end" role="group">
                    <button type="button" class="btn disabled" style="background-color:rgb(14, 116, 128); border-color:rgb(14, 116, 128);">
                        <span style="color: white;">Últimos 7 días</span>
                    </button>
                </div>
            </div>
        </div>



        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <!-- Racha Actual -->
            <div class="col-md-6">
                <div class="card h-100 habit-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Racha Actual</h5>
                            <div class="badge bg-primary">
                                <i class="bi bi-fire"></i>
                            </div>
                        </div>
                        <h2 class="mb-1"><?php echo $racha_actual; ?> días</h2>
                        <p class="card-text small text-muted mb-0">Días consecutivos completando al menos 1 hábito</p>
                    </div>
                </div>
            </div>

            <!-- Mejor Racha -->
            <div class="col-md-6">
                <div class="card h-100 habit-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Mejor Racha de Hábitos</h5>
                            <div class="badge bg-success">
                                <i class="bi bi-trophy"></i>
                            </div>
                        </div>
                        <h2 class="mb-1"><?php echo $mejor_racha['racha']; ?> hábitos</h2>
                        <p class="card-text small text-muted mb-0"><?php echo htmlspecialchars($mejor_racha['nombre_habito']); ?></p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Gráfico de Promedio de Hábitos -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Promedio de Hábitos Completados</h5>
                        <canvas id="habitsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Sueño -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">📊 Estadísticas de Sueño</h5>
                        <p class="text-muted mb-4">
                            <span class="d-block mb-2" style="font-size: 1.1rem;">
                                🌙 Se recomienda dormir entre 7-9 horas diarias para mantener un buen estado de salud
                            </span>
                            <small>
                                Abajo se muestra el promedio de tu sueño en los últimos 7 días
                            </small>
                        </p>
                        <div class="row">
                            <!-- Promedio de horas -->
                            <div class="col-md-4 mb-3">
                                <div class="card h-100" style="background-color:rgb(198, 227, 248)">
                                    <div class="card-body text-center">
                                        <i class="bi bi-clock-fill text-primary mb-2" style="font-size: 2rem;"></i>
                                        <h6 class="card-title mb-2">Promedio de Horas</h6>
                                        <h3 class="mb-0"><?php echo number_format($estadisticas_sueno['promedio_horas'], 1); ?>h</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Calidad del sueño -->
                            <div class="col-md-8">
                                <div class="card h-100" style="background-color:rgb(198, 227, 248)">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">Calidad del Sueño</h6>
                                        <div class="progress mb-2" style="height: 25px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $porcentaje_bueno; ?>%" 
                                                 aria-valuenow="<?php echo $porcentaje_bueno; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $porcentaje_bueno; ?>% Buena
                                            </div>
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $porcentaje_regular; ?>%" 
                                                 aria-valuenow="<?php echo $porcentaje_regular; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $porcentaje_regular; ?>% Regular
                                            </div>
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $porcentaje_malo; ?>%" 
                                                 aria-valuenow="<?php echo $porcentaje_malo; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $porcentaje_malo; ?>% Mala
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4">
                                                <h6 class="text-success mb-1">Buena</h6>
                                                <p class="mb-0"><?php echo $estadisticas_sueno['buenos']; ?> días</p>
                                            </div>
                                            <div class="col-4">
                                                <h6 class="text-warning mb-1">Regular</h6>
                                                <p class="mb-0"><?php echo $estadisticas_sueno['regulares']; ?> días</p>
                                            </div>
                                            <div class="col-4">
                                                <h6 class="text-danger mb-1">Mala</h6>
                                                <p class="mb-0"><?php echo $estadisticas_sueno['malos']; ?> días</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progreso de Agua -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0 overflow-hidden">
                        <div class="d-flex align-items-center" style="background: linear-gradient(135deg, #1e88e5, #0d47a1);">
                            <div class="p-4 text-white">
                                <h5 class="card-title mb-1 text-white">Mi Progreso de Agua</h5>
                                <p class="mb-0 small">Meta diaria: <?php echo $meta_diaria; ?> vasos (2L)</p>
                            </div>
                            <div class="ms-auto p-4 text-end">
                                <div class="display-4 fw-bold text-white"><?php echo $vasos_hoy; ?></div>
                                <span class="badge bg-white text-primary">Vasos hoy</span>
                            </div>
                        </div>
                        <div class="p-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Progreso diario</small>
                                <small><?php echo $porcentaje_agua; ?>%</small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: <?php echo $porcentaje_agua; ?>%" 
                                     aria-valuenow="<?php echo $porcentaje_agua; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <?php if ($vasos_hoy >= $meta_diaria): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> ¡Meta alcanzada!
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">
                                        Faltan <?php echo ($meta_diaria - $vasos_hoy); ?> vasos para tu meta
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Motivational Message -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card motivational-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-stars motivational-icon"></i>
                            <div class="ms-3">
                                <h5 class="card-title mb-1 text-info">
                                    <?php 
                                    if ($tasa_exito_dia == 0) {
                                        echo "¡Empieza ya!";
                                    } else {
                                        echo "¡Sigue así!";
                                    }
                                    ?>
                                </h5>
                                <p class="card-text mb-0">
                                    <?php 
                                    if ($tasa_exito_dia == 0) {
                                        echo "¡No has completado ningún hábito hoy! ¿Qué tal si empiezas con uno?";
                                    } else {
                                        echo "Has completado el " . $tasa_exito_dia . "% de tus hábitos diarios hoy. ¡Sigue así!";
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Habits History Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Historial de Hábitos</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Hábito</th>
                                <th>Categoría</th>
                                <th>Frecuencia</th>
                                <th>Meta</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($habito = $historial->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($habito['nombre']); ?></td>
                                <td>
                                    <?php if ($habito['categoria_nombre']): ?>
                                        <span class="badge" style="background-color: <?php echo $habito['categoria_color']; ?>">
                                            <?php echo htmlspecialchars($habito['categoria_nombre']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sin categoría</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo ucfirst($habito['frecuencia']); ?></td>
                                <td>
                                    <?php 
                                    if ($habito['frecuencia'] === 'diaria') {
                                        echo 'Diario';
                                    } else {
                                        echo $habito['meta'] . ' días';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($habito['fecha_registro']) {
                                        echo date('d/m/Y', strtotime($habito['fecha_registro']));
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    switch ($habito['estado']) {
                                        case 'atrasado':
                                            echo '<span class="badge bg-danger" title="Último registro: ' . 
                                                ($habito['ultimo_registro'] ? date('d/m/Y', strtotime($habito['ultimo_registro'])) : 'Nunca') . '">
                                                Atrasado
                                            </span>';
                                            break;
                                        case 'pendiente':
                                            echo '<span class="badge bg-warning">Pendiente</span>';
                                            break;
                                        case 'completado':
                                            echo '<span class="badge bg-success">Completado</span>';
                                            break;
                                    }
                                    ?>
                                </td>

                                <td>
                                    <button class="btn btn-danger btn-sm delete-habit" data-habito-id="<?php echo $habito['id']; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Pasar los datos del gráfico a JavaScript -->
    <script>
        // Hacer los datos del gráfico accesibles globalmente
        window.chartData = <?php echo $json_data; ?>;
    </script>
    
    <!-- Incluir Chart.js y el plugin de anotaciones -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.0.1/dist/chartjs-plugin-annotation.min.js"></script>
    
    <!-- Incluir nuestro script de gestión de hábitos -->
    <script src="../assets/js/gestion-habitos.js"></script>
    
    <!-- Footer -->
    <?php include('../habitos/footer.php'); ?>
</body>
</html>