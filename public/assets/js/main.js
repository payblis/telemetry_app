/**
 * JavaScript principal pour l'application
 * Télémétrie Moto SaaS
 */

document.addEventListener('DOMContentLoaded', function() {
    // Gestion des notifications
    initNotifications();
    
    // Gestion des menus déroulants
    initDropdowns();
    
    // Validation des formulaires
    initFormValidation();
});

/**
 * Initialiser les notifications
 */
function initNotifications() {
    // Faire disparaître les notifications après 5 secondes
    const notifications = document.querySelectorAll('.notification');
    
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 300);
        }, 5000);
    });
}

/**
 * Initialiser les menus déroulants
 */
function initDropdowns() {
    // Gestion des menus déroulants sur mobile
    const dropdownBtns = document.querySelectorAll('.user-dropdown-btn');
    
    dropdownBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const content = this.nextElementSibling;
            
            // Fermer tous les autres menus déroulants
            document.querySelectorAll('.user-dropdown-content').forEach(dropdown => {
                if (dropdown !== content) {
                    dropdown.classList.remove('show');
                }
            });
            
            // Basculer l'affichage du menu actuel
            content.classList.toggle('show');
        });
    });
    
    // Fermer les menus déroulants lorsqu'on clique ailleurs
    document.addEventListener('click', function(event) {
        if (!event.target.matches('.user-dropdown-btn') && !event.target.closest('.user-dropdown-btn')) {
            document.querySelectorAll('.user-dropdown-content').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
}

/**
 * Initialiser la validation des formulaires
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validation des champs requis
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Créer un message d'erreur si inexistant
                    let errorMsg = field.parentNode.querySelector('.form-error');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'form-error';
                        errorMsg.textContent = 'Ce champ est obligatoire';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.classList.remove('error');
                    const errorMsg = field.parentNode.querySelector('.form-error');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            // Validation des emails
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value.trim() && !isValidEmail(field.value)) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Créer un message d'erreur si inexistant
                    let errorMsg = field.parentNode.querySelector('.form-error');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'form-error';
                        errorMsg.textContent = 'Adresse email invalide';
                        field.parentNode.appendChild(errorMsg);
                    } else {
                        errorMsg.textContent = 'Adresse email invalide';
                    }
                }
            });
            
            // Validation des mots de passe
            const passwordFields = form.querySelectorAll('input[type="password"]');
            if (passwordFields.length >= 2) {
                const password = passwordFields[0].value;
                const confirmPassword = passwordFields[1].value;
                
                if (password && confirmPassword && password !== confirmPassword) {
                    isValid = false;
                    passwordFields[1].classList.add('error');
                    
                    // Créer un message d'erreur si inexistant
                    let errorMsg = passwordFields[1].parentNode.querySelector('.form-error');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'form-error';
                        errorMsg.textContent = 'Les mots de passe ne correspondent pas';
                        passwordFields[1].parentNode.appendChild(errorMsg);
                    } else {
                        errorMsg.textContent = 'Les mots de passe ne correspondent pas';
                    }
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
}

/**
 * Valider une adresse email
 * 
 * @param {string} email Adresse email à valider
 * @return {boolean} L'adresse est valide ou non
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}
