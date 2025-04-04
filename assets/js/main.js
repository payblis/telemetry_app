// Fonction pour initialiser les tooltips Bootstrap
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Fonction pour initialiser les popovers Bootstrap
function initPopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Fonction pour gérer les messages flash
function handleFlashMessages() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

// Fonction pour confirmer les actions importantes
function confirmAction(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir effectuer cette action ?');
}

// Fonction pour charger les données de télémétrie
async function loadTelemetryData(sessionId) {
    try {
        const response = await fetch(`/api/sessions/${sessionId}/telemetry`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur lors du chargement des données de télémétrie:', error);
        return null;
    }
}

// Fonction pour formater les temps
function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = (seconds % 60).toFixed(3);
    return `${minutes}:${remainingSeconds.padStart(6, '0')}`;
}

// Fonction pour formater les vitesses
function formatSpeed(kmh) {
    return `${kmh.toFixed(1)} km/h`;
}

// Fonction pour formater les angles
function formatAngle(degrees) {
    return `${degrees.toFixed(1)}°`;
}

// Fonction pour initialiser les graphiques
function initCharts() {
    const chartElements = document.querySelectorAll('.telemetry-chart');
    chartElements.forEach(element => {
        const ctx = element.getContext('2d');
        const chartType = element.dataset.chartType || 'line';
        const data = JSON.parse(element.dataset.chartData);
        
        new Chart(ctx, {
            type: chartType,
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    });
}

// Fonction pour gérer les formulaires dynamiques
function handleDynamicForms() {
    document.querySelectorAll('[data-dynamic-form]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const action = form.getAttribute('action');
            const method = form.getAttribute('method') || 'POST';
            
            try {
                const response = await fetch(action, {
                    method: method,
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        // Rafraîchir la page ou mettre à jour le contenu dynamiquement
                        location.reload();
                    }
                } else {
                    // Afficher les erreurs
                    console.error('Erreur:', result.message);
                }
            } catch (error) {
                console.error('Erreur lors de la soumission du formulaire:', error);
            }
        });
    });
}

// Fonction pour gérer les filtres de tableau
function handleTableFilters() {
    document.querySelectorAll('.table-filter').forEach(filter => {
        filter.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const table = document.querySelector(filter.dataset.target);
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    initTooltips();
    initPopovers();
    handleFlashMessages();
    initCharts();
    handleDynamicForms();
    handleTableFilters();
    
    // Initialisation des sélecteurs de date
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            locale: 'fr'
        });
    }
    
    // Initialisation des sélecteurs de temps
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.timepicker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true
        });
    }
}); 