<?php require_once 'app/views/templates/header.php'; ?>

<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Analyse télémétrique</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Informations de la session</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="tile-stats">
                                    <div class="icon"><i class="fa fa-user"></i></div>
                                    <div class="count"><?php echo htmlspecialchars($session['pilote_prenom'] . ' ' . $session['pilote_nom']); ?></div>
                                    <h3>Pilote</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="tile-stats">
                                    <div class="icon"><i class="fa fa-motorcycle"></i></div>
                                    <div class="count"><?php echo htmlspecialchars($session['moto_marque'] . ' ' . $session['moto_modele']); ?></div>
                                    <h3>Moto</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="tile-stats">
                                    <div class="icon"><i class="fa fa-road"></i></div>
                                    <div class="count"><?php echo htmlspecialchars($session['circuit_nom']); ?></div>
                                    <h3>Circuit</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="tile-stats">
                                    <div class="icon"><i class="fa fa-calendar"></i></div>
                                    <div class="count"><?php echo date('d/m/Y H:i', strtotime($session['date_session'])); ?></div>
                                    <h3>Date</h3>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>Conditions :</strong> <?php echo htmlspecialchars($session['conditions']); ?>,
                                    Température : <?php echo htmlspecialchars($session['temperature']); ?>°C,
                                    Humidité : <?php echo htmlspecialchars($session['humidite']); ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Vitesse et Régime moteur</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <canvas id="vitesseRegimeChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Accélération et Angle d'inclinaison</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <canvas id="accelerationInclinaisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Températures des pneus</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <canvas id="temperaturesPneusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Pressions des pneus</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <canvas id="pressionsPneusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Force de freinage</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <canvas id="freinageChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Tracé GPS</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div id="mapChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<script>
// Données pour les graphiques
const data = <?php echo json_encode($data); ?>;

// Configuration des graphiques
const timeLabels = data.timestamps.map(ts => {
    const date = new Date(ts * 1000);
    return date.toLocaleTimeString();
});

// Graphique Vitesse et Régime moteur
new Chart(document.getElementById('vitesseRegimeChart'), {
    type: 'line',
    data: {
        labels: timeLabels,
        datasets: [{
            label: 'Vitesse (km/h)',
            data: data.vitesse,
            borderColor: 'rgb(75, 192, 192)',
            yAxisID: 'y',
        }, {
            label: 'Régime moteur (tr/min)',
            data: data.regime_moteur,
            borderColor: 'rgb(255, 99, 132)',
            yAxisID: 'y1',
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Vitesse (km/h)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Régime moteur (tr/min)'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});

// Graphique Accélération et Angle d'inclinaison
new Chart(document.getElementById('accelerationInclinaisonChart'), {
    type: 'line',
    data: {
        labels: timeLabels,
        datasets: [{
            label: 'Accélération (g)',
            data: data.acceleration,
            borderColor: 'rgb(153, 102, 255)',
            yAxisID: 'y',
        }, {
            label: 'Angle d\'inclinaison (°)',
            data: data.angle_inclinaison,
            borderColor: 'rgb(255, 159, 64)',
            yAxisID: 'y1',
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Accélération (g)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Angle d\'inclinaison (°)'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});

// Graphique Températures des pneus
new Chart(document.getElementById('temperaturesPneusChart'), {
    type: 'line',
    data: {
        labels: timeLabels,
        datasets: [{
            label: 'Température pneu avant (°C)',
            data: data.temperatures_pneus.avant,
            borderColor: 'rgb(255, 99, 132)',
        }, {
            label: 'Température pneu arrière (°C)',
            data: data.temperatures_pneus.arriere,
            borderColor: 'rgb(54, 162, 235)',
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                title: {
                    display: true,
                    text: 'Température (°C)'
                }
            }
        }
    }
});

// Graphique Pressions des pneus
new Chart(document.getElementById('pressionsPneusChart'), {
    type: 'line',
    data: {
        labels: timeLabels,
        datasets: [{
            label: 'Pression pneu avant (bar)',
            data: data.pressions_pneus.avant,
            borderColor: 'rgb(255, 99, 132)',
        }, {
            label: 'Pression pneu arrière (bar)',
            data: data.pressions_pneus.arriere,
            borderColor: 'rgb(54, 162, 235)',
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                title: {
                    display: true,
                    text: 'Pression (bar)'
                }
            }
        }
    }
});

// Graphique Force de freinage
new Chart(document.getElementById('freinageChart'), {
    type: 'line',
    data: {
        labels: timeLabels,
        datasets: [{
            label: 'Force de freinage (N)',
            data: data.force_freinage,
            borderColor: 'rgb(153, 102, 255)',
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                title: {
                    display: true,
                    text: 'Force (N)'
                }
            }
        }
    }
});

// Carte GPS
const map = L.map('mapChart');
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

const coordinates = data.positions_gps.lat.map((lat, i) => [lat, data.positions_gps.long[i]]);
const polyline = L.polyline(coordinates, {color: 'red'}).addTo(map);
map.fitBounds(polyline.getBounds());

// Marqueur de départ
L.marker(coordinates[0], {
    icon: L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background-color:#4CAF50;' class='marker-pin'></div><i class='fa fa-flag' style='color: white;'></i>",
        iconSize: [30, 42],
        iconAnchor: [15, 42]
    })
}).addTo(map).bindPopup('Départ');

// Marqueur d'arrivée
L.marker(coordinates[coordinates.length - 1], {
    icon: L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background-color:#f44336;' class='marker-pin'></div><i class='fa fa-flag-checkered' style='color: white;'></i>",
        iconSize: [30, 42],
        iconAnchor: [15, 42]
    })
}).addTo(map).bindPopup('Arrivée');
</script>

<style>
.custom-div-icon {
    background: none;
    border: none;
}
.marker-pin {
    width: 30px;
    height: 30px;
    border-radius: 50% 50% 50% 0;
    position: absolute;
    transform: rotate(-45deg);
    left: 50%;
    top: 50%;
    margin: -15px 0 0 -15px;
}
.custom-div-icon i {
    position: absolute;
    width: 22px;
    font-size: 22px;
    left: 4px;
    top: 10px;
    text-align: center;
}
</style>

<?php require_once 'app/views/templates/footer.php'; ?> 