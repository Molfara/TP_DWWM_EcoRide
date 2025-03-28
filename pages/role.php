<?php
// Vérification de l'autorisation
if (!isset($_SESSION['user_id'])) {
    header('Location: /connexion');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir votre rôle - EcoRide</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php include_once __DIR__ . '/../public/header.php'; ?>

    <div class="role-container">
        <h1>Bienvenue sur EcoRide</h1>
        <p class="role-question">Comment souhaitez-vous utiliser la plateforme ? Vous pourrez toujours modifier ce choix plus tard.</p>

        <div class="role-buttons">
            <form action="/traitement/process-role.php" method="post">
                <input type="hidden" name="role" value="2"> <!-- Pour passager/utilisateur -->
                <button type="submit" class="role-button passager-button">Je suis passager</button>
            </form>

            <form action="/role" method="post">
                <input type="hidden" name="role" value="3"> <!-- Pour chauffeur/utilisateur -->
                <button type="submit" class="role-button chauffeur-button">Je suis chauffeur</button>
            </form>
        </div>
    </div>
</body>
</html>
