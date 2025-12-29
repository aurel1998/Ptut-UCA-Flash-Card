<?php
require_once 'config.php';
redirigerSiNonEtudiant();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

// Récupérer les decks disponibles pour la révision
$deck_id_filtre = $_GET['deck_id'] ?? null;

// Compter les cartes et vérifier si le deck a déjà été révisé aujourd'hui
$requete = $connexion->prepare("
    SELECT DISTINCT d.*,
    (SELECT COUNT(*) FROM cartes c WHERE c.deck_id = d.id) as cartes_a_reviser,
    (SELECT MAX(date(date_revision)) = date('now') 
     FROM historique_revisions 
     WHERE utilisateur_id = ? AND deck_id = d.id) as deja_revise_aujourdhui
    FROM decks d
    LEFT JOIN assignations_decks a ON d.id = a.deck_id
    LEFT JOIN partages_decks p ON d.id = p.deck_id AND p.utilisateur_partage_id = ?
    WHERE (a.utilisateur_id = ? OR (a.filiere = ? AND a.annee = ?))
    OR p.id IS NOT NULL
    OR d.utilisateur_id = ?
    ORDER BY d.titre
");
$requete->execute([
    $utilisateur['id'],
    $utilisateur['id'],
    $utilisateur['id'],
    $utilisateur['filiere'],
    $utilisateur['annee'],
    $utilisateur['id']
]);
$decks = $requete->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Révision - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Révision</h1>
        <p>Sélectionnez un deck pour commencer une session de révision</p>
        <p class="info-revision"><strong>Note :</strong> Vous pouvez réviser chaque deck une seule fois par jour.</p>
        
        <?php if (isset($_GET['message']) && $_GET['message'] === 'deja_revise'): ?>
            <div class="message-erreur">Ce deck a déjà été révisé aujourd'hui. Vous pourrez le réviser à nouveau demain.</div>
        <?php endif; ?>
        
        <div class="decks-grid">
            <?php foreach ($decks as $deck): ?>
                <?php if ($deck_id_filtre && $deck['id'] != $deck_id_filtre) continue; ?>
                <div class="deck-card">
                    <h3><?php echo htmlspecialchars($deck['titre']); ?></h3>
                    <p class="deck-description"><?php echo htmlspecialchars($deck['description'] ?? ''); ?></p>
                    <div class="deck-info">
                        <span class="deck-cartes"><?php echo $deck['cartes_a_reviser']; ?> cartes disponibles</span>
                    </div>
                    <div class="deck-actions">
                        <?php if ($deck['cartes_a_reviser'] > 0): ?>
                            <?php if ($deck['deja_revise_aujourdhui']): ?>
                                <span class="btn-disabled">Déjà révisé aujourd'hui</span>
                            <?php else: ?>
                                <a href="revision_session.php?deck_id=<?php echo $deck['id']; ?>" class="btn-primary">Réviser</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="btn-disabled">Aucune carte disponible</span>
                        <?php endif; ?>
                        <a href="deck_voir.php?id=<?php echo $deck['id']; ?>" class="btn-secondary">Voir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

