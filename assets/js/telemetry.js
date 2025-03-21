class TelemetryVisualizer {
    constructor(sessionId) {
        this.sessionId = sessionId;
        this.charts = {};
        this.data = null;
        this.updateInterval = null;
        
        // Configuration des couleurs
        this.colors = {
            speed: 'rgb(75, 192, 192)',
            rpm: 'rgb(255, 99, 132)',
            suspensionFront: 'rgb(54, 162, 235)',
            suspensionRear: 'rgb(153, 102, 255)',
            temperatureFront: 'rgb(255, 159, 64)',
            temperatureRear: 'rgb(255, 205, 86)'
        };
    }

    async initialize() {
        try {
            // Initialiser les graphiques
            this.initializeCharts();
            
            // Charger les données initiales
            await this.fetchData();
            
            // Mettre à jour les graphiques
            this.updateCharts();
            
            // Démarrer la mise à jour automatique
            this.startAutoUpdate();
        } catch (error) {
            console.error('Erreur lors de l\'initialisation:', error);
            this.showError('Une erreur est survenue lors de l\'initialisation des graphiques');
        }
    }

    initializeCharts() {
        // Configuration commune pour tous les graphiques
        const commonConfig = {
            type: 'line',
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    tooltip: {
                        enabled: true
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'second'
                        },
                        title: {
                            display: true,
                            text: 'Temps'
                        }
                    }
                }
            }
        };

        // Initialiser chaque graphique avec sa configuration spécifique
        this.charts.speed = new Chart(
            document.getElementById('speedChart'),
            {
                ...commonConfig,
                data: {
                    datasets: [{
                        label: 'Vitesse',
                        borderColor: this.colors.speed,
                        borderWidth: 1,
                        data: []
                    }]
                },
                options: {
                    ...commonConfig.options,
                    scales: {
                        ...commonConfig.options.scales,
                        y: {
                            title: {
                                display: true,
                                text: 'Vitesse (km/h)'
                            }
                        }
                    }
                }
            }
        );

        this.charts.rpm = new Chart(
            document.getElementById('rpmChart'),
            {
                ...commonConfig,
                data: {
                    datasets: [{
                        label: 'Régime moteur',
                        borderColor: this.colors.rpm,
                        borderWidth: 1,
                        data: []
                    }]
                },
                options: {
                    ...commonConfig.options,
                    scales: {
                        ...commonConfig.options.scales,
                        y: {
                            title: {
                                display: true,
                                text: 'RPM'
                            }
                        }
                    }
                }
            }
        );

        this.charts.suspension = new Chart(
            document.getElementById('suspensionChart'),
            {
                ...commonConfig,
                data: {
                    datasets: [
                        {
                            label: 'Suspension avant',
                            borderColor: this.colors.suspensionFront,
                            borderWidth: 1,
                            data: []
                        },
                        {
                            label: 'Suspension arrière',
                            borderColor: this.colors.suspensionRear,
                            borderWidth: 1,
                            data: []
                        }
                    ]
                },
                options: {
                    ...commonConfig.options,
                    scales: {
                        ...commonConfig.options.scales,
                        y: {
                            title: {
                                display: true,
                                text: 'Position (mm)'
                            }
                        }
                    },
                    plugins: {
                        ...commonConfig.options.plugins,
                        legend: {
                            display: true
                        }
                    }
                }
            }
        );

        this.charts.temperature = new Chart(
            document.getElementById('temperatureChart'),
            {
                ...commonConfig,
                data: {
                    datasets: [
                        {
                            label: 'Température pneu avant',
                            borderColor: this.colors.temperatureFront,
                            borderWidth: 1,
                            data: []
                        },
                        {
                            label: 'Température pneu arrière',
                            borderColor: this.colors.temperatureRear,
                            borderWidth: 1,
                            data: []
                        }
                    ]
                },
                options: {
                    ...commonConfig.options,
                    scales: {
                        ...commonConfig.options.scales,
                        y: {
                            title: {
                                display: true,
                                text: 'Température (°C)'
                            }
                        }
                    },
                    plugins: {
                        ...commonConfig.options.plugins,
                        legend: {
                            display: true
                        }
                    }
                }
            }
        );
    }

    async fetchData() {
        try {
            const response = await fetch(`/api/telemetry.php?session_id=${this.sessionId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error || 'Une erreur est survenue');
            }

            this.data = result.data;
        } catch (error) {
            console.error('Erreur lors de la récupération des données:', error);
            this.showError('Une erreur est survenue lors de la récupération des données');
            throw error;
        }
    }

    updateCharts() {
        if (!this.data) return;

        // Mettre à jour les données de chaque graphique
        this.charts.speed.data.datasets[0].data = this.data.speed;
        this.charts.rpm.data.datasets[0].data = this.data.rpm;
        this.charts.suspension.data.datasets[0].data = this.data.suspensionFront;
        this.charts.suspension.data.datasets[1].data = this.data.suspensionRear;
        this.charts.temperature.data.datasets[0].data = this.data.temperatureFront;
        this.charts.temperature.data.datasets[1].data = this.data.temperatureRear;

        // Mettre à jour l'affichage des graphiques
        Object.values(this.charts).forEach(chart => chart.update());
    }

    startAutoUpdate() {
        // Mettre à jour les données toutes les 5 secondes
        this.updateInterval = setInterval(async () => {
            try {
                await this.fetchData();
                this.updateCharts();
            } catch (error) {
                console.error('Erreur lors de la mise à jour automatique:', error);
            }
        }, 5000);
    }

    stopAutoUpdate() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    showError(message) {
        const errorContainer = document.getElementById('telemetryError');
        if (errorContainer) {
            errorContainer.textContent = message;
            errorContainer.style.display = 'block';
        }
    }

    destroy() {
        // Arrêter la mise à jour automatique
        this.stopAutoUpdate();
        
        // Détruire les graphiques
        Object.values(this.charts).forEach(chart => chart.destroy());
        
        // Réinitialiser les données
        this.data = null;
        this.charts = {};
    }
}

// Exemple d'utilisation :
// const visualizer = new TelemetryVisualizer(sessionId);
// visualizer.initialize(); 