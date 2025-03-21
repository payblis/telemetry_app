// Fonction pour afficher les messages de notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}-message`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Supprimer la notification après 3 secondes
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Fonction pour confirmer une action
function confirmAction(message) {
    return window.confirm(message);
}

// Fonction pour formater une date
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: '2-digit', 
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('fr-FR', options);
}

// Fonction pour valider un formulaire
function validateForm(formElement) {
    const requiredFields = formElement.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Gestionnaire d'événements pour les formulaires
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!validateForm(form)) {
                e.preventDefault();
                showNotification('Veuillez remplir tous les champs requis', 'error');
            }
        });
    });
    
    // Gestionnaire pour les boutons de suppression
    const deleteButtons = document.querySelectorAll('.delete-button');
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!confirmAction('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
});

// Fonction pour mettre à jour dynamiquement les statistiques
function updateStats() {
    const statsElements = document.querySelectorAll('.stats-value');
    
    statsElements.forEach(element => {
        const url = element.dataset.url;
        if (url) {
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    element.textContent = data.value;
                })
                .catch(error => {
                    console.error('Erreur lors de la mise à jour des statistiques:', error);
                });
        }
    });
}

// Fonction pour gérer les requêtes AJAX
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Erreur lors de la requête:', error);
        showNotification('Une erreur est survenue', 'error');
        throw error;
    }
}

// Fonction pour gérer le chargement des données télémétriques
function loadTelemetryData(sessionId) {
    fetchData(`/api/telemetry.php?session_id=${sessionId}`)
        .then(data => {
            // Traitement des données télémétriques
            console.log('Données télémétriques chargées:', data);
            // Ici, vous pouvez ajouter le code pour afficher les graphiques
        })
        .catch(error => {
            console.error('Erreur lors du chargement des données télémétriques:', error);
        });
}

// Export des fonctions pour utilisation dans d'autres fichiers
window.app = {
    showNotification,
    confirmAction,
    formatDate,
    validateForm,
    updateStats,
    fetchData,
    loadTelemetryData
}; 