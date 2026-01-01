<?php
$titre_page = 'Connexion';
?>
<div class="container-auth">
    <div class="form-container">
        <h1>Connexion</h1>
        <?php if (isset($erreur) && $erreur): ?>
            <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>
        <form method="POST" action="/index.php?controller=auth&action=login">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>
        <p class="lien-inscription">Pas encore de compte ? <a href="/index.php?controller=auth&action=register">S'inscrire</a></p>
        <p class="lien-inscription"><a href="/index.php?controller=accueil&action=index">← Retour à l'accueil</a></p>
    </div>
</div>

