<?php
// Démarrage de la session pour gérer l'authentification de l'utilisateur

// Au début de votre fichier PHP
$message = '';
$error = '';

// Inclusion du fichier d'authentification pour vérifier les droits d'accès
require_once __DIR__ . '/../middleware/auth.php';
        
// Vérification si l'utilisateur est connecté et a le rôle de chauffeur
// Si non, redirection vers la page de connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'chauffeur') {
    header('Location: connexion.php');
    exit;
}
    
// Récupération des informations de l'utilisateur depuis la base de données
require_once __DIR__ . '/../config/database.php';
$userId = $_SESSION['user_id'];
try {
    // Préparation et exécution de la requête pour obtenir les données de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    $error = "Erreur de base de données: " . $e->getMessage();
}

// Traitement de l'upload de photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];

    // Vérification des erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Erreur lors du téléchargement du fichier.';
    } else {
        // Vérification du type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $file['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Seuls les fichiers JPG, PNG et GIF sont autorisés.';
        } else {
            // Vérification de la taille du fichier (maximum 4MB)
            $maxSize = 4 * 1024 * 1024; // 4MB en octets
            if ($file['size'] > $maxSize) {
                $error = 'Le fichier est trop volumineux. Taille maximum: 4MB.';
            } else {
                // Lecture du fichier en format binaire
                $photoData = file_get_contents($file['tmp_name']);
  
                // Sauvegarde dans la base de données
                try {
                    $stmt = $pdo->prepare("UPDATE utilisateur SET photo = ? WHERE utilisateur_id = ?");
                    $stmt->execute([$photoData, $userId]);
                    //$message = 'Photo de profil mise à jour avec succès!';

                    // Mise à jour de la session avec la nouvelle photo pour affichage immédiat dans le header
                    $_SESSION['user_avatar'] = "data:image/jpeg;base64," . base64_encode($photoData);
    
                    // Mise à jour des données utilisateur pour affichage immédiat
                    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $error = 'Erreur lors de la sauvegarde en base de données: ' . $e->getMessage();
                }
            }
        }
    }
}

// Traitement de la suppression de photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photo'])) {
    try {
        // Mise à NULL de la photo dans la base de données
        $stmt = $pdo->prepare("UPDATE utilisateur SET photo = NULL WHERE utilisateur_id = ?");
        $stmt->execute([$userId]);
        //$message = 'Photo supprimée avec succès!';
        
        // Suppression de la photo de la session
        $_SESSION['user_avatar'] = null;
        
        // Mise à jour des données utilisateur pour affichage immédiat
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Erreur lors de la suppression: ' . $e->getMessage();
    }
}

// Récupération des véhicules de l'utilisateur
$vehicules = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM voiture WHERE utilisateur_id = ?");
    $stmt->execute([$userId]);
    $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des véhicules: " . $e->getMessage();
}

// Récupération des marques disponibles
$marques = [];
try {
    // Utilisez GROUP BY pour éliminer les doublons
    $stmt = $pdo->query("SELECT MIN(marque_id) as marque_id, libelle 
                         FROM marque 
                         GROUP BY libelle 
                         ORDER BY libelle");
    $marques = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Получено уникальных марок: " . count($marques));
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des marques: " . $e->getMessage();
    error_log($error);
}

?>
                    
<!DOCTYPE html>
<html lang="fr">  
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Chauffeur - CoVoiturage</title>
    <!-- Inclusion de la feuille de style principale -->
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
    <?php
    // Inclusion de l'en-tête commun du site
    include_once '../public/header.php';
    ?>
                
<main class="container">
    <div class="hero-background driver-hero">
        <div class="chauffeur-content">
            <h1>Espace Chauffeur</h1>
            <a href="role" class="btn btn-white">Changer pour passager</a>
        </div>
    </div>

<!-- Добавить этот код после закрывающего div hero -->
    <div class="profile-section">
<!-- Titre principal de la page -->
    <h2 class="page-title">Mon profil</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    
        <div class="profile-photo-container">
            <div class="profile-photo" id="profilePhoto">
                <?php if (!empty($user['photo'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($user['photo']); ?>" alt="Photo de profil">
                <?php else: ?>
                    <span class="profile-initial">
                        <?php echo isset($user['pseudo']) ? strtoupper(substr($user['pseudo'], 0, 1)) : 'U'; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    
<div class="photo-controls">
    <form method="POST" enctype="multipart/form-data" id="photoForm">
        <?php if (empty($user['photo'])): ?>
            <button type="button" onclick="document.getElementById('photoInput').click()" class="btn">
                Ajouter une image
            </button>
        <?php else: ?>
            <button type="button" onclick="document.getElementById('photoInput').click()" class="btn">
                Modifier
            </button>   
            <button type="button" onclick="deletePhoto()" class="btn">
                Supprimer
            </button>
        <?php endif; ?>
        
        <input type="file" name="avatar" id="photoInput" class="file-input"
               accept="image/jpeg,image/png,image/gif" onchange="previewAndUpload(this)">
    </form>
</div>

    
        <div id="uploadMessage" class="upload-message"></div>
    </div>
            
    <script>
    function previewAndUpload(input) {
       if (input.files && input.files[0]) {
            const file = input.files[0];
        
            // Preview de l'image dans le profil
            const reader = new FileReader(); 
            reader.onload = function(e) {
                const profilePhoto = document.getElementById('profilePhoto');
                profilePhoto.innerHTML = `<img src="${e.target.result}" alt="Photo de profil">`;
            };
            reader.readAsDataURL(file);
                        
            // Soumission automatique du formulaire
            document.getElementById('photoForm').submit();
        }
    }
    
    function deletePhoto() {
        if (confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil ?')) {
            // Création d'un formulaire pour supprimer la photo
            const form = document.createElement('form');
            form.method = 'POST';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_photo';
            input.value = '1';
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
               
    function showMessage(message, type) {
        const messageDiv = document.getElementById('uploadMessage');
        messageDiv.textContent = message;
        messageDiv.className = 'upload-message upload-' + type;
        messageDiv.style.display = 'block';
    
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }

    /**
 * Fonction pour confirmer et traiter la suppression d'un véhicule
 * @param {number} vehicleId - L'identifiant de la voiture à supprimer
 */
function confirmDeleteVehicle(vehicleId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce véhicule?')) {
        // Création d'un formulaire caché pour soumettre la demande de suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/traitement/process-car.php';
        
        // Ajout d'un champ caché avec l'ID du véhicule
        const vehicleIdInput = document.createElement('input');
        vehicleIdInput.type = 'hidden';
        vehicleIdInput.name = 'voiture_id';
        vehicleIdInput.value = vehicleId;
        
        // Ajout d'un champ caché avec l'action à effectuer
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_vehicle';
        
        // Ajout des champs au formulaire
        form.appendChild(vehicleIdInput);
        form.appendChild(actionInput);
        
        // Ajout du formulaire au document et soumission
        document.body.appendChild(form);
        form.submit();
    }
}
            
    </script>


    <?php
    // Affichage des messages d'erreur si présents
    if (isset($error) && !empty($error)):
    ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php
    endif;
    ?>

<!-- Conteneur pour placer les formulaires côte à côte -->
<div class="profile-forms-container">

<!-- Section Mes données personnelles -->
<div class="personal-data-section">
    <h2 class="section-title">Mes données personnelles</h2>
    <p class="section-description">Pour utiliser notre service de covoiturage, veuillez remplir vos informations personnelles.</p>
        
    <?php
    // Affichage des messages de succès ou d'erreur pour les données personnelles
    if (isset($_SESSION['personal_message']) && !empty($_SESSION['personal_message'])) {
        echo '<div class="message success">' . htmlspecialchars($_SESSION['personal_message']) . '</div>';
        unset($_SESSION['personal_message']);
    }
            
    if (isset($_SESSION['personal_error']) && !empty($_SESSION['personal_error'])) {
        echo '<div class="message error">' . htmlspecialchars($_SESSION['personal_error']) . '</div>';
        unset($_SESSION['personal_error']);
    }
    ?>
           
    <!-- Affichage en lecture seule -->
    <div id="personalDataDisplay" class="data-display">
        <div class="data-item">
            <span class="data-label">Pseudo :</span>
            <span class="data-value"><?php echo htmlspecialchars($user['pseudo'] ?? 'Non renseigné'); ?></span>
        </div>
        
        <?php if (!empty($user['nom'])): ?>
        <div class="data-item">
            <span class="data-label">Nom :</span>
            <span class="data-value"><?php echo htmlspecialchars($user['nom']); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($user['prenom'])): ?>
        <div class="data-item">
            <span class="data-label">Prénom :</span>
            <span class="data-value"><?php echo htmlspecialchars($user['prenom']); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="data-item">
            <span class="data-label">Email :</span>
            <span class="data-value"><?php echo htmlspecialchars($user['email'] ?? 'Non renseigné'); ?></span>
        </div>
        
        <?php if (!empty($user['telephone'])): ?>
        <div class="data-item">
            <span class="data-label">Téléphone :</span>
            <span class="data-value"><?php echo htmlspecialchars($user['telephone']); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($user['adresse'])): ?>
        <div class="data-item">
            <span class="data-label">Adresse :</span>
            <span class="data-value"><?php echo htmlspecialchars($user['adresse']); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($user['date_naissance'])): ?>
        <div class="data-item">
            <span class="data-label">Date de naissance :</span>
            <span class="data-value"><?php echo htmlspecialchars($user['date_naissance']); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="data-item">
            <span class="data-label">Crédits :</span>
            <span class="data-value"><?php echo htmlspecialchars($user['credits'] ?? '0'); ?></span>
        </div>
        
        <div class="form-actions">
            <button type="button" id="editPersonalDataBtn" class="btn btn-primary">Modifier</button>
        </div>
    </div>
    
    <!-- Formulaire de modification (caché par défaut) -->
    <div id="personalDataEditForm" class="data-form" style="display: none;">
        <form method="POST" class="personal-data-form" action="/traitement/profil-utilisateur.php">
            <div class="form-group">
                <label for="pseudo">Pseudo<sup>*</sup></label>
                <input type="text" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($user['pseudo'] ?? ''); ?>" required>
                <div class="error-message" id="pseudo-error"></div>
            </div>
            
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>">
            </div>
        
            <div class="form-group">
                <label for="email">Email<sup>*</sup></label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                <div class="error-message" id="email-error"></div>
            </div>  

            <div class="form-group">   
                <label for="password">Mot de passe<sup>*</sup></label>
                <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas modifier">
                <div class="error-message" id="password-error"></div>
            </div>
        
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['adresse'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($user['date_naissance'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="credits">Crédits</label>
                <div class="credits-container">
                    <input type="text" id="credits" name="credits" value="<?php echo htmlspecialchars($user['credits'] ?? '0'); ?>" readonly>
                </div>
            </div>
                
            <div class="form-note">
                <p><sup>*</sup> Champs obligatoires</p>
            </div>
            
            <div class="form-actions">
                <input type="hidden" name="update_personal_data" value="1">
                <button type="submit" class="btn btn-primary">Sauvegarder les modifications</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du bouton "Modifier"
    const editBtn = document.getElementById('editPersonalDataBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const displayDiv = document.getElementById('personalDataDisplay');
    const editFormDiv = document.getElementById('personalDataEditForm');
    const form = document.querySelector('#personalDataEditForm form');

    if (editBtn) {
    editBtn.className = 'btn btn-primary';
}
    
    if (editBtn && displayDiv && editFormDiv) {
        editBtn.addEventListener('click', function() {
            console.log('Bouton Modifier cliqué'); // Debug
            displayDiv.style.display = 'none';
            editFormDiv.style.display = 'block';
        });
    }
    
    // Validation du formulaire lors de la soumission
    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            
            // Vérification des champs obligatoires
            const pseudo = document.getElementById('pseudo');
            const email = document.getElementById('email');
        
            // Validation du pseudo
            if (!pseudo.value.trim()) {
                document.getElementById('pseudo-error').textContent = 'Le pseudo est obligatoire.';
                pseudo.classList.add('error');
                hasError = true;
            } else {
                document.getElementById('pseudo-error').textContent = '';
                pseudo.classList.remove('error');
            }
        
            // Validation de l'email
            if (!email.value.trim()) {
                document.getElementById('email-error').textContent = 'L\'email est obligatoire.';
                email.classList.add('error');
                hasError = true;
            } else if (!isValidEmail(email.value.trim())) {
                document.getElementById('email-error').textContent = 'Veuillez entrer une adresse email valide.';
                email.classList.add('error');
                hasError = true;
            } else {
                document.getElementById('email-error').textContent = '';
                email.classList.remove('error');
            }
        
            if (hasError) {
                e.preventDefault(); // Empêcher l'envoi du formulaire en cas d'erreurs
            }
        });
    }
    
    // Fonction pour vérifier le format de l'email
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    // Effacement du message d'erreur lors de la mise au point sur un champ
    document.querySelectorAll('#personalDataEditForm input').forEach(input => {
        input.addEventListener('focus', function() {
            const errorId = this.id + '-error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.textContent = '';
            }
            this.classList.remove('error');
        });
    });
});
</script>

<!-- Section Mes véhicules -->
<div class="vehicles-section">
    <h2 class="section-title">Mes véhicules</h2>
    <p class="section-description">Pour le rôle de chauffeur, vous devez enregistrer au moins un véhicule.</p>
    
    <?php
    // Affichage des messages de succès ou d'erreur
    if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
        echo '<div class="message success">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
            
    if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
        echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    ?>
    
    <!-- Affichage des véhicules en lecture seule -->
    <div id="vehiclesDisplay" class="vehicles-display">
        <?php if (!empty($vehicules)): ?>
            <?php foreach ($vehicules as $index => $vehicule): ?>
                <div class="vehicle-display-item">
                    <div class="vehicle-header">
                        <h3>Véhicule <?php echo $index + 1; ?></h3>
                        <button type="button" class="btn btn-danger btn-sm"
                                onclick="confirmDeleteVehicle(<?php echo $vehicule['voiture_id']; ?>)">
                            Supprimer
                        </button>
                    </div>
                    
                    <div class="vehicle-details">
                        <div class="data-item">
                            <span class="data-label">Marque :</span>
                            <span class="data-value">
                                <?php 
                                // Trouver le libellé de la marque
                                foreach ($marques as $marque) {
                                    if ($marque['marque_id'] == $vehicule['marque_id']) {
                                        echo htmlspecialchars($marque['libelle']);
                                        break;
                                    }
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div class="data-item">
                            <span class="data-label">Modèle :</span>
                            <span class="data-value"><?php echo htmlspecialchars($vehicule['modele']); ?></span>
                        </div>
                        
                        <div class="data-item">
                            <span class="data-label">Immatriculation :</span>
                            <span class="data-value"><?php echo htmlspecialchars($vehicule['immatriculation']); ?></span>
                        </div>
                        
                        <div class="data-item">
                            <span class="data-label">Énergie :</span>
                            <span class="data-value"><?php echo htmlspecialchars(ucfirst($vehicule['energie'])); ?></span>
                        </div>
                        
                        <?php if (!empty($vehicule['couleur'])): ?>
                        <div class="data-item">
                            <span class="data-label">Couleur :</span>
                            <span class="data-value"><?php echo htmlspecialchars($vehicule['couleur']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($vehicule['date_premiere_immatriculation'])): ?>
                        <div class="data-item">
                            <span class="data-label">Date première immatriculation :</span>
                            <span class="data-value"><?php echo htmlspecialchars($vehicule['date_premiere_immatriculation']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="data-item">
                            <span class="data-label">Nombre de places :</span>
                            <span class="data-value"><?php echo htmlspecialchars($vehicule['nb_places']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-vehicles">
                <p>Aucun véhicule enregistré. Ajoutez votre premier véhicule pour pouvoir proposer des trajets.</p>
            </div>
        <?php endif; ?>
        
        <div class="vehicle-actions">
            <?php if (!empty($vehicules)): ?>
                <button type="button" id="editVehiclesBtn" class="btn btn-primary">Modifier</button>
            <?php endif; ?>
            <button type="button" id="addVehicleBtn" class="btn btn-primary">Ajouter</button>
        </div>
    </div>
    
    <!-- Formulaire de modification des véhicules existants (caché par défaut) -->
    <?php if (!empty($vehicules)): ?>
    <form method="POST" action="/traitement/process-car.php" id="vehiclesEditForm" class="vehicles-edit-form" style="display: none;">
        <input type="hidden" name="action" value="update_vehicles">
        
        <div class="vehicles-list">
            <?php foreach ($vehicules as $index => $vehicule): ?>
                <div class="vehicle-item">
                    <div class="vehicle-header">
                        <h3>Véhicule <?php echo $index + 1; ?></h3>
                    </div>
                    
                    <div class="form-group">
                        <label for="marque_id_<?php echo $index; ?>">Marque<sup>*</sup></label>
                        <select id="marque_id_<?php echo $index; ?>" name="vehicules[<?php echo $index; ?>][marque_id]" required>
                            <?php foreach ($marques as $marque): ?>
                                <option value="<?php echo $marque['marque_id']; ?>" <?php echo ($vehicule['marque_id'] ?? '') == $marque['marque_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($marque['libelle']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="modele_<?php echo $index; ?>">Modèle<sup>*</sup></label>
                        <input type="text" id="modele_<?php echo $index; ?>" name="vehicules[<?php echo $index; ?>][modele]" value="<?php echo htmlspecialchars($vehicule['modele'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="immatriculation_<?php echo $index; ?>">Immatriculation<sup>*</sup></label>
                        <input type="text" id="immatriculation_<?php echo $index; ?>" name="vehicules[<?php echo $index; ?>][immatriculation]" value="<?php echo htmlspecialchars($vehicule['immatriculation'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="energie_<?php echo $index; ?>">Énergie<sup>*</sup></label>
                        <select id="energie_<?php echo $index; ?>" name="vehicules[<?php echo $index; ?>][energie]" required>
                            <option value="essence" <?php echo ($vehicule['energie'] ?? '') === 'essence' ? 'selected' : ''; ?>>Essence</option>
                            <option value="diesel" <?php echo ($vehicule['energie'] ?? '') === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                            <option value="electrique" <?php echo ($vehicule['energie'] ?? '') === 'electrique' ? 'selected' : ''; ?>>Électrique</option>
                            <option value="hybride" <?php echo ($vehicule['energie'] ?? '') === 'hybride' ? 'selected' : ''; ?>>Hybride</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="couleur_<?php echo $index; ?>">Couleur</label>
                        <input type="text" id="couleur_<?php echo $index; ?>" name="vehicules[<?php echo $index; ?>][couleur]" value="<?php echo htmlspecialchars($vehicule['couleur'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_premiere_immatriculation_<?php echo $index; ?>">Date première immatriculation</label>
                        <input type="date" id="date_premiere_immatriculation_<?php echo $index; ?>" name="vehicules[<?php echo $index; ?>][date_premiere_immatriculation]" value="<?php echo htmlspecialchars($vehicule['date_premiere_immatriculation'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="nb_places_<?php echo $index; ?>">Nombre de places<sup>*</sup></label>
                        <input type="number" id="nb_places_<?php echo $index; ?>" name="vehicules[<?php echo $index; ?>][nb_places]" value="<?php echo htmlspecialchars($vehicule['nb_places'] ?? ''); ?>" min="1" max="9" required>
                    </div>
                    
                    <input type="hidden" name="vehicules[<?php echo $index; ?>][voiture_id]" value="<?php echo $vehicule['voiture_id'] ?? ''; ?>">
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="form-note">
            <p><sup>*</sup> Champs obligatoires</p>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Sauvegarder les modifications</button>
        </div>
    </form>
    <?php endif; ?>
    
    <!-- Formulaire d'ajout d'un nouveau véhicule (caché par défaut) -->
    <form method="POST" action="/traitement/process-car.php" id="addVehicleForm" class="add-vehicle-form" style="display: none;">
        <input type="hidden" name="action" value="add_vehicle">
        
        <h3>Nouveau véhicule</h3>
        
        <div class="form-group">
            <label for="new_marque_id">Marque<sup>*</sup></label>
            <select id="new_marque_id" name="marque_id" required>
                <option value="">Choisir une marque</option>
                <?php foreach ($marques as $marque): ?>
                    <option value="<?php echo $marque['marque_id']; ?>">
                        <?php echo htmlspecialchars($marque['libelle']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="new_modele">Modèle<sup>*</sup></label>
            <input type="text" id="new_modele" name="modele" required>
        </div>
        
        <div class="form-group">
            <label for="new_immatriculation">Immatriculation<sup>*</sup></label>
            <input type="text" id="new_immatriculation" name="immatriculation" required>
        </div>
        
        <div class="form-group">
            <label for="new_energie">Énergie<sup>*</sup></label>
            <select id="new_energie" name="energie" required>
                <option value="">Choisir une énergie</option>
                <option value="essence">Essence</option>
                <option value="diesel">Diesel</option>
                <option value="electrique">Électrique</option>
                <option value="hybride">Hybride</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="new_couleur">Couleur</label>
            <input type="text" id="new_couleur" name="couleur">
        </div>
        
        <div class="form-group">
            <label for="new_date_premiere_immatriculation">Date première immatriculation</label>
            <input type="date" id="new_date_premiere_immatriculation" name="date_premiere_immatriculation">
        </div>
        
        <div class="form-group">
            <label for="new_nb_places">Nombre de places<sup>*</sup></label>
            <input type="number" id="new_nb_places" name="nb_places" min="1" max="9" required>
        </div>
        
        <div class="form-note">
            <p><sup>*</sup> Champs obligatoires</p>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Ajouter le véhicule</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const vehiclesDisplay = document.getElementById('vehiclesDisplay');
    const vehiclesEditForm = document.getElementById('vehiclesEditForm');
    const addVehicleForm = document.getElementById('addVehicleForm');
    
    const editVehiclesBtn = document.getElementById('editVehiclesBtn');
    const addVehicleBtn = document.getElementById('addVehicleBtn');
    const cancelEditVehiclesBtn = document.getElementById('cancelEditVehiclesBtn');
    const cancelAddVehicleBtn = document.getElementById('cancelAddVehicleBtn');
    
    // Gestion du bouton "Modifier" les véhicules
    if (editVehiclesBtn) {
        editVehiclesBtn.addEventListener('click', function() {
            console.log('Bouton Modifier véhicules cliqué');
            vehiclesDisplay.style.display = 'none';
            if (vehiclesEditForm) {
                vehiclesEditForm.style.display = 'block';
            }
        });
    }
    
    // Gestion du bouton "Ajouter" un véhicule
    if (addVehicleBtn) {
        addVehicleBtn.addEventListener('click', function() {
            console.log('Bouton Ajouter véhicule cliqué');
            vehiclesDisplay.style.display = 'none';
            addVehicleForm.style.display = 'block';
        });
    }

    // Gestion du bouton "Annuler" modification
    if (cancelEditVehiclesBtn) {
        cancelEditVehiclesBtn.addEventListener('click', function() {
            console.log('Annuler modification véhicules');
            if (vehiclesEditForm) {
                vehiclesEditForm.style.display = 'none';
            }
            vehiclesDisplay.style.display = 'block';
        });
    }
    
    // Gestion du bouton "Annuler" ajout
    if (cancelAddVehicleBtn) {
        cancelAddVehicleBtn.addEventListener('click', function() {
            console.log('Annuler ajout véhicule');
            addVehicleForm.style.display = 'none';
            vehiclesDisplay.style.display = 'block';
            
            // Réinitialiser le formulaire
            addVehicleForm.reset();
        });
    }
});

// Fonction pour confirmer et traiter la suppression d'un véhicule
function confirmDeleteVehicle(vehicleId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce véhicule?')) {
        // Création d'un formulaire caché pour soumettre la demande de suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/traitement/process-car.php';
        
        // Ajout d'un champ caché avec l'ID du véhicule
        const vehicleIdInput = document.createElement('input');
        vehicleIdInput.type = 'hidden';
        vehicleIdInput.name = 'voiture_id';
        vehicleIdInput.value = vehicleId;
        
        // Ajout d'un champ caché avec l'action à effectuer
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_vehicle';
        
        // Ajout des champs au formulaire
        form.appendChild(vehicleIdInput);
        form.appendChild(actionInput);
        
        // Ajout du formulaire au document et soumission
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

</main>
</body>
</html> 