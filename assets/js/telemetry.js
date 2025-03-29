class TelemetryManager {
    constructor(sessionId) {
        this.sessionId = sessionId;
        this.isActive = false;
        this.currentLap = 0;
        this.lapStartTime = 0;
        this.bestLapTime = null;
        this.lastLapTime = null;
        this.maxSpeed = 0;
        this.updateInterval = null;
        this.lapInterval = null;

        // Éléments DOM
        this.speedElement = document.getElementById('speedValue');
        this.rpmElement = document.getElementById('rpmValue');
        this.currentLapElement = document.getElementById('currentLap');
        this.bestLapElement = document.getElementById('bestLap');
        this.lastLapElement = document.getElementById('lastLap');
        this.lapTableBody = document.querySelector('#lapTable tbody');

        // Initialisation des événements
        this.initEventListeners();
    }

    initEventListeners() {
        document.getElementById('startSession').addEventListener('click', () => this.startSession());
        document.getElementById('stopSession').addEventListener('click', () => this.stopSession());
    }

    startSession() {
        this.isActive = true;
        this.currentLap = 0;
        this.lapStartTime = Date.now();
        document.getElementById('startSession').style.display = 'none';
        document.getElementById('stopSession').style.display = 'inline-block';
        
        // Démarrer les mises à jour
        this.startUpdates();
    }

    stopSession() {
        this.isActive = false;
        document.getElementById('stopSession').style.display = 'none';
        document.getElementById('startSession').style.display = 'inline-block';
        
        // Arrêter les mises à jour
        this.stopUpdates();
    }

    startUpdates() {
        // Mise à jour des données toutes les 100ms
        this.updateInterval = setInterval(() => this.updateTelemetryData(), 100);
        
        // Mise à jour du chrono toutes les 10ms
        this.lapInterval = setInterval(() => this.updateLapTime(), 10);
    }

    stopUpdates() {
        clearInterval(this.updateInterval);
        clearInterval(this.lapInterval);
    }

    async updateTelemetryData() {
        try {
            const response = await fetch(`api/telemetry.php?action=data&session_id=${this.sessionId}`);
            const data = await response.json();

            if (data.success && data.data.length > 0) {
                const latestData = data.data[data.data.length - 1];
                this.updateDisplay(latestData);
                this.saveTelemetryData(latestData);
            }
        } catch (error) {
            console.error('Erreur lors de la mise à jour des données:', error);
        }
    }

    updateDisplay(data) {
        // Mettre à jour les compteurs
        this.speedElement.textContent = Math.round(data.speed);
        this.rpmElement.textContent = Math.round(data.rpm);

        // Mettre à jour la vitesse maximale
        if (data.speed > this.maxSpeed) {
            this.maxSpeed = data.speed;
        }
    }

    updateLapTime() {
        if (!this.isActive) return;

        const currentTime = Date.now();
        const lapTime = currentTime - this.lapStartTime;
        this.currentLapElement.textContent = this.formatTime(lapTime);
    }

    async completeLap() {
        const lapTime = Date.now() - this.lapStartTime;
        this.currentLap++;

        // Préparer les données du tour
        const lapData = {
            lap_number: this.currentLap,
            lap_time: lapTime,
            sector1_time: 0, // À implémenter
            sector2_time: 0, // À implémenter
            sector3_time: 0, // À implémenter
            max_speed: this.maxSpeed
        };

        // Sauvegarder le tour
        try {
            const response = await fetch(`api/telemetry.php?action=save_lap&session_id=${this.sessionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(lapData)
            });

            const result = await response.json();
            if (result.success) {
                this.updateLapTable(lapData);
                this.lastLapTime = lapTime;

                // Mettre à jour le meilleur temps
                if (!this.bestLapTime || lapTime < this.bestLapTime) {
                    this.bestLapTime = lapTime;
                    this.bestLapElement.textContent = this.formatTime(lapTime);
                    this.bestLapElement.classList.add('new-best');
                    setTimeout(() => this.bestLapElement.classList.remove('new-best'), 1000);
                }
            }
        } catch (error) {
            console.error('Erreur lors de l\'enregistrement du tour:', error);
        }

        // Réinitialiser pour le prochain tour
        this.lapStartTime = Date.now();
        this.maxSpeed = 0;
    }

    async saveTelemetryData(data) {
        try {
            await fetch(`api/telemetry.php?action=save_data&session_id=${this.sessionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
        } catch (error) {
            console.error('Erreur lors de la sauvegarde des données:', error);
        }
    }

    updateLapTable(lapData) {
        const row = document.createElement('tr');
        
        // Calculer l'écart avec le meilleur temps
        const gap = this.bestLapTime ? lapData.lap_time - this.bestLapTime : 0;
        const gapClass = gap > 0 ? 'time-positive' : 'time-negative';
        
        row.innerHTML = `
            <td>${lapData.lap_number}</td>
            <td>${this.formatTime(lapData.lap_time)}</td>
            <td class="${gapClass}">${gap !== 0 ? this.formatTime(Math.abs(gap)) : '-'}</td>
            <td>${this.formatTime(lapData.sector1_time)}</td>
            <td>${this.formatTime(lapData.sector2_time)}</td>
            <td>${this.formatTime(lapData.sector3_time)}</td>
            <td>${Math.round(lapData.max_speed)} km/h</td>
        `;

        this.lapTableBody.insertBefore(row, this.lapTableBody.firstChild);
    }

    formatTime(ms) {
        const minutes = Math.floor(ms / 60000);
        const seconds = Math.floor((ms % 60000) / 1000);
        const milliseconds = ms % 1000;
        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}.${String(milliseconds).padStart(3, '0')}`;
    }

    // Méthode pour exporter les données
    async exportData(startTime, endTime) {
        try {
            const response = await fetch(`api/telemetry.php?action=export&session_id=${this.sessionId}&start=${startTime}&end=${endTime}`);
            const result = await response.json();
            
            if (result.success) {
                window.location.href = `exports/${result.filename}`;
            } else {
                alert('Erreur lors de l\'export des données');
            }
        } catch (error) {
            console.error('Erreur lors de l\'export:', error);
            alert('Erreur lors de l\'export des données');
        }
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    const sessionId = new URLSearchParams(window.location.search).get('id');
    if (sessionId) {
        window.telemetryManager = new TelemetryManager(sessionId);
    } else {
        console.error('ID de session non trouvé');
    }
}); 