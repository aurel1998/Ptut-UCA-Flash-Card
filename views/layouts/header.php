<?php
if (!isset($utilisateur)) {
    $utilisateur = obtenirUtilisateurConnecte();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titre_page ?? 'Plateforme de Révision'; ?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<header>
    <div class="header-content">
        <div class="logo-left">
            <img src="/images/logouca.webp" alt="Logo UCA" class="logo">
        </div>
        <nav>
            <?php if ($utilisateur): ?>
                <a href="/index.php?controller=dashboard&action=index">Tableau de bord</a>
                <a href="/index.php?controller=deck&action=liste">Decks</a>
                <?php if ($utilisateur['role'] === 'etudiant'): ?>
                    <a href="/index.php?controller=deck&action=partages">Decks Partagés</a>
                    <a href="/index.php?controller=revision&action=index">Réviser</a>
                    <a href="/index.php?controller=progression&action=index">Progression</a>
                <?php elseif ($utilisateur['role'] === 'enseignant'): ?>
                    <a href="/index.php?controller=assignation&action=index">Assignations</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="/index.php?controller=accueil&action=index">Accueil</a>
            <?php endif; ?>
        </nav>
        <div class="user-menu">
            <?php if ($utilisateur): ?>
                <span class="user-name"><?php echo htmlspecialchars($utilisateur['nom']); ?></span>
                <a href="/index.php?controller=auth&action=logout" class="btn-logout">Déconnexion</a>
            <?php else: ?>
                <a href="/index.php?controller=auth&action=login">Connexion</a>
                <a href="/index.php?controller=auth&action=register" class="btn-register-header">Inscription</a>
            <?php endif; ?>
        </div>
        <div class="logo-right">
            <img src="/images/logo2.webp" alt="Logo" class="logo">
        </div>
    </div>
</header>

