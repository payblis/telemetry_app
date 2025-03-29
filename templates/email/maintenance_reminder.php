<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subject); ?></title>
    <style>
        /* Styles de base */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }

        /* Conteneur principal */
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }

        /* En-tête */
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
        }

        .header img {
            max-width: 200px;
            height: auto;
        }

        /* Contenu */
        .content {
            padding: 30px 20px;
        }

        .content h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .content h2 {
            color: #34495e;
            font-size: 20px;
            margin: 25px 0 15px;
        }

        .content p {
            margin-bottom: 15px;
        }

        /* Alerte */
        .alert {
            margin: 20px 0;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid;
        }

        .alert.high {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .alert.medium {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .alert.low {
            background-color: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }

        /* Tâches de maintenance */
        .maintenance-tasks {
            margin: 20px 0;
        }

        .task-item {
            margin-bottom: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid;
        }

        .task-item.high {
            border-color: #dc3545;
        }

        .task-item.medium {
            border-color: #ffc107;
        }

        .task-item.low {
            border-color: #17a2b8;
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .task-title {
            font-weight: bold;
            color: #2c3e50;
        }

        .task-priority {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .task-priority.high {
            background-color: #dc3545;
            color: #ffffff;
        }

        .task-priority.medium {
            background-color: #ffc107;
            color: #000000;
        }

        .task-priority.low {
            background-color: #17a2b8;
            color: #ffffff;
        }

        .task-details {
            margin-top: 10px;
            font-size: 14px;
        }

        .task-stat {
            display: inline-block;
            margin-right: 15px;
            color: #666666;
        }

        /* Statistiques */
        .stats {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .stat-item {
            margin-bottom: 10px;
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-label {
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-value {
            float: right;
            color: #3498db;
        }

        /* Bouton d'action */
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }

        .button:hover {
            background-color: #2980b9;
        }

        /* Pied de page */
        .footer {
            text-align: center;
            padding: 20px;
            border-top: 2px solid #f0f0f0;
            font-size: 12px;
            color: #666666;
        }

        .footer p {
            margin: 5px 0;
        }

        /* Responsive */
        @media screen and (max-width: 600px) {
            .container {
                width: 100%;
                padding: 10px;
            }

            .content {
                padding: 20px 10px;
            }

            .header img {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" />
        </div>

        <div class="content">
            <h1><?php echo htmlspecialchars($subject); ?></h1>
            
            <p>Bonjour <?php echo htmlspecialchars($data['user']['name']); ?>,</p>
            
            <div class="alert <?php echo $data['maintenance']['priority']; ?>">
                <p><strong>Rappel de maintenance pour votre <?php echo htmlspecialchars($data['bike']['name']); ?></strong></p>
                <p><?php echo htmlspecialchars($data['maintenance']['message']); ?></p>
            </div>

            <div class="stats">
                <h2>État actuel</h2>
                
                <div class="stat-item">
                    <span class="stat-label">Kilométrage actuel</span>
                    <span class="stat-value"><?php echo number_format($data['bike']['current_mileage'], 0, ',', ' '); ?> km</span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Heures de fonctionnement</span>
                    <span class="stat-value"><?php echo number_format($data['bike']['engine_hours'], 1, ',', ' '); ?> h</span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Dernière maintenance</span>
                    <span class="stat-value"><?php echo date('d/m/Y', strtotime($data['bike']['last_maintenance'])); ?></span>
                </div>
            </div>

            <div class="maintenance-tasks">
                <h2>Tâches de maintenance recommandées</h2>
                
                <?php foreach ($data['maintenance']['tasks'] as $task): ?>
                <div class="task-item <?php echo $task['priority']; ?>">
                    <div class="task-header">
                        <span class="task-title"><?php echo htmlspecialchars($task['title']); ?></span>
                        <span class="task-priority <?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                    </div>
                    <p><?php echo htmlspecialchars($task['description']); ?></p>
                    <div class="task-details">
                        <span class="task-stat">
                            <strong>Intervalle:</strong> <?php echo number_format($task['interval'], 0, ',', ' '); ?> km
                        </span>
                        <span class="task-stat">
                            <strong>Dernière fois:</strong> <?php echo date('d/m/Y', strtotime($task['last_done'])); ?>
                        </span>
                        <?php if (isset($task['estimated_cost'])): ?>
                        <span class="task-stat">
                            <strong>Coût estimé:</strong> <?php echo number_format($task['estimated_cost'], 2, ',', ' '); ?> €
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <a href="<?php echo SITE_URL; ?>/maintenance/schedule.php" class="button">
                Planifier la maintenance
            </a>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé par <?php echo SITE_NAME; ?></p>
            <p>Si vous ne souhaitez plus recevoir ces notifications, vous pouvez <a href="<?php echo SITE_URL; ?>/notifications/settings.php">modifier vos préférences</a>.</p>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html> 