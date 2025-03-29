<?php
require_once '../../includes/init.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Récupération des notifications
$notifications = $notification->getNotifications($_SESSION['user_id'], $per_page, $offset);
$total_notifications = $notification->getTotalNotifications($_SESSION['user_id']);
$total_pages = ceil($total_notifications / $per_page);

// Marquer toutes les notifications comme lues
if (isset($_POST['mark_all_read'])) {
    try {
        $notification->markAllAsRead($_SESSION['user_id']);
        header('Location: /notifications/all.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toutes les notifications - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="container">
        <div class="notifications-page">
            <div class="page-header">
                <h1>Toutes les notifications</h1>
                <div class="header-actions">
                    <form method="POST" class="inline-form">
                        <button type="submit" name="mark_all_read" class="btn btn-secondary">
                            <i class="fas fa-check-double"></i> Tout marquer comme lu
                        </button>
                    </form>
                    <a href="/notifications/settings.php" class="btn btn-primary">
                        <i class="fas fa-cog"></i> Paramètres
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($notifications)): ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>Aucune notification</p>
                </div>
            <?php else: ?>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item <?php echo $notif['read'] ? 'read' : 'unread'; ?>" data-id="<?php echo $notif['id']; ?>">
                            <div class="notification-icon">
                                <i class="<?php echo $notification->getIconClass($notif['type']); ?>"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-header">
                                    <h4><?php echo htmlspecialchars($notif['title']); ?></h4>
                                    <span class="notification-time"><?php echo $notification->formatTime($notif['created_at']); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                <?php if ($notif['url']): ?>
                                    <a href="<?php echo htmlspecialchars($notif['url']); ?>" class="notification-link">Voir plus</a>
                                <?php endif; ?>
                            </div>
                            <?php if (!$notif['read']): ?>
                                <button class="mark-read" title="Marquer comme lu">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        <?php endif; ?>

                        <div class="page-numbers">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i === $page): ?>
                                    <span class="current-page"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <script>
        // Gestion du marquage des notifications comme lues
        document.querySelectorAll('.mark-read').forEach(button => {
            button.addEventListener('click', async function() {
                const notificationItem = this.closest('.notification-item');
                const notificationId = notificationItem.dataset.id;

                try {
                    const response = await fetch(`/api/notifications/${notificationId}/read`, {
                        method: 'POST'
                    });

                    if (response.ok) {
                        notificationItem.classList.remove('unread');
                        notificationItem.classList.add('read');
                        this.remove();
                    } else {
                        throw new Error('Erreur lors du marquage de la notification comme lue');
                    }
                } catch (error) {
                    console.error(error);
                    alert('Impossible de marquer la notification comme lue');
                }
            });
        });
    </script>
</body>
</html> 