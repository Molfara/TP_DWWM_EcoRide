<!-- pages/connexion.php -->
<div class="form-container">
    <h2>Se connecter</h2>
    <form action="traitement_connexion.php" method="POST" class="login-form">
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
