<?php
// Session déjà démarrée dans header.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EcoRide</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php include_once __DIR__ . '/../public/header.php'; ?>

    <div class="form-container">
        <form action="/traitement/connexion.php" method="POST" class="login-form">
            <h2>Connexion</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
