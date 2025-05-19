<?php
// zona horaria a centro de México
date_default_timezone_set('America/Mexico_City');

session_start();

// Verificar si el usuario está logueado y es usuario normal
// En caso de que no sea redirige a login directamente
if (!isset($_SESSION['user_id']) || $_SESSION['rol_id'] != 2) {
    header("Location: login.php");
    exit();
}

// Incluir el archivo de conexión para conectar la base de datos
include('../habitos/conexion.php');

// CONSULTAS .....
// Obtener todos los datos del usuario
$query = "SELECT u.*, r.nombre as rol_nombre, e.descripcion as estado 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id 
          JOIN estatus e ON u.estatus_id = e.id 
          WHERE u.id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Esta consulta toma los habitos del día actual
$query = "SELECT 
    h.*, -- Toma todos los campos de la tabla habitos
    c.nombre as categoria_nombre, -- Nombre de la categoría
    c.color as categoria_color,-- Color de la categoría
    (SELECT completado FROM registro_habitos rh -- Verifica si el hábito fue completado hoy
     WHERE rh.habito_id = h.id 
     AND rh.fecha = CURDATE()) as completado_hoy,
    -- Formatea la fecha para poder mostrarla al usuario (día/mes/año)
    DATE_FORMAT(h.fecha_registro, '%d/%m/%Y') as fecha_formateada,
    -- Formatea la hora, muestra 'Sin horario' en caso de ser null
    IFNULL(DATE_FORMAT(h.hora_registro, '%H:%i'), 'Sin horario') as hora_formateada
FROM 
    habitos h
    -- JOIN con la tabla de categorías para obtener más información
    LEFT JOIN categorias_habitos c ON h.categoria_id = c.id
WHERE 
    -- Donde esté el usuario actual
    h.usuario_id = ? 
    -- Muestra solo los habitos de la fecha actual (arreglar en server)
    AND h.fecha_registro = CURDATE()
-- Ordena los resultados:
-- 1. Primero los que tienen hora
-- 2. Luego los que no
ORDER BY 
    h.hora_registro IS NULL,  -- Si no tiene registro de hora irá al final
    h.hora_registro";
$stmt = $conexion->prepare($query);     // Prepara la consulta
$stmt->bind_param("i", $_SESSION['user_id']); // (i = integer) Vincula de manera seguria el id de usuario (evitar inyección)
$stmt->execute();                           // Se ejecuta la consulta preparada
$habitos = $stmt->get_result();             // Obtiene los resultados de la consulta

// Calcular progreso del día
// Inicializar contadores
$total_habitos = 0;          // Revisa el total de habitos del día
$habitos_completados = 0;    // Revisa cuántos habitos están marcados como completados

// Recorre cada habito del resultado de la consulta
while ($habito = $habitos->fetch_assoc()) {
    $total_habitos++;  // Incrementa el contador de habitos totales
    
    // Si el habito está marcado como completado hoy
    if ($habito['completado_hoy']) {
        $habitos_completados++;  // Incrementa el contador de habitos completados
    }
}
$progreso = $total_habitos > 0 ? ($habitos_completados / $total_habitos) * 100 : 0;

// Resetear el puntero del resultado para usarlo en el bucle de visualización
$habitos->data_seek(0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Hábitos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"><!-- Iconos de Bootstrap -->
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/assets_sn.css">
    <link rel="stylesheet" href="../assets/style_index.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <div class="d-flex align-items-center">
                <div class="logo-container">
                    <img src="../img/enhabita_white2.png" alt="Logo" style="width: 70px; height: 70px; object-fit: contain;">
                </div>
                <a class="navbar-brand text-white" href="#">Enhabita</a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gestion-habitos.php">Gestión de Hábitos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="container">
            <h2>¡Hola, <?php echo htmlspecialchars($usuario['nombre']); ?>!</h2>
            <p class="mb-0">Mantén tu rutina y alcanza tus metas</p>
            <!-- Información adicional del usuario -->
            <!--
                Borrado :)
            -->
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Date Section -->
        <div class="date-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2"><?php echo date('l, j \d\e F'); ?></h3>
                    <p class="text-muted mb-0">Semana <?php echo date('W'); ?> - Día <?php echo date('N'); ?> de la semana</p>
                </div>
            </div>
        </div>

        <!-- Formulario de Nuevo Hábito -->
        <div class="card mb-4 habit-card">
            <div class="card-body">
                <h5 class="card-title mb-4 text-center">Crear Nuevo Hábito</h5>
                <form action="funciones/crear_habito.php" method="POST" class="row g-3 justify-content-center"> <!-- Se manda a crear_habito.php -->
                    <div class="col-12 col-lg-10">
                        <div class="row g-3">
                            <!-- Fila 1 -->
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="nombre" placeholder="Nombre del hábito" required>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="frecuencia" id="frecuencia" required>
                                    <option value="diaria" selected>Una vez (Por defecto)</option>
                                    <!--
                                    <option value="semanal">Semanal</option>
                                    <option value="mensual">Mensual</option>
                                    -->
                                </select>
                            </div>
                            <div class="col-md-2" id="metaContainer" style="display: none;">
                                <input type="number" class="form-control" name="meta" id="meta" placeholder="Días" min="1" max="30">
                            </div>
                            
                            <!-- Fila 2 -->
                            <div class="col-12">
                                <textarea class="form-control" name="descripcion" placeholder="Descripción del hábito" rows="2"></textarea>
                            </div>
                            
                            <!-- Fila 3 -->
                            <div class="col-md-4">
                                <select class="form-select" name="categoria">
                                    <option value="">Seleccionar categoría</option>
                                    <?php
                                    $query = "SELECT * FROM categorias_habitos ORDER BY nombre";
                                    $result = $conexion->query($query);
                                    while ($categoria = $result->fetch_assoc()) {
                                        echo "<option value='{$categoria['id']}'>{$categoria['nombre']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                            <input type="date" class="form-control" id="fecha_registro" 
                                                   name="fecha_registro" required
                                                   min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                            <input type="time" class="form-control" id="hora_registro" 
                                                   name="hora_registro" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 d-grid">
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-plus-circle me-1"></i>Crear Hábito
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Seguimiento de Agua -->
        <div class="card mb-4">
            <div class="card-body bg-light-blue">
                <h5 class="card-title mb-3 text-primary">💧 Seguimiento de Agua</h5>
                <div class="text-center mb-3">
                    <h6>Agua consumida hoy</h6>
                    <div class="fs-1 fw-bold" id="vasos-contador">0</div>
                    <div class="fs-5 text-muted" id="mililitros">0 ml</div>
                    <div class="mt-2 text-info" id="mensaje-recomendacion"></div>
                </div>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <button type="button" class="btn btn-outline-primary" onclick="actualizarAgua(-1)">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="actualizarAgua(1)">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-primary" onclick="guardarAgua()"> <!-- Se va a js para guardar el agua -->
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
        
        <script>
        let contadorAgua = 0;
        
        function actualizarAgua(cantidad) {
            const nuevoValor = contadorAgua + cantidad;
            const mensaje = document.getElementById('mensaje-recomendacion');
            
            // Limitar entre 0 y 15
            if (nuevoValor < 0) {
                contadorAgua = 0;
            } else if (nuevoValor > 15) {
                contadorAgua = 15;
                mensaje.textContent = '¡Límite alcanzado! No se recomienda consumir más de 15 vasos (3.75L)';
                mensaje.className = 'mt-2 text-danger fw-bold';
            } else {
                contadorAgua = nuevoValor;
            }
            
            // Actualizar contador de vasos
            document.getElementById('vasos-contador').textContent = contadorAgua;
            
            // Calcular y mostrar mililitros (250ml por vaso)
            const ml = contadorAgua * 250;
            const textoML = ml >= 1000 ? `${(ml/1000).toFixed(1)} L` : `${ml} ml`;
            document.getElementById('mililitros').textContent = textoML;
            
            // Mostrar mensaje de recomendación (solo si no estamos en el límite)
            if (contadorAgua < 15) {
                if (contadorAgua >= 8) {
                    mensaje.textContent = '¡Excelente! Has alcanzado la meta diaria recomendada de 2L';
                    mensaje.className = 'mt-2 text-success fw-bold';
                } else if (contadorAgua > 0) {
                    mensaje.textContent = `Te faltan ${8 - contadorAgua} vasos para llegar a 2L`;
                    mensaje.className = 'mt-2 text-info';
                } else {
                    mensaje.textContent = '';
                }
            }
        }
        
        // Guardar el agua
        function guardarAgua() {
            const mensaje = document.getElementById('mensaje-recomendacion');
            const botonGuardar = document.querySelector('button[onclick="guardarAgua()"]');
            
            // Guardar estado actual
            const mensajeAnterior = mensaje.textContent;
            const claseAnterior = mensaje.className;
            
            // Mostrar mensaje de carga
            mensaje.textContent = 'Guardando...';
            mensaje.className = 'mt-2 text-info';
            botonGuardar.disabled = true;
            
            // Crear y enviar formulario
            const formData = new FormData();
            formData.append('vasos', contadorAgua);
            
            fetch('funciones/guardar_agua.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data && data.success) {
                        mensaje.textContent = '¡Guardado correctamente!';
                        mensaje.className = 'mt-2 text-success fw-bold';
                    } else {
                        throw new Error(data.message || 'Error desconocido');
                    }
                } catch (e) {
                    // Si no es JSON o hay error al parsear
                    console.log('Respuesta del servidor:', text);
                    // Asumir éxito si el servidor no devuelve JSON
                    mensaje.textContent = '¡Guardado correctamente!';
                    mensaje.className = 'mt-2 text-success fw-bold';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mensaje.textContent = 'Guardado (error en la respuesta)';
                mensaje.className = 'mt-2 text-warning';
            })
            .finally(() => {
                // Restaurar mensaje después de 2 segundos
                setTimeout(() => {
                    if (mensaje.textContent.includes('Guardado')) {
                        // Solo restaurar si no se ha cambiado el mensaje
                        if (mensaje.textContent.includes('Guardado')) {
                            mensaje.textContent = mensajeAnterior;
                            mensaje.className = claseAnterior;
                        }
                    }
                }, 2000);
                botonGuardar.disabled = false;
            });
        }
        
        // Cargar el contador al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            fetch('funciones/obtener_agua.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        contadorAgua = data.vasos || 0;
                        document.getElementById('vasos-contador').textContent = contadorAgua;
                        
                        // Actualizar mililitros y mensaje
                        const ml = contadorAgua * 250;
                        const textoML = ml >= 1000 ? `${(ml/1000).toFixed(1)} L` : `${ml} ml`;
                        document.getElementById('mililitros').textContent = textoML;
                        
                        const mensaje = document.getElementById('mensaje-recomendacion');
                        if (contadorAgua >= 8) {
                            mensaje.textContent = '¡Excelente! Has alcanzado la meta diaria recomendada de 2L';
                            mensaje.className = 'mt-2 text-success fw-bold';
                        } else if (contadorAgua > 0) {
                            mensaje.textContent = `Te faltan ${8 - contadorAgua} vasos para llegar a 2L`;
                            mensaje.className = 'mt-2 text-info';
                        }
                    }
                });
        });
        </script>

        <!-- Frase Motivacional -->
        <div class="card mb-4">
            <div class="card-body frase-container">
                <div class="text-center">
                    <h5 class="card-title mb-3" id="hora-titulo"></h5>
                    <p class="fs-4 fw-bold mb-0" id="frase-motivacional"></p>
                </div>
            </div>
        </div>

        <!-- Seguimiento de Sueño -->
        <div class="card mb-4">
            <div class="card-body seguimiento-sueno">
                <h5 class="card-title mb-3 text-primary">😴 Seguimiento de Sueño</h5>
                <form id="form-sueno" class="row g-3" onsubmit="return guardarSueno(event)">
                    <div class="col-md-4">
                        <div class="input-group mb-3">
                            <input type="date" class="form-control" name="fecha" required
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <input type="time" class="form-control me-2" name="hora_dormir" required
                                   value="<?php echo date('H:i'); ?>">
                            <span class="input-group-text bg-primary text-white">Dormí</span>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <input type="time" class="form-control me-2" name="hora_despertar" required>
                            <span class="input-group-text bg-primary text-white">Desperté</span>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label d-flex align-items-center">
                            <i class="bi bi-moon-fill me-2"></i>Calidad del sueño
                        </label>
                        <div class="btn-group w-100" role="group" aria-label="Calidad del sueño">
                            <input type="radio" class="btn-check" name="calidad" id="calidad-buena" value="Buena" checked>
                            <label class="btn btn-primary-custom w-100" for="calidad-buena">
                                <i class="bi bi-star-fill"></i> Buena
                            </label>
                            
                            <input type="radio" class="btn-check" name="calidad" id="calidad-regular" value="Regular">
                            <label class="btn btn-secondary-custom w-100" for="calidad-regular">
                                <i class="bi bi-star"></i> Regular
                            </label>
                            
                            <input type="radio" class="btn-check" name="calidad" id="calidad-mala" value="Mala">
                            <label class="btn btn-danger-custom w-100" for="calidad-mala">
                                <i class="bi bi-star"></i> Mala
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <textarea class="form-control" name="comentarios" placeholder="Comentarios sobre tu sueño..."></textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-1"></i> Guardar Registro
                        </button>
                    </div>

                    <!-- Mensaje de éxito/error -->
                    <div class="col-12">
                        <div id="mensaje-sueno" class="alert d-none"></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Today's Progress -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Progreso de Hoy</h5>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><?php echo $habitos_completados; ?> de <?php echo $total_habitos; ?> hábitos completados</span>
                    <span class="text-primary"><?php echo round($progreso); ?>%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo $progreso; ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Today's Habits -->
        <h4 class="mb-3">Hábitos de Hoy</h4>
        
        <?php if ($habitos->num_rows > 0): ?>
            <?php while ($habito = $habitos->fetch_assoc()): ?>
                <div class="habit-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="form-check custom-checkbox me-3">
                                <input class="form-check-input habit-checkbox" type="checkbox" 
                                       id="habit<?php echo $habito['id']; ?>"
                                       data-habito-id="<?php echo $habito['id']; ?>"
                                       <?php echo $habito['completado_hoy'] ? 'checked' : ''; ?>>
                            </div>
                            <div class="habit-info">
                                <h5 class="mb-0 habit-title"><?php echo htmlspecialchars($habito['nombre']); ?></h5>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="habit-detail"><?php echo htmlspecialchars($habito['descripcion']); ?></small>
                                    <span class="habit-time text-muted">
                                        <?php if ($habito['hora_formateada'] !== 'Sin horario'): ?>
                                            <i class="bi bi-clock"></i> <?php echo $habito['hora_formateada']; ?>
                                        <?php else: ?>
                                            <span>Sin horario</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <?php if ($habito['categoria_nombre']): ?>
                                <span class="categoria-badge me-2" style="--categoria-color: <?php echo $habito['categoria_color']; ?>">
                                    <?php echo htmlspecialchars($habito['categoria_nombre']); ?>
                                </span>
                            <?php endif; ?>
                            <span class="badge status-badge <?php echo $habito['completado_hoy'] ? 'status-completed' : 'status-pending'; ?>">
                                <?php echo $habito['completado_hoy'] ? 'Completado' : 'Pendiente'; ?>
                            </span>
                            <button class="btn btn-link text-white delete-habit" data-habito-id="<?php echo $habito['id']; ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">
                No tienes hábitos configurados para hoy. ¡Agrega uno nuevo!
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS Bundle y JavaScript personalizado -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/index.js"></script>
    
    <!-- Footer -->
    <?php include('../habitos/footer.php'); ?>
</body>
</html>