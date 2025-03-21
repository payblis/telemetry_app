<div class="row">
    <div class="col-md-12 text-center mb-5">
        <h1>Bienvenue sur Télémétrie IA</h1>
        <p class="lead">La solution intelligente pour l'analyse télémétrique en compétition moto</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-robot me-2"></i>Assistant IA</h5>
                <p class="card-text">Bénéficiez d'une double intelligence artificielle (ChatGPT + IA spécialisée) pour des analyses précises et personnalisées.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Analyse en Temps Réel</h5>
                <p class="card-text">Visualisez et analysez vos données télémétrique en temps réel pour des ajustements immédiats.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-sliders me-2"></i>Réglages Personnalisés</h5>
                <p class="card-text">Obtenez des recommandations de réglages adaptées à votre pilote, votre moto et le circuit.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <h3>Dernières Sessions</h3>
        <div class="list-group">
            <?php if(isset($lastSessions) && !empty($lastSessions)): ?>
                <?php foreach($lastSessions as $session): ?>
                    <a href="/session/<?php echo $session['id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo $session['circuit_name']; ?></h5>
                            <small><?php echo $session['date']; ?></small>
                        </div>
                        <p class="mb-1">Pilote: <?php echo $session['pilot_name']; ?></p>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Aucune session récente à afficher
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6">
        <h3>Statistiques Globales</h3>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center">
                        <h4><?php echo isset($totalSessions) ? $totalSessions : 0; ?></h4>
                        <p>Sessions Total</p>
                    </div>
                    <div class="col-6 text-center">
                        <h4><?php echo isset($totalPilots) ? $totalPilots : 0; ?></h4>
                        <p>Pilotes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 