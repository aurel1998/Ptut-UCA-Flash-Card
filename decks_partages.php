<?php
require_once 'config.php';
redirigerSiNonEtudiant();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

// Récupérer les decks partagés avec l'étudiant
$requete = $connexion->prepare("
    SELECT d.*, u.nom as createur_nom, u.email as createur_email,
    (SELECT COUNT(*) FROM cartes WHERE deck_id = d.id) as nombre_cartes,
    p.date_creation as date_partage
    FROM decks d
    JOIN partages_decks p ON d.id = p.deck_id
    JOIN utilisateurs u ON d.utilisateur_id = u.id
    WHERE p.utilisateur_partage_id = ?
    ORDER BY p.date_creation DESC
");
$requete->execute([$utilisateur['id']]);
$decks_partages = $requete->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decks Partagés - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Decks Partagés avec Moi</h1>
        </div>
        
        <?php if (empty($decks_partages)): ?>
            <div class="message-info">
                <p>Aucun deck partagé avec vous pour le moment.</p>
                <p>Les decks que d'autres étudiants partagent avec vous apparaîtront ici.</p>
            </div>
        <?php else: ?>
            <div class="decks-grid">
                <?php foreach ($decks_partages as $deck): ?>
                    <div class="deck-card">
                        <h3><?php echo htmlspecialchars($deck['titre']); ?></h3>
                        <p class="deck-description"><?php echo htmlspecialchars($deck['description'] ?? ''); ?></p>
                        <div class="deck-info">
                            <span class="deck-cartes"><?php echo $deck['nombre_cartes']; ?> cartes</span>
                            <span class="deck-createur">Partagé par <?php echo htmlspecialchars($deck['createur_nom']); ?></span>
                            <small>Le <?php echo date('d/m/Y', strtotime($deck['date_partage'])); ?></small>
                        </div>
                        <div class="deck-actions">
                            <a href="deck_voir.php?id=<?php echo $deck['id']; ?>" class="btn-secondary">Voir</a>
                            <a href="revision.php?deck_id=<?php echo $deck['id']; ?>" class="btn-primary">Réviser</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

