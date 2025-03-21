<?php require_once APP_PATH . 'views/templates/header.php'; ?>

<div class="container">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Liste des motos</h2>
            <a href="index.php?route=moto/create" class="btn btn-success">Ajouter une moto</a>
        </div>
        <div class="panel-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="moto-grid">
                <?php foreach ($motos as $moto): ?>
                    <div class="moto-card">
                        <div class="moto-header">
                            <h3><?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele']); ?></h3>
                            <span class="moto-year"><?php echo htmlspecialchars($moto['annee']); ?></span>
                        </div>
                        <div class="moto-content">
                            <div class="moto-info">
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($moto['type_moto']); ?></p>
                                <p><strong>Cylindrée:</strong> <?php echo htmlspecialchars($moto['cylindree']); ?> cc</p>
                                <p><strong>Puissance:</strong> <?php echo htmlspecialchars($moto['puissance_moteur']); ?> ch</p>
                            </div>
                            <div class="moto-stats">
                                <p><strong>Sessions:</strong> <?php echo htmlspecialchars($moto['total_sessions'] ?? 0); ?></p>
                                <p><strong>Pilotes:</strong> <?php echo htmlspecialchars($moto['total_pilotes'] ?? 0); ?></p>
                            </div>
                        </div>
                        <div class="moto-actions">
                            <a href="index.php?route=moto/view&id=<?php echo $moto['id']; ?>" class="btn btn-primary">Détails</a>
                            <a href="index.php?route=moto/edit&id=<?php echo $moto['id']; ?>" class="btn btn-primary">Modifier</a>
                            <a href="index.php?route=moto/delete&id=<?php echo $moto['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette moto ?');">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.moto-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px 0;
}

.moto-card {
    background: var(--white);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.moto-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.moto-header {
    background: var(--primary-color);
    color: var(--white);
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.moto-header h3 {
    margin: 0;
    font-size: 18px;
}

.moto-year {
    background: var(--accent-color);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 14px;
}

.moto-content {
    padding: 15px;
}

.moto-info p {
    margin: 5px 0;
    color: var(--dark-gray);
}

.moto-stats {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--light-gray);
}

.moto-actions {
    padding: 15px;
    background: #f8f9fa;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-danger {
    background-color: var(--error-color);
    color: var(--white);
}

.btn-danger:hover {
    background-color: #c0392b;
}
</style>

<?php require_once APP_PATH . 'views/templates/footer.php'; ?> 