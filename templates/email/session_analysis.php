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

        /* Performances */
        .performance {
            margin: 20px 0;
        }

        .performance-item {
            margin-bottom: 15px;
        }

        .performance-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        .progress-bar {
            background-color: #e9ecef;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-value {
            height: 100%;
            background-color: #3498db;
            transition: width 0.3s ease;
        }

        /* Recommandations */
        .recommendations {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .recommendation-item {
            margin-bottom: 15px;
            padding: 10px;
            border-left: 4px solid #3498db;
            background-color: #ffffff;
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
            
            <p>Voici l'analyse de votre session du <?php echo date('d/m/Y', strtotime($data['session']['date'])); ?> sur le circuit <?php echo htmlspecialchars($data['session']['track']); ?>.</p>

            <div class="stats">
                <h2>Statistiques de la session</h2>
                
                <div class="stat-item">
                    <span class="stat-label">Durée totale</span>
                    <span class="stat-value"><?php echo $data['session']['duration']; ?></span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Nombre de tours</span>
                    <span class="stat-value"><?php echo $data['session']['laps']; ?></span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Meilleur temps</span>
                    <span class="stat-value"><?php echo $data['session']['best_lap_time']; ?></span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Vitesse maximale</span>
                    <span class="stat-value"><?php echo $data['session']['max_speed']; ?> km/h</span>
                </div>
            </div>

            <div class="performance">
                <h2>Scores de performance</h2>
                
                <div class="performance-item">
                    <span class="performance-label">Score de pilotage (<?php echo $data['analysis']['riding_score']; ?>%)</span>
                    <div class="progress-bar">
                        <div class="progress-value" style="width: <?php echo $data['analysis']['riding_score']; ?>%;"></div>
                    </div>
                </div>
                
                <div class="performance-item">
                    <span class="performance-label">Score de régularité (<?php echo $data['analysis']['consistency_score']; ?>%)</span>
                    <div class="progress-bar">
                        <div class="progress-value" style="width: <?php echo $data['analysis']['consistency_score']; ?>%;"></div>
                    </div>
                </div>
                
                <div class="performance-item">
                    <span class="performance-label">Score de trajectoire (<?php echo $data['analysis']['line_score']; ?>%)</span>
                    <div class="progress-bar">
                        <div class="progress-value" style="width: <?php echo $data['analysis']['line_score']; ?>%;"></div>
                    </div>
                </div>
            </div>

            <div class="recommendations">
                <h2>Recommandations</h2>
                
                <?php foreach ($data['analysis']['recommendations'] as $recommendation): ?>
                <div class="recommendation-item">
                    <p><?php echo htmlspecialchars($recommendation); ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <a href="<?php echo SITE_URL; ?>/stats/analysis.php?session=<?php echo $data['session']['id']; ?>" class="button">
                Voir l'analyse détaillée
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