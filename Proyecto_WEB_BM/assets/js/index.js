// Función para guardar el registro de sueño
function guardarSueno(event) {
    event.preventDefault();
    
    const form = document.getElementById('form-sueno');
    const formData = new FormData(form);

    fetch('funciones/guardar_sueno.php', {
        method: 'POST',
        body: formData
    })
    .then(() => {
        form.reset();
    })
    .catch(error => {
        console.error('Error:', error);
    });

    return false;
}

// Manejar frases motivacionales
function actualizarFraseMotivacional() {
    const hora = new Date().getHours();
    const container = document.querySelector('.frase-container');
    const titulo = document.getElementById('hora-titulo');
    const frase = document.getElementById('frase-motivacional');
    
    // Limpiar clases anteriores
    container.className = 'frase-container';
    
    let icono = '';
    let mensaje = '';

    if (hora >= 0 && hora < 5) {
        container.classList.add('noche');
        icono = '🌌';
        mensaje = '¿Aún despierto? No olvides cuidar tu descanso.';
    } else if (hora >= 5 && hora < 7) {
        container.classList.add('madrugada');
        icono = '🌅';
        mensaje = '¡Wow, madrugaste! Hoy puedes con todo.';
    } else if (hora >= 7 && hora < 9) {
        container.classList.add('mañana');
        icono = '☀️';
        mensaje = '¡Buen día! Inicia con intención y propósito.';
    } else if (hora >= 9 && hora < 12) {
        container.classList.add('mañana');
        icono = '🌤️';
        mensaje = '¡Sigue así! Tu esfuerzo de hoy será tu recompensa mañana.';
    } else if (hora >= 12 && hora < 14) {
        container.classList.add('mediodia');
        icono = '🌞';
        mensaje = '¡Hora de recargar energías y seguir brillando!';
    } else if (hora >= 14 && hora < 17) {
        container.classList.add('tarde');
        icono = '🌱';
        mensaje = '¡Aún queda tiempo para lograr algo genial hoy!';
    } else if (hora >= 17 && hora < 19) {
        container.classList.add('tarde');
        icono = '🌇';
        mensaje = '¡Bien hecho! Agradece lo logrado hoy.';
    } else if (hora >= 19 && hora < 22) {
        container.classList.add('noche');
        icono = '🌆';
        mensaje = 'Es momento de cuidar de ti y reflexionar.';
    } else {
        container.classList.add('noche');
        icono = '🌙';
        mensaje = '¡Hora de desconectarse y descansar! Mañana será otro gran día.';
    }

    titulo.textContent = `${icono} ${hora}:00 – ${(hora + 1).toString().padStart(2, '0')}:59 ${hora < 12 ? 'AM' : 'PM'}`;
    frase.textContent = mensaje;
}

// Cargar el contador de agua al iniciar
function cargarContadorAgua() {
    fetch('funciones/obtener_agua.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarInterfazAgua(data.vasos);
            }
        });
}

// Actualizar la interfaz con el número de vasos
function actualizarInterfazAgua(vasos) {
    const contador = document.getElementById('vasos-contador');
    const mensaje = document.getElementById('mensaje-motivador');
    const vasoBtns = document.querySelectorAll('.vaso-btn');
    
    // Actualizar contador
    contador.textContent = vasos;
    
    // Actualizar estado de los botones
    vasoBtns.forEach((btn, index) => {
        if (index < vasos) {
            btn.classList.add('btn-primary');
            btn.classList.remove('btn-outline-primary');
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
        }
    });
    
    // Actualizar mensaje motivacional
    actualizarMensajeMotivador(vasos);
}

// Actualizar mensaje según los vasos consumidos
function actualizarMensajeMotivador(vasos) {
    const mensajes = [
        '¡Empieza tu día con un vaso de agua!',
        '¡Buen comienzo! Sigue así.',
        '¡Vas por buen camino!',
        '¡Excelente! Mantén la hidratación.',
        '¡Increíble! Tu cuerpo te lo agradecerá.',
        '¡Espectacular! Casi llegas a la meta.',
        '¡Casi lo logras! Un último esfuerzo.',
        '¡Meta alcanzada! Estás bien hidratado.'
    ];
    
    const mensaje = document.getElementById('mensaje-motivador');
    const indice = Math.min(vasos, mensajes.length - 1);
    mensaje.textContent = mensajes[indice];
}

// Función para guardar los vasos de agua
function guardarVasosAgua(vasos) {
    fetch('funciones/guardar_agua.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `vasos=${vasos}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            actualizarInterfazAgua(vasos);
        } else {
            alert('Error al guardar: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al conectar con el servidor');
    });
}

// Función para actualizar la barra de progreso
function actualizarBarraProgreso() {
    const totalCheckboxes = document.querySelectorAll('.habit-checkbox').length;
    const checkboxesCompletados = document.querySelectorAll('.habit-checkbox:checked').length;
    const progreso = totalCheckboxes > 0 ? (checkboxesCompletados / totalCheckboxes) * 100 : 0;
    
    // Actualizar el texto y la barra
    const progressText = document.querySelector('.card-body span:first-child');
    const progressPercent = document.querySelector('.card-body span:last-child');
    const progressBar = document.querySelector('.progress-bar');
    
    if (progressText) progressText.textContent = `${checkboxesCompletados} de ${totalCheckboxes} hábitos completados`;
    if (progressPercent) progressPercent.textContent = `${Math.round(progreso)}%`;
    if (progressBar) progressBar.style.width = `${progreso}%`;
}

// Inicialización cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Configurar frases motivacionales
    actualizarFraseMotivacional();
    setInterval(actualizarFraseMotivacional, 60000);

    // Configurar botones de vasos de agua
    const vasoBtns = document.querySelectorAll('.vaso-btn');
    const contador = document.getElementById('vasos-contador');
    const mensaje = document.getElementById('mensaje-motivador');
    
    // Cargar el contador al iniciar
    cargarContadorAgua();
    
    // Manejar clic en los botones de vasos
    vasoBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const vasos = parseInt(this.getAttribute('data-vaso'));
            guardarVasosAgua(vasos);
            
            // Actualizar estado visual de los botones
            vasoBtns.forEach(b => b.classList.remove('active'));
            for (let i = 0; i < vasos; i++) {
                vasoBtns[i].classList.add('active');
            }
            
            // Actualizar contador y mensaje
            if (contador) contador.textContent = vasos;
            
            // Mensajes motivadores según el número de vasos
            const mensajes = {
                0: '¡Empieza tu día con un vaso de agua!',
                1: '¡Buen comienzo! Mantén el ritmo.',
                2: '¡Bien hecho! Estás en buen camino.',
                3: '¡Excelente! Casi lo logras.',
                4: '¡Casi! Sigue esforzándote.',
                5: '¡Casi! Sigue esforzándote.',
                6: '¡Casi! Sigue esforzándote.',
                7: '¡Perfecto! Has alcanzado tu objetivo.',
                8: '¡Perfecto! Has alcanzado tu objetivo.'
            };
            
            if (mensaje) mensaje.textContent = mensajes[vasos] || mensajes[0];
        });
    });

    // Manejar cambios en los checkboxes de hábitos
    document.querySelectorAll('.habit-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const habitoId = this.dataset.habitoId;
            const completado = this.checked;
            const statusBadge = this.closest('.habit-card')?.querySelector('.status-badge');
            
            // Actualizar la etiqueta de estado inmediatamente
            if (statusBadge) {
                statusBadge.textContent = completado ? 'Completado' : 'Pendiente';
                statusBadge.className = `badge status-badge ${completado ? 'status-completed' : 'status-pending'}`;
            }
            
            // Actualizar la barra de progreso inmediatamente
            actualizarBarraProgreso();
            
            fetch('funciones/actualizar_habito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `habito_id=${habitoId}&completado=${completado ? 1 : 0}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });

    // Manejar eliminación de hábitos
    document.querySelectorAll('.delete-habit').forEach(button => {
        button.addEventListener('click', function() {
            if (!confirm('¿Estás seguro de que deseas eliminar este hábito?')) {
                return;
            }
            
            const habitoId = this.dataset.habitoId;
            const habitCard = this.closest('.habit-card');
            
            fetch('funciones/eliminar_habito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `habito_id=${habitoId}`
            })
            .then(() => {
                if (habitCard) habitCard.remove();
                actualizarBarraProgreso();
            });
        });
    });

    // Manejar la visibilidad del campo meta según la frecuencia
    const frecuenciaSelect = document.getElementById('frecuencia');
    const metaContainer = document.getElementById('metaContainer');
    const metaInput = document.getElementById('meta');

    function actualizarCampoMeta() {
        if (!frecuenciaSelect || !metaContainer || !metaInput) return;
        
        const frecuencia = frecuenciaSelect.value;
        
        // Mostrar/ocultar el contenedor de meta según la frecuencia
        if (frecuencia === 'diaria') {
            metaContainer.style.display = 'none';
            metaInput.value = 1; // Establecer meta a 1 automáticamente
        } else {
            metaContainer.style.display = 'block';
            metaInput.value = ''; // Limpiar el valor de meta
        }
        
        // Configurar atributos según la frecuencia
        switch(frecuencia) {
            case 'diaria':
                metaInput.removeAttribute('required');
                metaInput.removeAttribute('min');
                metaInput.removeAttribute('max');
                metaInput.removeAttribute('placeholder');
                break;
            case 'semanal':
                metaInput.setAttribute('required', 'required');
                metaInput.setAttribute('min', '1');
                metaInput.setAttribute('max', '7');
                metaInput.setAttribute('placeholder', 'Días (1-7)');
                break;
            case 'mensual':
                metaInput.setAttribute('required', 'required');
                metaInput.setAttribute('min', '1');
                metaInput.setAttribute('max', '30');
                metaInput.setAttribute('placeholder', 'Días (1-30)');
                break;
            default:
                metaContainer.style.display = 'none';
                metaInput.value = 1;
                break;
        }
    }

    // Configurar evento de cambio en el select de frecuencia
    if (frecuenciaSelect) {
        frecuenciaSelect.addEventListener('change', actualizarCampoMeta);
        // Ejecutar una vez al cargar para configurar el estado inicial
        actualizarCampoMeta();
    }

    // Manejar el envío del formulario de nuevo hábito
    const nuevoHabitoForm = document.getElementById('nuevoHabitoForm');
    if (nuevoHabitoForm) {
        nuevoHabitoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const frecuencia = formData.get('frecuencia');
            
            // Si es diaria, establecer meta como 1
            if (frecuencia === 'diaria') {
                formData.set('meta', '1');
            }
            
            fetch('funciones/crear_habito.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al crear el hábito');
                }
            })
            .catch(error => {
                alert('Error al procesar la solicitud');
            });
        });
    }

    // Manejar cambios en los checkboxes de hábitos
    document.querySelectorAll('.habit-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const habitoId = this.dataset.habitoId;
            const completado = this.checked;
            const statusBadge = this.closest('.habit-card')?.querySelector('.status-badge');
            
            // Actualizar la etiqueta de estado inmediatamente
            if (statusBadge) {
                statusBadge.textContent = completado ? 'Completado' : 'Pendiente';
                statusBadge.className = `badge status-badge ${completado ? 'status-completed' : 'status-pending'}`;
            }
            
            // Actualizar la barra de progreso inmediatamente
            actualizarBarraProgreso();
            
            fetch('funciones/actualizar_habito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `habito_id=${habitoId}&completado=${completado ? 1 : 0}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });

    // Manejar eliminación de hábitos
    document.querySelectorAll('.delete-habit').forEach(button => {
        button.addEventListener('click', function() {
            if (!confirm('¿Estás seguro de que deseas eliminar este hábito?')) {
                return;
            }
            
            const habitoId = this.dataset.habitoId;
            const habitCard = this.closest('.habit-card');
            
            fetch('funciones/eliminar_habito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `habito_id=${habitoId}`
            })
            .then(() => {
                if (habitCard) habitCard.remove();
                actualizarBarraProgreso();
            });
        });
    });
});