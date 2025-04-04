<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Erreur serveur</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/racing.css">
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
            padding: 0 20px;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: #e30613;
            margin: 0;
            line-height: 1;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .error-title {
            font-size: 32px;
            margin: 10px 0 30px;
            color: #333;
        }
        
        .error-message {
            font-size: 18px;
            margin-bottom: 30px;
            color: #666;
            max-width: 600px;
        }
        
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            color: #e30613;
        }
        
        .home-button {
            display: inline-block;
            background-color: #0066cc;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .home-button:hover {
            background-color: #004c99;
        }
        
        .racing-stripe {
            height: 8px;
            width: 100%;
            background: linear-gradient(to right, #e30613 0%, #e30613 33%, #ffffff 33%, #ffffff 66%, #0066cc 66%, #0066cc 100%);
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="racing-stripe"></div>
        <div class="error-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h1 class="error-code">500</h1>
        <h2 class="error-title">Erreur serveur</h2>
        <p class="error-message">
            Une erreur inattendue s'est produite sur le serveur.
            Nos équipes techniques ont été informées et travaillent à résoudre le problème.
            Veuillez réessayer ultérieurement.
        </p>
        <a href="<?= BASE_URL ?>/" class="home-button">
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
        <div class="racing-stripe" style="margin-top: 40px;"></div>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
