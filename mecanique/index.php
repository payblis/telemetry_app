<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth_functions.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion à la base de données
$conn = getDBConnection();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="mecanique-container">
    <h1 class="mecanique-title">Assistance Mécanique</h1>
    
    <div class="mecanique-intro">
        <p>Bienvenue dans la section d'assistance mécanique. Cette section vous aide à comprendre comment appliquer les réglages recommandés sur votre moto en fonction de votre équipement spécifique.</p>
    </div>
    
    <div class="mecanique-tabs">
        <button class="tab-button active" data-tab="suspension">Suspension</button>
        <button class="tab-button" data-tab="chassis">Châssis</button>
        <button class="tab-button" data-tab="moteur">Moteur/Transmission</button>
        <button class="tab-button" data-tab="pneus">Pneumatiques</button>
        <button class="tab-button" data-tab="electronique">Électronique</button>
    </div>
    
    <div class="tab-content active" id="suspension">
        <h2>Réglages de Suspension</h2>
        
        <div class="mecanique-section">
            <h3>Précharge</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/precharge.jpg'); ?>" alt="Réglage de précharge">
                </div>
                <div class="mecanique-text">
                    <p>La précharge est le réglage de base de votre suspension. Elle détermine la position statique de votre moto et influence directement la géométrie et le comportement dynamique.</p>
                    
                    <h4>Comment régler la précharge avant</h4>
                    <ol>
                        <li>Localisez les bouchons de réglage en haut des tubes de fourche</li>
                        <li>Utilisez une clé adaptée pour tourner dans le sens horaire (plus dur) ou anti-horaire (plus souple)</li>
                        <li>Comptez le nombre de tours ou de clics depuis la position d'origine</li>
                        <li>Assurez-vous que les deux tubes sont réglés de manière identique</li>
                    </ol>
                    
                    <h4>Comment régler la précharge arrière</h4>
                    <ol>
                        <li>Localisez la bague de réglage sur l'amortisseur arrière</li>
                        <li>Utilisez la clé spécifique fournie avec votre moto</li>
                        <li>Tournez dans le sens horaire pour augmenter la précharge (plus dur)</li>
                        <li>Tournez dans le sens anti-horaire pour diminuer la précharge (plus souple)</li>
                        <li>Mesurez la longueur du ressort pour garantir la précision du réglage</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <div class="mecanique-section">
            <h3>Compression</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/compression.jpg'); ?>" alt="Réglage de compression">
                </div>
                <div class="mecanique-text">
                    <p>Le réglage de compression contrôle la vitesse à laquelle la suspension se comprime lorsqu'elle rencontre un obstacle. Sur les suspensions haut de gamme, on distingue la compression haute vitesse et basse vitesse.</p>
                    
                    <h4>Compression basse vitesse</h4>
                    <p>Affecte le comportement de la moto lors des transferts de masse (freinage, accélération) et des mouvements lents de la suspension.</p>
                    <ul>
                        <li>Fourche : généralement réglable en haut des tubes ou sur le pied de fourche</li>
                        <li>Amortisseur : réglable via une molette sur le corps de l'amortisseur</li>
                    </ul>
                    
                    <h4>Compression haute vitesse</h4>
                    <p>Contrôle la réaction de la suspension face aux chocs violents et rapides (bosses, nids de poule).</p>
                    <ul>
                        <li>Généralement réglable uniquement sur les suspensions haut de gamme</li>
                        <li>Souvent nécessite un outil spécifique</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="mecanique-section">
            <h3>Détente</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/detente.jpg'); ?>" alt="Réglage de détente">
                </div>
                <div class="mecanique-text">
                    <p>La détente contrôle la vitesse à laquelle la suspension revient à sa position après avoir été comprimée. Un réglage trop rapide rend la moto nerveuse, trop lent la rend pataude.</p>
                    
                    <h4>Comment régler la détente avant</h4>
                    <ol>
                        <li>Localisez la vis de réglage en bas des tubes de fourche (généralement de couleur rouge)</li>
                        <li>Tournez dans le sens horaire pour ralentir la détente (plus dur)</li>
                        <li>Tournez dans le sens anti-horaire pour accélérer la détente (plus souple)</li>
                        <li>Comptez le nombre de clics depuis la position d'origine</li>
                    </ol>
                    
                    <h4>Comment régler la détente arrière</h4>
                    <ol>
                        <li>Localisez la vis de réglage sur l'amortisseur (souvent en bas)</li>
                        <li>Utilisez un tournevis plat pour ajuster</li>
                        <li>Suivez les mêmes principes que pour l'avant</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tab-content" id="chassis">
        <h2>Réglages du Châssis</h2>
        
        <div class="mecanique-section">
            <h3>Hauteur de fourche</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/hauteur_fourche.jpg'); ?>" alt="Réglage de hauteur de fourche">
                </div>
                <div class="mecanique-text">
                    <p>La hauteur de fourche modifie l'angle de chasse et l'empattement de la moto, influençant directement sa stabilité et sa maniabilité.</p>
                    
                    <h4>Comment régler la hauteur de fourche</h4>
                    <ol>
                        <li>Desserrez les vis de bridage des tés de fourche</li>
                        <li>Ajustez la hauteur des tubes dans les tés</li>
                        <li>Mesurez précisément la hauteur dépassant du té supérieur</li>
                        <li>Assurez-vous que les deux tubes sont à la même hauteur</li>
                        <li>Resserrez les vis au couple spécifié par le constructeur</li>
                    </ol>
                    
                    <p><strong>Attention :</strong> Ce réglage modifie significativement le comportement de la moto. Procédez par petits incréments (2-3mm) et testez entre chaque modification.</p>
                </div>
            </div>
        </div>
        
        <div class="mecanique-section">
            <h3>Position de l'axe de roue arrière</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/axe_roue.jpg'); ?>" alt="Réglage de l'axe de roue arrière">
                </div>
                <div class="mecanique-text">
                    <p>La position de l'axe de roue arrière permet d'ajuster l'empattement de la moto et la tension de chaîne.</p>
                    
                    <h4>Comment régler la position de l'axe</h4>
                    <ol>
                        <li>Desserrez l'écrou d'axe de roue arrière</li>
                        <li>Ajustez les tendeurs de chaîne de manière égale des deux côtés</li>
                        <li>Vérifiez l'alignement à l'aide des repères sur les bras oscillants</li>
                        <li>Contrôlez la tension de chaîne</li>
                        <li>Resserrez l'écrou d'axe au couple spécifié</li>
                    </ol>
                    
                    <p><strong>Conseil :</strong> Un empattement plus long améliore la stabilité en ligne droite, tandis qu'un empattement plus court rend la moto plus agile dans les virages.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tab-content" id="moteur">
        <h2>Réglages Moteur et Transmission</h2>
        
        <div class="mecanique-section">
            <h3>Rapport de transmission</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/transmission.jpg'); ?>" alt="Réglage du rapport de transmission">
                </div>
                <div class="mecanique-text">
                    <p>Le rapport de transmission (pignon avant/couronne arrière) influence directement l'accélération et la vitesse de pointe de votre moto.</p>
                    
                    <h4>Comment changer les pignons</h4>
                    <ol>
                        <li>Pignon avant :
                            <ul>
                                <li>Retirez le cache pignon</li>
                                <li>Desserrez l'écrou de fixation</li>
                                <li>Remplacez le pignon</li>
                                <li>Resserrez au couple spécifié</li>
                            </ul>
                        </li>
                        <li>Couronne arrière :
                            <ul>
                                <li>Retirez la roue arrière</li>
                                <li>Dévissez les écrous de fixation de la couronne</li>
                                <li>Remplacez la couronne</li>
                                <li>Resserrez au couple spécifié</li>
                            </ul>
                        </li>
                    </ol>
                    
                    <p><strong>Conseil :</strong> Augmenter le nombre de dents de la couronne arrière ou diminuer celles du pignon avant améliore l'accélération mais réduit la vitesse de pointe.</p>
                </div>
            </div>
        </div>
        
        <div class="mecanique-section">
            <h3>Mapping moteur</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/mapping.jpg'); ?>" alt="Réglage du mapping moteur">
                </div>
                <div class="mecanique-text">
                    <p>Le mapping moteur contrôle l'injection de carburant et l'allumage en fonction des conditions de fonctionnement.</p>
                    
                    <h4>Comment ajuster le mapping</h4>
                    <ol>
                        <li>Motos avec modes préréglés :
                            <ul>
                                <li>Utilisez les boutons de sélection de mode sur le tableau de bord</li>
                                <li>Choisissez entre les modes (souvent : Sport, Route, Pluie)</li>
                            </ul>
                        </li>
                        <li>Motos avec boîtier additionnel :
                            <ul>
                                <li>Connectez le boîtier à l'ordinateur</li>
                                <li>Utilisez le logiciel fourni pour ajuster les paramètres</li>
                                <li>Téléchargez la nouvelle cartographie dans le boîtier</li>
                            </ul>
                        </li>
                    </ol>
                    
                    <p><strong>Attention :</strong> Des modifications trop importantes peuvent endommager le moteur. Consultez un spécialiste pour les modifications avancées.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tab-content" id="pneus">
        <h2>Réglages des Pneumatiques</h2>
        
        <div class="mecanique-section">
            <h3>Pression des pneus</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/pression_pneus.jpg'); ?>" alt="Réglage de la pression des pneus">
                </div>
                <div class="mecanique-text">
                    <p>La pression des pneus est un réglage crucial qui affecte l'adhérence, l'usure et le comportement de la moto.</p>
                    
                    <h4>Comment vérifier et ajuster la pression</h4>
                    <ol>
                        <li>Utilisez un manomètre de qualité</li>
                        <li>Vérifiez la pression à froid (pneus non chauffés par la conduite)</li>
                        <li>Comparez avec les valeurs recommandées par le fabricant</li>
                        <li>Ajustez si nécessaire</li>
                        <li>Pour la piste, réduisez légèrement la pression par rapport aux valeurs route</li>
                    </ol>
                    
                    <p><strong>Conseil :</strong> Sur circuit, mesurez la température des pneus après quelques tours pour affiner les réglages de pression.</p>
                </div>
            </div>
        </div>
        
        <div class="mecanique-section">
            <h3>Choix des pneus</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/choix_pneus.jpg'); ?>" alt="Choix des pneus">
                </div>
                <div class="mecanique-text">
                    <p>Le choix des pneus dépend de votre style de pilotage, des conditions météo et du type d'utilisation.</p>
                    
                    <h4>Types de pneus</h4>
                    <ul>
                        <li>Slick : 100% piste, adhérence maximale sur sol sec</li>
                        <li>Racing : piste et route, excellent grip, durée de vie limitée</li>
                        <li>Sport : route et piste occasionnelle, bon compromis grip/durée</li>
                        <li>Sport-Touring : route principalement, longue durée de vie</li>
                        <li>Pluie : spécifiques pour conditions humides</li>
                    </ul>
                    
                    <p><strong>Conseil :</strong> Pour la piste, privilégiez des pneus avec une gomme plus tendre pour plus d'adhérence, mais sachez qu'ils s'useront plus rapidement.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tab-content" id="electronique">
        <h2>Réglages Électroniques</h2>
        
        <div class="mecanique-section">
            <h3>Contrôle de traction</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/traction_control.jpg'); ?>" alt="Réglage du contrôle de traction">
                </div>
                <div class="mecanique-text">
                    <p>Le contrôle de traction limite la puissance envoyée à la roue arrière pour éviter le patinage.</p>
                    
                    <h4>Comment régler le contrôle de traction</h4>
                    <ol>
                        <li>Accédez au menu des réglages sur le tableau de bord</li>
                        <li>Sélectionnez le niveau d'intervention souhaité</li>
                        <li>Niveau élevé : intervention précoce, sécurité maximale</li>
                        <li>Niveau bas : intervention tardive, plus de liberté</li>
                        <li>Désactivé : aucune assistance (réservé aux pilotes expérimentés)</li>
                    </ol>
                    
                    <p><strong>Conseil :</strong> Sur piste sèche, commencez avec un niveau intermédiaire et réduisez progressivement à mesure que vous gagnez en confiance.</p>
                </div>
            </div>
        </div>
        
        <div class="mecanique-section">
            <h3>ABS</h3>
            <div class="mecanique-content">
                <div class="mecanique-image">
                    <img src="<?php echo url('images/mecanique/abs.jpg'); ?>" alt="Réglage de l'ABS">
                </div>
                <div class="mecanique-text">
                    <p>L'ABS empêche le blocage des roues lors d'un freinage d'urgence, maintenant la stabilité et la direction.</p>
                    
                    <h4>Comment régler l'ABS</h4>
                    <ol>
                        <li>Motos avec ABS réglable :
                            <ul>
                                <li>Accédez au menu des réglages sur le tableau de bord</li>
                                <li>Sélectionnez le mode souhaité (Route, Sport, Piste)</li>
                                <li>Mode Route : intervention précoce</li>
                                <li>Mode Sport : permet un léger dérapage contrôlé</li>
                                <li>Mode Piste : intervention minimale, permet le freinage en dérive</li>
                            </ul>
                        </li>
                    </ol>
                    
                    <p><strong>Attention :</strong> Sur circuit, certains pilotes désactivent l'ABS pour avoir un contrôle total, mais cela nécessite une excellente technique de freinage.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons et contenus
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            // Afficher le contenu correspondant
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>

<style>
.mecanique-container {
    padding: 1rem 0;
}

.mecanique-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.mecanique-intro {
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.mecanique-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 1rem;
}

.tab-button {
    background-color: var(--card-background);
    border: 1px solid var(--light-gray);
    color: var(--text-color);
    padding: 0.8rem 1.5rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s;
    font-weight: bold;
}

.tab-button:hover {
    background-color: rgba(0, 168, 255, 0.1);
    border-color: var(--primary-color);
}

.tab-button.active {
    background-color: var(--primary-color);
    color: #000;
    border-color: var(--primary-color);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.tab-content h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.mecanique-section {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.mecanique-section h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.mecanique-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

.mecanique-image img {
    width: 100%;
    border-radius: var(--border-radius);
    border: 1px solid var(--light-gray);
}

.mecanique-text h4 {
    color: var(--text-color);
    margin: 1rem 0 0.5rem;
    font-weight: bold;
}

.mecanique-text p {
    margin-bottom: 1rem;
    line-height: 1.6;
}

.mecanique-text ul, .mecanique-text ol {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.mecanique-text li {
    margin-bottom: 0.5rem;
}

@media (min-width: 768px) {
    .mecanique-content {
        grid-template-columns: 300px 1fr;
    }
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
