<?php
// Session déjà démarrée dans header.php
// Pas besoin de DOCTYPE, html, head, etc. car ils sont déjà dans header.php et footer.php
?>

<div class="form-container">
    <form action="/connexion" method="POST" class="login-form">
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
