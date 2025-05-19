// Obtener el contexto del canvas donde se dibujará el gráfico
const ctx = document.getElementById('habitsChart').getContext('2d');
// Obtener los datos del gráfico pasados desde PHP o usar un objeto vacío si no hay datos
const data = window.chartData || { labels: [], data: [] };

// Calcular estadísticas para el gráfico:
// - maxHabits: El número más alto de hábitos completados
// - maxIndex: La posición en el array del día con más hábitos
// - maxLabel: La etiqueta (fecha) del día con más hábitos
const maxHabits = data.data.length > 0 ? Math.max(...data.data) : 0;
const maxIndex = data.data.length > 0 ? data.data.indexOf(maxHabits) : -1;
const maxLabel = maxIndex >= 0 ? data.labels[maxIndex] : '';

new Chart(ctx, {
    type: 'line',
    data: {
        labels: data.labels,
        datasets: [
            {
                label: 'Hábitos Completados',
                data: data.data,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: function(context) {
                    const index = context.dataIndex;
                    return index === maxIndex ? 'rgba(255, 99, 132, 1)' : 'rgba(75, 192, 192, 1)';
                },
                pointBorderColor: function(context) {
                    const index = context.dataIndex;
                    return index === maxIndex ? 'rgba(255, 99, 132, 1)' : 'rgba(75, 192, 192, 1)';
                },
                pointHoverBackgroundColor: 'rgba(255, 99, 132, 1)'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Número de Hábitos'
                },
                ticks: {
                    stepSize: 1,
                    callback: function(value) {
                        return Math.round(value);
                    }
                },
                max: function() {
                    const maxData = data.data.length > 0 ? Math.max(...data.data) : 10;
                    return maxData > 10 ? Math.ceil(maxData) : 10;
                }
            }
        },
        plugins: {
            legend: {
                position: 'top'
            },
            title: {
                display: true,
                text: 'Hábitos Completados por Día'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += context.parsed.y;
                            if (context.dataIndex === maxIndex) {
                                label += ' (Máximo)';
                            }
                        }
                        return label;
                    }
                }
            },
            annotation: {
                annotations: {
                    maxPoint: {
                        type: 'point',
                        xValue: maxLabel,
                        yValue: maxHabits,
                        pointStyle: 'star',
                        radius: 8,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        backgroundColor: 'rgba(255, 99, 132, 1)',
                        label: {
                            content: 'Máximo',
                            enabled: true,
                            position: 'top'
                        }
                    }
                }
            }
        }
    }
});

// Manejar la eliminación de hábitos cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Agregar evento de clic a todos los botones de eliminar
    document.querySelectorAll('.delete-habit').forEach(button => {
        button.addEventListener('click', function() {
            // Obtener el ID del hábito desde el atributo de datos
            const habitoId = this.dataset.habitoId;
            
            // Enviar solicitud para eliminar el hábito al servidor
            fetch('funciones/eliminar_habito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `habito_id=${habitoId}`
            })
            .then(() => {
                // Si la eliminación es exitosa, remover la fila de la tabla
                this.closest('tr').remove();
            });
        });
    });
});