<?php
include_once '../public/header.php';
require_once '../config/mongodb_connection.php';

$success_message = "";
$error_message = "";

// Traitement du formulaire d'avis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $name = trim($_POST['nom']);
    $comment = trim($_POST['votre_avis']);
    
    if (empty($name) || empty($comment)) {
        $error_message = "Tous les champs sont obligatoires.";
    } else {
        // Vérifier que la connexion MongoDB est disponible
        if ($mongodb === null) {
            $error_message = "Erreur de connexion à la base de données.";
        } else {
            try {
                // Utiliser la nouvelle façon d'accéder à la collection
                $database = $mongodb->selectDatabase('EcoRideReviews');
                $collection = $database->selectCollection('reviews');
                
                $review = [
                    'name' => $name,
                    'comment' => $comment,
                    'date' => new MongoDB\BSON\UTCDateTime()
                ];
                
                $result = $collection->insertOne($review);
                
                if ($result->getInsertedCount()) {
                    $success_message = "Merci pour votre avis !";
                    $name = "";
                    $comment = "";
                } else {
                    $error_message = "Erreur lors de l'enregistrement de votre avis.";
                }
            } catch (Exception $e) {
                $error_message = "Erreur de connexion à la base de données: " . $e->getMessage();
                // Journaliser l'erreur pour le débogage
                error_log("Erreur MongoDB dans contact.php: " . $e->getMessage());
            }
        }
    }
}

// Récupération des avis
$reviews = [];
if ($mongodb !== null) {
    try {
        $database = $mongodb->selectDatabase('EcoRideReviews');
        $collection = $database->selectCollection('reviews');
        $cursor = $collection->find([], ['sort' => ['date' => -1]]);
        $reviews = $cursor->toArray();
    } catch (Exception $e) {
        $error_message = "Erreur lors de la récupération des avis: " . $e->getMessage();
        error_log("Erreur récupération avis: " . $e->getMessage());
    }
}
?>

<div class="container mt-5">
    <!-- Bloc avec informations de contact -->
    <div class="row mb-5">
        <div class="col-md-12">
            <h2>Contactez-nous</h2>
            <p>Pour toute question concernant nos services, y compris les propositions de collaboration, n'hésitez pas à nous contacter à l'adresse suivante :</p>
            <p class="email-contact"><a href="mailto:contact@ecoride.fr">contact@ecoride.fr</a></p>
            <p>Notre équipe vous répondra dans les plus brefs délais.</p>
        </div>
    </div>
    
    <!-- Formulaire d'avis avec la même structure que ajouter-voiture -->
    <div class="row">
        <div class="col-md-12">
            <form method="POST" action="" class="review-form-container">
                <h2>Avis sur notre plateforme</h2>
                <p class="review-intro">Rejoignez notre communauté de conducteurs et passagers pour une mobilité collaborative. Partagez votre expérience avec notre plateforme.</p>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nom">Nom<span class="required">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" required 
                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="votre_avis">Votre avis<span class="required">*</span></label>
                    <textarea class="form-control" id="votre_avis" name="votre_avis" rows="4" required><?php echo isset($comment) ? htmlspecialchars($comment) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <p><span class="required">*</span> Champs obligatoires</p>
                </div>
                
                <div class="form-submit">
                    <button type="submit" name="submit_review" class="btn btn-primary btn-envoyer">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Affichage des avis -->
    <?php if (!empty($reviews)): ?>
    <div class="row mt-5">
        <div class="col-md-12">
            <h3 class="text-center mb-4">Avis de nos utilisateurs</h3>
            <div class="row">
                <?php foreach ($reviews as $review): ?>
                    <div class="col-md-6 mb-4">
                        <div class="review-card">
                            <div class="review-content">
                                <h5 class="review-author"><?php echo htmlspecialchars($review->name); ?></h5>
                                <p class="review-text"><?php echo htmlspecialchars($review->comment); ?></p>
                            </div>
                            <div class="review-footer">
                                <?php 
                                    $date = $review->date->toDateTime();
                                    echo $date->format('d/m/Y H:i'); 
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
