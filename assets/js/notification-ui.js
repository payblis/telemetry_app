class NotificationUI {
    constructor() {
        this.notificationContainer = document.getElementById('notification-container');
        this.notificationList = document.getElementById('notification-list');
        this.unreadCount = document.getElementById('unread-count');
        this.notificationToggle = document.getElementById('notification-toggle');
        this.markAllReadBtn = document.getElementById('mark-all-read');
        this.clearAllBtn = document.getElementById('clear-all');
        
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        if (this.notificationToggle) {
            this.notificationToggle.addEventListener('click', () => this.toggleNotificationPanel());
        }

        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', () => this.markAllAsRead());
        }

        if (this.clearAllBtn) {
            this.clearAllBtn.addEventListener('click', () => this.clearAllNotifications());
        }

        // Fermeture du panneau lors d'un clic en dehors
        document.addEventListener('click', (e) => {
            if (this.notificationContainer && 
                !this.notificationContainer.contains(e.target) && 
                !e.target.closest('#notification-toggle')) {
                this.hideNotificationPanel();
            }
        });
    }

    toggleNotificationPanel() {
        if (this.notificationContainer.classList.contains('show')) {
            this.hideNotificationPanel();
        } else {
            this.showNotificationPanel();
        }
    }

    showNotificationPanel() {
        if (this.notificationContainer) {
            this.notificationContainer.classList.add('show');
            this.loadNotifications();
        }
    }

    hideNotificationPanel() {
        if (this.notificationContainer) {
            this.notificationContainer.classList.remove('show');
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch('/api/notifications/list.php');
            const data = await response.json();
            
            if (data.success) {
                this.renderNotifications(data.notifications);
            } else {
                console.error('Erreur lors du chargement des notifications:', data.error);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des notifications:', error);
        }
    }

    renderNotifications(notifications) {
        if (!this.notificationList) return;

        this.notificationList.innerHTML = '';
        
        if (notifications.length === 0) {
            this.notificationList.innerHTML = '<div class="no-notifications">Aucune notification</div>';
            return;
        }

        notifications.forEach(notification => {
            const notificationElement = this.createNotificationElement(notification);
            this.notificationList.appendChild(notificationElement);
        });
    }

    createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `notification-item ${notification.read ? 'read' : 'unread'}`;
        div.dataset.id = notification.id;

        const timestamp = new Date(notification.created_at).toLocaleString();
        
        div.innerHTML = `
            <div class="notification-content">
                <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                <div class="notification-meta">
                    <span class="notification-time">${timestamp}</span>
                    ${!notification.read ? '<span class="unread-badge">Nouveau</span>' : ''}
                </div>
            </div>
            <div class="notification-actions">
                ${!notification.read ? 
                    `<button class="mark-read-btn" title="Marquer comme lu">
                        <i class="fas fa-check"></i>
                    </button>` : 
                    ''
                }
                <button class="delete-btn" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        // Ajout des gestionnaires d'événements
        const markReadBtn = div.querySelector('.mark-read-btn');
        if (markReadBtn) {
            markReadBtn.addEventListener('click', () => this.markAsRead(notification.id));
        }

        const deleteBtn = div.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.deleteNotification(notification.id));
        }

        return div;
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch('/api/notifications/mark-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: notificationId })
            });

            const data = await response.json();
            
            if (data.success) {
                const notificationElement = this.notificationList.querySelector(`[data-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                    notificationElement.classList.add('read');
                    const unreadBadge = notificationElement.querySelector('.unread-badge');
                    if (unreadBadge) {
                        unreadBadge.remove();
                    }
                    const markReadBtn = notificationElement.querySelector('.mark-read-btn');
                    if (markReadBtn) {
                        markReadBtn.remove();
                    }
                }
                this.updateUnreadCount();
            } else {
                console.error('Erreur lors du marquage de la notification comme lue:', data.error);
            }
        } catch (error) {
            console.error('Erreur lors du marquage de la notification comme lue:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read.php', {
                method: 'POST'
            });

            const data = await response.json();
            
            if (data.success) {
                const unreadNotifications = this.notificationList.querySelectorAll('.notification-item.unread');
                unreadNotifications.forEach(notification => {
                    notification.classList.remove('unread');
                    notification.classList.add('read');
                    const unreadBadge = notification.querySelector('.unread-badge');
                    if (unreadBadge) {
                        unreadBadge.remove();
                    }
                    const markReadBtn = notification.querySelector('.mark-read-btn');
                    if (markReadBtn) {
                        markReadBtn.remove();
                    }
                });
                this.updateUnreadCount();
            } else {
                console.error('Erreur lors du marquage de toutes les notifications comme lues:', data.error);
            }
        } catch (error) {
            console.error('Erreur lors du marquage de toutes les notifications comme lues:', error);
        }
    }

    async deleteNotification(notificationId) {
        try {
            const response = await fetch('/api/notifications/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: notificationId })
            });

            const data = await response.json();
            
            if (data.success) {
                const notificationElement = this.notificationList.querySelector(`[data-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.remove();
                }
                this.updateUnreadCount();
            } else {
                console.error('Erreur lors de la suppression de la notification:', data.error);
            }
        } catch (error) {
            console.error('Erreur lors de la suppression de la notification:', error);
        }
    }

    async clearAllNotifications() {
        try {
            const response = await fetch('/api/notifications/delete-all.php', {
                method: 'POST'
            });

            const data = await response.json();
            
            if (data.success) {
                this.notificationList.innerHTML = '<div class="no-notifications">Aucune notification</div>';
                this.updateUnreadCount();
            } else {
                console.error('Erreur lors de la suppression de toutes les notifications:', data.error);
            }
        } catch (error) {
            console.error('Erreur lors de la suppression de toutes les notifications:', error);
        }
    }

    async updateUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count.php');
            const data = await response.json();
            
            if (data.success) {
                if (this.unreadCount) {
                    this.unreadCount.textContent = data.count;
                    this.unreadCount.style.display = data.count > 0 ? 'block' : 'none';
                }
            } else {
                console.error('Erreur lors de la mise à jour du compteur:', data.error);
            }
        } catch (error) {
            console.error('Erreur lors de la mise à jour du compteur:', error);
        }
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

// Initialisation de l'interface utilisateur des notifications
document.addEventListener('DOMContentLoaded', () => {
    const notificationUI = new NotificationUI();
}); 