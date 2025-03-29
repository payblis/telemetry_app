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

        .content p {
            margin-bottom: 15px;
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

        .social-links {
            margin: 15px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #3498db;
            text-decoration: none;
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
            
            <?php echo $message; ?>

            <?php if (isset($data['action_url']) && isset($data['action_text'])): ?>
            <a href="<?php echo htmlspecialchars($data['action_url']); ?>" class="button">
                <?php echo htmlspecialchars($data['action_text']); ?>
            </a>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé par <?php echo SITE_NAME; ?></p>
            <p>Si vous ne souhaitez plus recevoir ces notifications, vous pouvez <a href="<?php echo SITE_URL; ?>/notifications/settings.php">modifier vos préférences</a>.</p>
            
            <div class="social-links">
                <?php if (defined('SOCIAL_FACEBOOK')): ?>
                <a href="<?php echo SOCIAL_FACEBOOK; ?>">Facebook</a>
                <?php endif; ?>
                
                <?php if (defined('SOCIAL_TWITTER')): ?>
                <a href="<?php echo SOCIAL_TWITTER; ?>">Twitter</a>
                <?php endif; ?>
                
                <?php if (defined('SOCIAL_INSTAGRAM')): ?>
                <a href="<?php echo SOCIAL_INSTAGRAM; ?>">Instagram</a>
                <?php endif; ?>
            </div>

            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html> 