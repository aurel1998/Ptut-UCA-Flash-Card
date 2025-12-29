<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

// Statistiques pour le dashboard
if ($utilisateur['role'] === 'etudiant') {
    // Nombre de decks disponibles (assignés, partagés ou créés par l'étudiant)
    $requete = $connexion->prepare("
        SELECT COUNT(DISTINCT d.id) as total
        FROM decks d
        LEFT JOIN assignations_decks a ON d.id = a.deck_id
        LEFT JOIN partages_decks p ON d.id = p.deck_id AND p.utilisateur_partage_id = ?
        WHERE (a.utilisateur_id = ? OR (a.filiere = ? AND a.annee = ?))
        OR p.id IS NOT NULL
        OR d.utilisateur_id = ?
    ");
    $requete->execute([
        $utilisateur['id'],
        $utilisateur['id'],
        $utilisateur['filiere'],
        $utilisateur['annee'],
        $utilisateur['id']
    ]);
    $stats_decks = $requete->fetch();
    
    // Nombre total de cartes disponibles (pour permettre plusieurs révisions par jour)
    $requete = $connexion->prepare("
        SELECT COUNT(DISTINCT c.id) as total
        FROM cartes c
        JOIN decks d ON c.deck_id = d.id
        LEFT JOIN assignations_decks a ON d.id = a.deck_id
        LEFT JOIN partages_decks p ON d.id = p.deck_id AND p.utilisateur_partage_id = ?
        WHERE (a.utilisateur_id = ? OR (a.filiere = ? AND a.annee = ?))
        OR p.id IS NOT NULL
        OR d.utilisateur_id = ?
    ");
    $requete->execute([
        $utilisateur['id'],
        $utilisateur['id'],
        $utilisateur['filiere'],
        $utilisateur['annee'],
        $utilisateur['id']
    ]);
    $stats_revisions = $requete->fetch();
} else {
    // Nombre de decks créés
    $requete = $connexion->prepare("SELECT COUNT(*) as total FROM decks WHERE utilisateur_id = ?");
    $requete->execute([$utilisateur['id']]);
    $stats_decks = $requete->fetch();
    
    // Nombre de cartes créées
    $requete = $connexion->prepare("
        SELECT COUNT(*) as total 
        FROM cartes c
        JOIN decks d ON c.deck_id = d.id
        WHERE d.utilisateur_id = ?
    ");
    $requete->execute([$utilisateur['id']]);
    $stats_cartes = $requete->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Tableau de bord</h1>
        <p>Bienvenue, <?php echo htmlspecialchars($utilisateur['nom']); ?> !</p>
        
        <div class="stats-grid">
            <?php if ($utilisateur['role'] === 'etudiant'): ?>
                <div class="stat-card">
                    <h3>Decks disponibles</h3>
                    <p class="stat-number"><?php echo $stats_decks['total']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Cartes disponibles</h3>
                    <p class="stat-number"><?php echo $stats_revisions['total']; ?></p>
                </div>
            <?php else: ?>
                <div class="stat-card">
                    <h3>Mes decks</h3>
                    <p class="stat-number"><?php echo $stats_decks['total']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Mes cartes</h3>
                    <p class="stat-number"><?php echo $stats_cartes['total']; ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="actions-grid">
            <?php if ($utilisateur['role'] === 'etudiant'): ?>
                <a href="decks.php" class="action-card">
                    <h3>Mes decks</h3>
                    <p>Consulter tous vos decks de révision</p>
                </a>
                <a href="decks_partages.php" class="action-card">
                    <h3>Decks partagés</h3>
                    <p>Voir les decks partagés avec vous</p>
                </a>
                <a href="deck_creer.php" class="action-card">
                    <h3>Créer un deck</h3>
                    <p>Créer votre propre deck de révision</p>
                </a>
                <a href="revision.php" class="action-card">
                    <h3>Réviser</h3>
                    <p>Commencer une session de révision</p>
                </a>
                <a href="progression.php" class="action-card">
                    <h3>Ma progression</h3>
                    <p>Voir vos statistiques de révision</p>
                </a>
            <?php else: ?>
                <a href="decks.php" class="action-card">
                    <h3>Mes decks</h3>
                    <p>Gérer vos decks de cartes</p>
                </a>
                <a href="assignations.php" class="action-card">
                    <h3>Assignations</h3>
                    <p>Assigner des decks aux étudiants</p>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

