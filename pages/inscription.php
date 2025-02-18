<!-- pages/inscription.php -->
<div class="form-container">
    <?php
    ?>
     <form action="/inscription" method="POST" class="register-form">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="pseudo">Pseudo</label>
            <input type="text" id="pseudo" name="pseudo" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="role">Type de compte</label>
            <select id="role" name="role" required>
                <option value="passager">Passager</option>
                <option value="chauffeur">Chauffeur</option>
                <option value="both">Passager et Chauffeur</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">Créer un compte</button>
    </form>
</div>
