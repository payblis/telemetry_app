/* JavaScript principal pour l'application de télémétrie moto */

// Attendre que le document soit chargé
document.addEventListener('DOMContentLoaded', function() {
    // Fermeture automatique des alertes après 5 secondes
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Activation des tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Activation des popovers Bootstrap
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Confirmation pour les actions de suppression
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });

    // Initialisation des datepickers si présents
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            locale: 'fr'
        });
    }

    // Initialisation des select2 si présents
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // Fonction pour initialiser les graphiques si Chart.js est présent
    if (typeof Chart !== 'undefined') {
        initCharts();
    }
});

// Fonction pour initialiser les graphiques
function initCharts() {
    // Graphique de vitesse si présent
    const speedChartCanvas = document.getElementById('speedChart');
    if (speedChartCanvas) {
        const ctx = speedChartCanvas.getContext('2d');
        
        // Récupérer les données du graphique depuis l'attribut data
        const chartData = JSON.parse(speedChartCanvas.getAttribute('data-chart'));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Vitesse (km/h)',
                    data: chartData.data,
                    borderColor: '#0066cc',
                    backgroundColor: 'rgba(0, 102, 204, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Vitesse (km/h)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Temps'
                        }
                    }
                }
            }
        });
    }

    // Graphique d'accélération si présent
    const accelChartCanvas = document.getElementById('accelChart');
    if (accelChartCanvas) {
        const ctx = accelChartCanvas.getContext('2d');
        
        // Récupérer les données du graphique depuis l'attribut data
        const chartData = JSON.parse(accelChartCanvas.getAttribute('data-chart'));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Accélération X',
                    data: chartData.dataX,
                    borderColor: '#e30613',
                    backgroundColor: 'rgba(227, 6, 19, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'Accélération Y',
                    data: chartData.dataY,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'Accélération Z',
                    data: chartData.dataZ,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: 'Accélération (g)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Temps'
                        }
                    }
                }
            }
        });
    }
}

// Fonction pour charger les données télémétriques via AJAX
function loadTelemetryData(sessionId, callback) {
    fetch(`index.php?page=api_telemetry&session_id=${sessionId}`)
        .then(response => response.json())
        .then(data => {
            if (callback && typeof callback === 'function') {
                callback(data);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des données:', error);
        });
}

// Fonction pour formater le temps au tour
function formatLapTime(seconds) {
    if (!seconds) return '--:--';
    
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = (seconds % 60).toFixed(3);
    return `${minutes}:${remainingSeconds.padStart(6, '0')}`;
}
