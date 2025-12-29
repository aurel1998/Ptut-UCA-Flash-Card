<?php
require_once 'config.php';
$utilisateur = obtenirUtilisateurConnecte();
?>
<header>
    <div class="header-content">
        <div class="logo-left">
            <img src="images/logouca.webp" alt="Logo UCA" class="logo">
        </div>
        <nav>
            <?php if ($utilisateur): ?>
                <a href="dashboard.php">Tableau de bord</a>
                <a href="decks.php">Decks</a>
                <?php if ($utilisateur['role'] === 'etudiant'): ?>
                    <a href="decks_partages.php">Decks Partagés</a>
                    <a href="revision.php">Réviser</a>
                    <a href="progression.php">Progression</a>
                <?php elseif ($utilisateur['role'] === 'enseignant'): ?>
                    <a href="assignations.php">Assignations</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="accueil.php">Accueil</a>
            <?php endif; ?>
        </nav>
        <div class="user-menu">
            <?php if ($utilisateur): ?>
                <span class="user-name"><?php echo htmlspecialchars($utilisateur['nom']); ?></span>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="register.php" class="btn-register-header">Inscription</a>
            <?php endif; ?>
        </div>
        <div class="logo-right">
            <img src="images/logo2.webp" alt="Logo" class="logo">
        </div>
    </div>
</header>

