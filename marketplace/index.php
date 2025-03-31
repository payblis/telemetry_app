<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth_functions.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est connecté
requireLogin();

// Connexion à la base de données
$conn = getDBConnection();

// Récupérer les produits
$sql = "SELECT p.*, c.nom as categorie_nom 
        FROM produits p
        LEFT JOIN categories_produits c ON p.categorie_id = c.id
        ORDER BY p.created_at DESC
        LIMIT 20";

$result = $conn->query($sql);
$produits = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produits[] = $row;
    }
}

// Récupérer les catégories
$sql = "SELECT * FROM categories_produits ORDER BY nom";
$result = $conn->query($sql);
$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Traitement de la recherche
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $categorie_id = isset($_GET['categorie_id']) ? intval($_GET['categorie_id']) : 0;
    
    $sql = "SELECT p.*, c.nom as categorie_nom 
            FROM produits p
            LEFT JOIN categories_produits c ON p.categorie_id = c.id
            WHERE p.nom LIKE ? OR p.description LIKE ?";
    
    if ($categorie_id > 0) {
        $sql .= " AND p.categorie_id = ?";
        $stmt = $conn->prepare($sql);
        $search_param = "%$search%";
        $stmt->bind_param("ssi", $search_param, $search_param, $categorie_id);
    } else {
        $stmt = $conn->prepare($sql);
        $search_param = "%$search%";
        $stmt->bind_param("ss", $search_param, $search_param);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    }
}

// Récupérer les détails d'un produit
$produit = null;
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $produit_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT p.*, c.nom as categorie_nom, u.nom as vendeur_nom, u.prenom as vendeur_prenom
                           FROM produits p
                           LEFT JOIN categories_produits c ON p.categorie_id = c.id
                           LEFT JOIN utilisateurs u ON p.vendeur_id = u.id
                           WHERE p.id = ?");
    $stmt->bind_param("i", $produit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $produit = $result->fetch_assoc();
        
        // Récupérer les avis sur le produit
        $stmt = $conn->prepare("SELECT a.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom
                               FROM avis_produits a
                               JOIN utilisateurs u ON a.utilisateur_id = u.id
                               WHERE a.produit_id = ?
                               ORDER BY a.created_at DESC");
        $stmt->bind_param("i", $produit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $avis = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $avis[] = $row;
            }
        }
        
        $produit['avis'] = $avis;
    }
}

// Traitement de l'ajout d'un produit
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $nom = $_POST['nom'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix = floatval($_POST['prix'] ?? 0);
    $categorie_id = intval($_POST['categorie_id'] ?? 0);
    $etat = $_POST['etat'] ?? '';
    $marque = $_POST['marque'] ?? '';
    $modele = $_POST['modele'] ?? '';
    $annee = intval($_POST['annee'] ?? 0);
    $vendeur_id = $_SESSION['user_id'];
    
    // Validation des données
    if (empty($nom) || empty($description) || $prix <= 0 || empty($categorie_id) || empty($etat)) {
        $error_message = 'Tous les champs obligatoires doivent être remplis.';
    } else {
        // Simuler l'upload d'image (en production, utiliser move_uploaded_file)
        $image_path = 'uploads/produits/' . uniqid() . '.jpg';
        
        // Insérer le produit dans la base de données
        $stmt = $conn->prepare("INSERT INTO produits (nom, description, prix, categorie_id, etat, marque, modele, annee, vendeur_id, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisissss", $nom, $description, $prix, $categorie_id, $etat, $marque, $modele, $annee, $vendeur_id, $image_path);
        
        if ($stmt->execute()) {
            $success_message = 'Produit ajouté avec succès.';
            
            // Rediriger pour éviter la soumission multiple du formulaire
            header("Location: " . url("marketplace/?success=1"));
            exit;
        } else {
            $error_message = 'Erreur lors de l\'ajout du produit: ' . $conn->error;
        }
    }
}

// Traitement de l'ajout d'un avis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    $produit_id = intval($_POST['produit_id'] ?? 0);
    $note = intval($_POST['note'] ?? 0);
    $commentaire = $_POST['commentaire'] ?? '';
    $utilisateur_id = $_SESSION['user_id'];
    
    // Validation des données
    if (empty($produit_id) || $note < 1 || $note > 5 || empty($commentaire)) {
        $error_message = 'Tous les champs obligatoires doivent être remplis.';
    } else {
        // Vérifier si l'utilisateur a déjà laissé un avis
        $stmt = $conn->prepare("SELECT id FROM avis_produits WHERE produit_id = ? AND utilisateur_id = ?");
        $stmt->bind_param("ii", $produit_id, $utilisateur_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = 'Vous avez déjà laissé un avis pour ce produit.';
        } else {
            // Insérer l'avis dans la base de données
            $stmt = $conn->prepare("INSERT INTO avis_produits (produit_id, utilisateur_id, note, commentaire) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $produit_id, $utilisateur_id, $note, $commentaire);
            
            if ($stmt->execute()) {
                $success_message = 'Avis ajouté avec succès.';
                
                // Rediriger pour éviter la soumission multiple du formulaire
                header("Location: " . url("marketplace/?id=$produit_id&success=2"));
                exit;
            } else {
                $error_message = 'Erreur lors de l\'ajout de l\'avis: ' . $conn->error;
            }
        }
    }
}

// Message de succès après redirection
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $success_message = 'Produit ajouté avec succès.';
    } elseif ($_GET['success'] == 2) {
        $success_message = 'Avis ajouté avec succès.';
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="marketplace-container">
    <?php if ($produit): ?>
        <div class="product-details">
            <div class="product-header">
                <h1 class="product-title"><?php echo htmlspecialchars($produit['nom']); ?></h1>
                <a href="<?php echo url('marketplace/'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
            
            <div class="product-content">
                <div class="product-images">
                    <div class="main-image">
                        <img src="<?php echo url($produit['image_path']); ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                    </div>
                </div>
                
                <div class="product-info">
                    <div class="product-price"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</div>
                    
                    <div class="product-meta">
                        <div class="meta-item">
                            <span class="meta-label">Catégorie:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($produit['categorie_nom']); ?></span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">État:</span>
                            <span class="meta-value"><?php echo getEtatLabel($produit['etat']); ?></span>
                        </div>
                        
                        <?php if (!empty($produit['marque'])): ?>
                            <div class="meta-item">
                                <span class="meta-label">Marque:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($produit['marque']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($produit['modele'])): ?>
                            <div class="meta-item">
                                <span class="meta-label">Modèle:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($produit['modele']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($produit['annee'])): ?>
                            <div class="meta-item">
                                <span class="meta-label">Année:</span>
                                <span class="meta-value"><?php echo $produit['annee']; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <span class="meta-label">Vendeur:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($produit['vendeur_prenom'] . ' ' . $produit['vendeur_nom']); ?></span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">Publié le:</span>
                            <span class="meta-value"><?php echo date('d/m/Y', strtotime($produit['created_at'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="product-description">
                        <h3>Description</h3>
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($produit['description'])); ?>
                        </div>
                    </div>
                    
                    <div class="product-actions">
                        <a href="mailto:contact@example.com?subject=Intérêt pour <?php echo urlencode($produit['nom']); ?>" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> Contacter le vendeur
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="product-reviews">
                <h2>Avis et commentaires</h2>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="reviews-summary">
                    <?php
                    $total_reviews = count($produit['avis']);
                    $average_rating = 0;
                    
                    if ($total_reviews > 0) {
                        $sum_ratings = array_sum(array_column($produit['avis'], 'note'));
                        $average_rating = $sum_ratings / $total_reviews;
                    }
                    ?>
                    
                    <div class="average-rating">
                        <div class="rating-value"><?php echo number_format($average_rating, 1); ?></div>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $average_rating): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i - 0.5 <= $average_rating): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-count"><?php echo $total_reviews; ?> avis</div>
                    </div>
                    
                    <button class="btn btn-primary" id="showReviewForm">
                        <i class="fas fa-comment"></i> Laisser un avis
                    </button>
                </div>
                
                <div class="review-form" id="reviewForm" style="display: none;">
                    <h3>Votre avis</h3>
                    
                    <form method="POST" action="<?php echo url('marketplace/?id=' . $produit['id']); ?>">
                        <input type="hidden" name="add_review" value="1">
                        <input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">
                        
                        <div class="form-group">
                            <label for="note">Note:</label>
                            <div class="rating-input">
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="note" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i == 5 ? 'checked' : ''; ?>>
                                        <label for="star<?php echo $i; ?>"><i class="far fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="commentaire">Commentaire:</label>
                            <textarea id="commentaire" name="commentaire" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Publier</button>
                            <button type="button" class="btn btn-secondary" id="cancelReview">Annuler</button>
                        </div>
                    </form>
                </div>
                
                <div class="reviews-list">
                    <?php if (empty($produit['avis'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun avis pour ce produit. Soyez le premier à donner votre avis !
                        </div>
                    <?php else: ?>
                        <?php foreach ($produit['avis'] as $avis): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <div class="reviewer-name"><?php echo htmlspecialchars($avis['utilisateur_prenom'] . ' ' . $avis['utilisateur_nom']); ?></div>
                                        <div class="review-date"><?php echo date('d/m/Y', strtotime($avis['created_at'])); ?></div>
                                    </div>
                                    
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $avis['note']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="review-content">
                                    <?php echo nl2br(htmlspecialchars($avis['commentaire'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <h1 class="marketplace-title">Marketplace d'Équipements</h1>
        
        <div class="marketplace-intro">
            <p>Achetez et vendez des équipements moto d'occasion entre passionnés. Trouvez des pièces, accessoires et équipements pour votre moto à des prix avantageux.</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="marketplace-actions">
            <button class="btn btn-primary" id="showAddProductForm">
                <i class="fas fa-plus"></i> Vendre un équipement
            </button>
        </div>
        
        <div class="marketplace-search">
            <form method="GET" action="<?php echo url('marketplace/'); ?>" class="search-form">
                <div class="search-input-group">
                    <input type="text" name="search" placeholder="Rechercher un équipement..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    
                    <select name="categorie_id">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?php echo $categorie['id']; ?>" <?php echo (isset($_GET['categorie_id']) && $_GET['categorie_id'] == $categorie['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categorie['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>
        
        <div class="add-product-form" id="addProductForm" style="display: none;">
            <h2>Vendre un équipement</h2>
            
            <form method="POST" action="<?php echo url('marketplace/'); ?>" enctype="multipart/form-data" class="product-form">
                <input type="hidden" name="add_product" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Titre de l'annonce:</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categorie_id">Catégorie:</label>
                        <select id="categorie_id" name="categorie_id" required>
                            <option value="">Sélectionner une catégorie</option>
                            <?php foreach ($categories as $categorie): ?>
                                <option value="<?php echo $categorie['id']; ?>">
                                    <?php echo htmlspecialchars($categorie['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="prix">Prix (€):</label>
                        <input type="number" id="prix" name="prix" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="etat">État:</label>
                        <select id="etat" name="etat" required>
                            <option value="">Sélectionner un état</option>
                            <option value="neuf">Neuf</option>
                            <option value="comme_neuf">Comme neuf</option>
                            <option value="tres_bon">Très bon état</option>
                            <option value="bon">Bon état</option>
                            <option value="acceptable">État acceptable</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="marque">Marque:</label>
                        <input type="text" id="marque" name="marque">
                    </div>
                    
                    <div class="form-group">
                        <label for="modele">Modèle:</label>
                        <input type="text" id="modele" name="modele">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="annee">Année:</label>
                    <input type="number" id="annee" name="annee" min="1900" max="<?php echo date('Y'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Photos:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    <small class="form-text">Formats acceptés: JPG, PNG, GIF. Taille maximale: 5 MB.</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Publier l'annonce</button>
                    <button type="button" class="btn btn-secondary" id="cancelAddProduct">Annuler</button>
                </div>
            </form>
        </div>
        
        <?php if (isset($_GET['search'])): ?>
            <div class="search-results">
                <h2>Résultats de recherche pour "<?php echo htmlspecialchars($_GET['search']); ?>"</h2>
                
                <?php if (empty($search_results)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucun résultat trouvé pour votre recherche.
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($search_results as $produit): ?>
                            <div class="product-card">
                                <a href="<?php echo url('marketplace/?id=' . $produit['id']); ?>" class="product-link">
                                    <div class="product-image">
                                        <img src="<?php echo url($produit['image_path']); ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                                    </div>
                                    
                                    <div class="product-card-content">
                                        <h3 class="product-card-title"><?php echo htmlspecialchars($produit['nom']); ?></h3>
                                        
                                        <div class="product-card-price"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</div>
                                        
                                        <div class="product-card-meta">
                                            <span class="product-category"><?php echo htmlspecialchars($produit['categorie_nom']); ?></span>
                                            <span class="product-state"><?php echo getEtatLabel($produit['etat']); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="featured-categories">
                <h2>Catégories populaires</h2>
                
                <div class="categories-grid">
                    <?php foreach ($categories as $categorie): ?>
                        <a href="<?php echo url('marketplace/?categorie_id=' . $categorie['id']); ?>" class="category-card">
                            <div class="category-icon">
                                <i class="<?php echo getCategoryIcon($categorie['nom']); ?>"></i>
                            </div>
                            <div class="category-name"><?php echo htmlspecialchars($categorie['nom']); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="latest-products">
                <h2>Dernières annonces</h2>
                
                <?php if (empty($produits)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucune annonce disponible pour le moment.
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($produits as $produit): ?>
                            <div class="product-card">
                                <a href="<?php echo url('marketplace/?id=' . $produit['id']); ?>" class="product-link">
                                    <div class="product-image">
                                        <img src="<?php echo url($produit['image_path']); ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                                    </div>
                                    
                                    <div class="product-card-content">
                                        <h3 class="product-card-title"><?php echo htmlspecialchars($produit['nom']); ?></h3>
                                        
                                        <div class="product-card-price"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</div>
                                        
                                        <div class="product-card-meta">
                                            <span class="product-category"><?php echo htmlspecialchars($produit['categorie_nom']); ?></span>
                                            <span class="product-state"><?php echo getEtatLabel($produit['etat']); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du formulaire d'ajout de produit
    const showAddProductBtn = document.getElementById('showAddProductForm');
    const addProductForm = document.getElementById('addProductForm');
    const cancelAddProductBtn = document.getElementById('cancelAddProduct');
    
    if (showAddProductBtn && addProductForm) {
        showAddProductBtn.addEventListener('click', function() {
            addProductForm.style.display = 'block';
            this.style.display = 'none';
        });
    }
    
    if (cancelAddProductBtn && addProductForm && showAddProductBtn) {
        cancelAddProductBtn.addEventListener('click', function() {
            addProductForm.style.display = 'none';
            showAddProductBtn.style.display = 'block';
        });
    }
    
    // Gestion du formulaire d'avis
    const showReviewFormBtn = document.getElementById('showReviewForm');
    const reviewForm = document.getElementById('reviewForm');
    const cancelReviewBtn = document.getElementById('cancelReview');
    
    if (showReviewFormBtn && reviewForm) {
        showReviewFormBtn.addEventListener('click', function() {
            reviewForm.style.display = 'block';
            this.style.display = 'none';
        });
    }
    
    if (cancelReviewBtn && reviewForm && showReviewFormBtn) {
        cancelReviewBtn.addEventListener('click', function() {
            reviewForm.style.display = 'none';
            showReviewFormBtn.style.display = 'block';
        });
    }
    
    // Gestion des étoiles pour la notation
    const ratingInputs = document.querySelectorAll('.rating-input input');
    const ratingLabels = document.querySelectorAll('.rating-input label');
    
    if (ratingInputs.length > 0 && ratingLabels.length > 0) {
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                const rating = parseInt(this.value);
                
                ratingLabels.forEach((label, index) => {
                    const star = label.querySelector('i');
                    if (index < rating) {
                        star.className = 'fas fa-star';
                    } else {
                        star.className = 'far fa-star';
                    }
                });
            });
        });
        
        // Initialiser les étoiles
        const checkedInput = document.querySelector('.rating-input input:checked');
        if (checkedInput) {
            checkedInput.dispatchEvent(new Event('change'));
        }
    }
});
</script>

<style>
.marketplace-container {
    padding: 1rem 0;
}

.marketplace-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.marketplace-intro {
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.marketplace-actions {
    margin-bottom: 2rem;
    text-align: right;
}

.marketplace-search {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.search-input-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.search-input-group input, .search-input-group select {
    flex: 1;
    min-width: 200px;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.add-product-form {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.add-product-form h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.product-form .form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .product-form .form-row {
        grid-template-columns: 1fr 1fr;
    }
}

.product-form .form-group {
    margin-bottom: 1.5rem;
}

.product-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.product-form input, .product-form select, .product-form textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.product-form textarea {
    resize: vertical;
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--dark-gray);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.featured-categories {
    margin-bottom: 2rem;
}

.featured-categories h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1.5rem;
}

.category-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
    text-align: center;
    text-decoration: none;
    color: var(--text-color);
    transition: transform 0.3s, border-color 0.3s;
}

.category-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-color);
}

.category-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.category-name {
    font-weight: bold;
}

.latest-products, .search-results {
    margin-bottom: 2rem;
}

.latest-products h2, .search-results h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.product-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    border: 1px solid var(--light-gray);
    transition: transform 0.3s, border-color 0.3s;
}

.product-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-color);
}

.product-link {
    text-decoration: none;
    color: var(--text-color);
    display: block;
}

.product-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-card-content {
    padding: 1rem;
}

.product-card-title {
    margin: 0 0 0.5rem;
    font-size: 1.1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-card-price {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.product-card-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: var(--dark-gray);
}

.product-details {
    margin-bottom: 2rem;
}

.product-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.product-title {
    color: var(--primary-color);
    margin: 0;
}

.product-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (min-width: 992px) {
    .product-content {
        grid-template-columns: 1fr 1fr;
    }
}

.product-images {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.main-image {
    width: 100%;
    height: 400px;
    overflow: hidden;
    border-radius: var(--border-radius);
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.product-info {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.product-price {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
}

.product-meta {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.meta-item {
    display: flex;
    flex-direction: column;
}

.meta-label {
    font-size: 0.9rem;
    color: var(--dark-gray);
}

.meta-value {
    font-weight: bold;
}

.product-description {
    margin-bottom: 1.5rem;
}

.product-description h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.description-content {
    line-height: 1.6;
}

.product-actions {
    display: flex;
    gap: 1rem;
}

.product-reviews {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.product-reviews h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.reviews-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.average-rating {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.rating-value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
}

.rating-stars {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.rating-count {
    color: var(--dark-gray);
}

.review-form {
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--light-gray);
}

.review-form h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input input {
    display: none;
}

.rating-input label {
    cursor: pointer;
    font-size: 1.5rem;
    color: var(--dark-gray);
    margin-right: 0.25rem;
}

.rating-input label:hover,
.rating-input label:hover ~ label,
.rating-input input:checked ~ label {
    color: var(--primary-color);
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-item {
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.reviewer-name {
    font-weight: bold;
}

.review-date {
    font-size: 0.9rem;
    color: var(--dark-gray);
}

.review-rating {
    color: var(--primary-color);
}

.review-content {
    line-height: 1.6;
}

.btn {
    padding: 0.8rem 1.2rem;
    border-radius: var(--border-radius);
    border: none;
    cursor: pointer;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-primary {
    background-color: var(--primary-color);
    color: #000;
}

.btn-primary:hover {
    background-color: #0095e0;
}

.btn-secondary {
    background-color: var(--dark-gray);
    color: var(--text-color);
}

.btn-secondary:hover {
    background-color: var(--light-gray);
}
</style>

<?php
// Fonction pour obtenir le libellé de l'état
function getEtatLabel($etat) {
    $etats = [
        'neuf' => 'Neuf',
        'comme_neuf' => 'Comme neuf',
        'tres_bon' => 'Très bon état',
        'bon' => 'Bon état',
        'acceptable' => 'État acceptable'
    ];
    
    return $etats[$etat] ?? 'État inconnu';
}

// Fonction pour obtenir l'icône de la catégorie
function getCategoryIcon($categorie) {
    $icons = [
        'Casques' => 'fas fa-helmet-safety',
        'Vêtements' => 'fas fa-tshirt',
        'Gants' => 'fas fa-mitten',
        'Bottes' => 'fas fa-boot',
        'Pneus' => 'fas fa-circle-notch',
        'Pièces moteur' => 'fas fa-cog',
        'Échappements' => 'fas fa-wind',
        'Suspensions' => 'fas fa-compress-alt',
        'Freins' => 'fas fa-brake-system',
        'Électronique' => 'fas fa-microchip',
        'Accessoires' => 'fas fa-tools',
        'Bagagerie' => 'fas fa-suitcase',
        'Entretien' => 'fas fa-oil-can',
        'Autres' => 'fas fa-box'
    ];
    
    return $icons[$categorie] ?? 'fas fa-box';
}

// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
