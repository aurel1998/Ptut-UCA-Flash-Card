<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

// Récupérer les decks selon le rôle
if ($utilisateur['role'] === 'etudiant') {
    // Decks assignés, partagés avec l'étudiant, ou créés par l'étudiant
    $requete = $connexion->prepare("
        SELECT DISTINCT d.*, u.nom as createur_nom,
        (SELECT COUNT(*) FROM cartes WHERE deck_id = d.id) as nombre_cartes,
        CASE 
            WHEN d.utilisateur_id = ? THEN 'mes_decks'
            WHEN EXISTS (SELECT 1 FROM partages_decks WHERE deck_id = d.id AND utilisateur_partage_id = ?) THEN 'partage'
            ELSE 'autre'
        END as type_deck
        FROM decks d
        LEFT JOIN utilisateurs u ON d.utilisateur_id = u.id
        LEFT JOIN assignations_decks a ON d.id = a.deck_id
        LEFT JOIN partages_decks p ON d.id = p.deck_id AND p.utilisateur_partage_id = ?
        WHERE (a.utilisateur_id = ? OR (a.filiere = ? AND a.annee = ?))
        OR p.id IS NOT NULL
        OR d.utilisateur_id = ?
        ORDER BY 
            CASE 
                WHEN d.utilisateur_id = ? THEN 1
                WHEN p.id IS NOT NULL THEN 2
                ELSE 3
            END,
            d.date_creation DESC
    ");
    $requete->execute([
        $utilisateur['id'],
        $utilisateur['id'],
        $utilisateur['id'],
        $utilisateur['id'],
        $utilisateur['filiere'],
        $utilisateur['annee'],
        $utilisateur['id'],
        $utilisateur['id']
    ]);
} else {
    // Tous les decks de l'enseignant
    $requete = $connexion->prepare("
        SELECT d.*, 
        (SELECT COUNT(*) FROM cartes WHERE deck_id = d.id) as nombre_cartes
        FROM decks d
        WHERE d.utilisateur_id = ?
        ORDER BY d.date_creation DESC
    ");
    $requete->execute([$utilisateur['id']]);
}

$decks = $requete->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Decks - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Mes Decks</h1>
            <a href="deck_creer.php" class="btn-primary">Créer un deck</a>
        </div>
        
        <?php if (empty($decks)): ?>
            <div class="message-info">
                <p>Aucun deck disponible pour le moment.</p>
                <?php if ($utilisateur['role'] === 'enseignant'): ?>
                    <a href="deck_creer.php" class="btn-primary">Créer votre premier deck</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="decks-grid">
                <?php foreach ($decks as $deck): ?>
                    <div class="deck-card">
                        <h3><?php echo htmlspecialchars($deck['titre']); ?></h3>
                        <p class="deck-description"><?php echo htmlspecialchars($deck['description'] ?? ''); ?></p>
                        <div class="deck-info">
                            <span class="deck-cartes"><?php echo $deck['nombre_cartes']; ?> cartes</span>
                            <?php if ($utilisateur['role'] === 'etudiant'): ?>
                                <?php if (isset($deck['type_deck'])): ?>
                                    <?php if ($deck['type_deck'] === 'partage'): ?>
                                        <span class="deck-badge badge-partage">Partagé avec moi</span>
                                    <?php elseif ($deck['type_deck'] === 'mes_decks'): ?>
                                        <span class="deck-badge badge-mes-decks">Mon deck</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (isset($deck['createur_nom']) && $deck['utilisateur_id'] != $utilisateur['id']): ?>
                                    <span class="deck-createur">Par <?php echo htmlspecialchars($deck['createur_nom']); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="deck-actions">
                            <a href="deck_voir.php?id=<?php echo $deck['id']; ?>" class="btn-secondary">Voir</a>
                            <a href="deck_imprimer.php?id=<?php echo $deck['id']; ?>" class="btn-secondary" target="_blank">Imprimer</a>
                            <?php if ($deck['utilisateur_id'] == $utilisateur['id']): ?>
                                <a href="deck_modifier.php?id=<?php echo $deck['id']; ?>" class="btn-secondary">Modifier</a>
                                <a href="deck_supprimer.php?id=<?php echo $deck['id']; ?>" class="btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce deck ?');">Supprimer</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

