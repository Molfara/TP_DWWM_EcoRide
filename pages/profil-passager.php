<?php
// Démarrage de la session pour gérer l'authentification de l'utilisateur
// Inclusion du fichier d'authentification pour vérifier les droits d'accès
require_once __DIR__ . '/../middleware/auth.php';

// Vérification si l'utilisateur est connecté et a le rôle de passager
// Si non, redirection vers la page de connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'passager') {
    header('Location: connexion.php');
    exit;
}
    
// Récupération des informations de l'utilisateur depuis la base de données
require_once __DIR__ . '/../config/database.php';
$userId = $_SESSION['user_id'];

// Initialisation des variables de messages (ПЕРЕМЕЩЕНО В НАЧАЛО)
$message = '';
$error = '';

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
                    
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Passager - CoVoiturage</title>
    <!-- Inclusion de la feuille de style principale -->
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php
    // Inclusion de l'en-tête commun du site
    include_once '../public/header.php';
    ?>

<main class="container">
    <div class="hero-background passenger-hero">
        <div class="hero-content passenger-content">
            <h1>Espace Passager</h1>
            <a href="role" class="btn btn-white">Changer pour chauffeur</a>
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
            
    </script>
</main>
</body>
</html>


<!-- Section Mes données personnelles -->
<div class="personal-data-section">
<form method="POST" id="personalDataForm" class="personal-data-form" action="/traitement/profil-passager.php">
    <h2 class="section-title">Mes données personnelles</h2>
    <p class="section-description">Pour utiliser notre service de covoiturage, veuillez remplir vos informations personnelles.</p>
            
    <?php
    // Affichage des messages de succès ou d'erreur retournés par le traitement
    if (isset($_SESSION['message'])) {
        echo '<div class="message success">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
            
    if (isset($_SESSION['error'])) {
        echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    ?>
            
    <!-- Formulaire des données personnelles -->
    <form method="POST" id="personalDataForm" class="personal-data-form" action="/traitement/profil-passager.php">
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
                    
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validation du formulaire lors de la soumission
        document.getElementById('personalDataForm').addEventListener('submit', function(e) {
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
        
        // Fonction pour vérifier le format de l'email
        function isValidEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }
        
        // Effacement du message d'erreur lors de la mise au point sur un champ
        document.querySelectorAll('input').forEach(input => {
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
