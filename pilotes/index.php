<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

checkAuth();

// Récupération des pilotes de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM pilotes WHERE user_id = ? ORDER BY nom, prenom");
$stmt->execute([$_SESSION['user_id']]);
$pilotes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Pilotes - Télémétrie Moto</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Gestion des Pilotes</h1>
        
        <div class="actions">
            <a href="ajouter.php" class="btn-primary">Ajouter un pilote</a>
        </div>

        <?php if (count($pilotes) > 0): ?>
            <div class="pilotes-list">
                <?php foreach ($pilotes as $pilote): ?>
                    <div class="pilote-card">
                        <h3><?php echo htmlspecialchars($pilote['nom'] . ' ' . $pilote['prenom']); ?></h3>
                        <p class="pseudo"><?php echo htmlspecialchars($pilote['pseudo']); ?></p>
                        <div class="details">
                            <p><strong>Niveau :</strong> <?php echo htmlspecialchars($pilote['niveau']); ?></p>
                            <p><strong>Expérience :</strong> <?php echo htmlspecialchars($pilote['experience_annees']); ?> années</p>
                            <p><strong>Style de pilotage :</strong> <?php echo htmlspecialchars($pilote['style_pilotage']); ?></p>
                        </div>
                        <div class="actions">
                            <a href="modifier.php?id=<?php echo $pilote['id']; ?>" class="btn-secondary">Modifier</a>
                            <a href="supprimer.php?id=<?php echo $pilote['id']; ?>" class="btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce pilote ?');">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-data">Aucun pilote enregistré. Commencez par en ajouter un !</p>
        <?php endif; ?>

        <p class="back-link">
            <a href="../index.php">Retour à l'accueil</a>
        </p>
    </div>

    <style>
        .actions {
            margin: 2rem 0;
            text-align: right;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            text-decoration: none;
        }

        .pilotes-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .pilote-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .pilote-card h3 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }

        .pilote-card .pseudo {
            color: #666;
            font-style: italic;
            margin-bottom: 1rem;
        }

        .pilote-card .details {
            margin-bottom: 1.5rem;
        }

        .pilote-card .details p {
            margin: 0.5rem 0;
            text-align: left;
        }

        .pilote-card .actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-secondary, .btn-danger {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            flex: 1;
            text-align: center;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-secondary:hover, .btn-danger:hover {
            opacity: 0.9;
            text-decoration: none;
        }

        .no-data {
            text-align: center;
            color: #666;
            margin: 2rem 0;
        }

        .back-link {
            margin-top: 2rem;
            text-align: center;
        }
    </style>
</body>
</html> 