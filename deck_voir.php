<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

$deck_id = $_GET['id'] ?? 0;

// Récupérer le deck
if ($utilisateur['role'] === 'etudiant') {
    $requete = $connexion->prepare("
        SELECT d.*, u.nom as createur_nom
        FROM decks d
        LEFT JOIN utilisateurs u ON d.utilisateur_id = u.id
        LEFT JOIN assignations_decks a ON d.id = a.deck_id
        LEFT JOIN partages_decks p ON d.id = p.deck_id AND p.utilisateur_partage_id = ?
        WHERE d.id = ? 
        AND ((a.utilisateur_id = ? OR (a.filiere = ? AND a.annee = ?))
        OR p.id IS NOT NULL
        OR d.utilisateur_id = ?)
    ");
    $requete->execute([
        $utilisateur['id'],
        $deck_id,
        $utilisateur['id'],
        $utilisateur['filiere'],
        $utilisateur['annee'],
        $utilisateur['id']
    ]);
} else {
    $requete = $connexion->prepare("SELECT * FROM decks WHERE id = ?");
    $requete->execute([$deck_id]);
}

$deck = $requete->fetch();

if (!$deck) {
    header('Location: decks.php');
    exit;
}

// Récupérer les cartes du deck
$requete = $connexion->prepare("SELECT * FROM cartes WHERE deck_id = ? ORDER BY date_creation");
$requete->execute([$deck_id]);
$cartes = $requete->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($deck['titre']); ?> - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($deck['titre']); ?></h1>
            <div class="page-actions">
                <?php if ($deck['utilisateur_id'] == $utilisateur['id']): ?>
                    <a href="carte_ajouter.php?deck_id=<?php echo $deck_id; ?>" class="btn-primary">Ajouter une carte</a>
                    <a href="deck_modifier.php?id=<?php echo $deck_id; ?>" class="btn-secondary">Modifier</a>
                    <?php if ($utilisateur['role'] === 'etudiant'): ?>
                        <a href="partage_creer.php?deck_id=<?php echo $deck_id; ?>" class="btn-secondary">Partager</a>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="deck_imprimer.php?id=<?php echo $deck_id; ?>" class="btn-secondary" target="_blank">Imprimer</a>
                <?php if ($utilisateur['role'] === 'etudiant'): ?>
                    <a href="revision.php?deck_id=<?php echo $deck_id; ?>" class="btn-primary">Réviser ce deck</a>
                <?php endif; ?>
                <a href="decks.php" class="btn-secondary">Retour</a>
            </div>
        </div>
        
        <?php if ($deck['description']): ?>
            <p class="deck-description-full"><?php echo nl2br(htmlspecialchars($deck['description'])); ?></p>
        <?php endif; ?>
        
        <h2>Cartes (<?php echo count($cartes); ?>)</h2>
        
        <?php if (empty($cartes)): ?>
            <div class="message-info">
                <p>Aucune carte dans ce deck pour le moment.</p>
                <?php if ($deck['utilisateur_id'] == $utilisateur['id']): ?>
                    <a href="carte_ajouter.php?deck_id=<?php echo $deck_id; ?>" class="btn-primary">Ajouter la première carte</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="cartes-list">
                <?php foreach ($cartes as $index => $carte): ?>
                    <div class="carte-card">
                        <div class="carte-header">
                            <span class="carte-numero">Carte <?php echo $index + 1; ?></span>
                            <span class="carte-type"><?php echo $carte['type'] === 'qcm' ? 'QCM' : 'Texte'; ?></span>
                        </div>
                        <div class="carte-content">
                            <?php if ($carte['type'] === 'qcm'): ?>
                                <?php
                                $requete_choix = $connexion->prepare("SELECT * FROM choix_cartes WHERE carte_id = ? ORDER BY ordre");
                                $requete_choix->execute([$carte['id']]);
                                $choix = $requete_choix->fetchAll();
                                ?>
                                <div class="carte-recto">
                                    <strong>Question :</strong>
                                    <p><?php echo nl2br(htmlspecialchars($carte['texte_recto'])); ?></p>
                                </div>
                                
                                <?php if (!empty($choix)): ?>
                                    <div class="carte-choix">
                                        <strong>Réponses possibles (cochez celles que vous pensez correctes) :</strong>
                                        <ul class="choix-list-view">
                                            <?php foreach ($choix as $choix_item): ?>
                                                <li>
                                                    <span class="checkbox-view">☐</span>
                                                    <?php echo htmlspecialchars($choix_item['texte_choix']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    
                                    <div class="carte-reponses-correctes">
                                        <strong>Réponses correctes :</strong>
                                        <ul>
                                            <?php foreach ($choix as $choix_item): ?>
                                                <?php if ($choix_item['est_correct']): ?>
                                                    <li class="choix-correct">
                                                        <?php echo htmlspecialchars($choix_item['texte_choix']); ?>
                                                        <span class="badge-correct">✓ Correct</span>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="carte-recto">
                                    <strong>Recto :</strong>
                                    <p><?php echo nl2br(htmlspecialchars($carte['texte_recto'])); ?></p>
                                </div>
                                <div class="carte-verso">
                                    <strong>Verso :</strong>
                                    <p><?php echo nl2br(htmlspecialchars($carte['texte_verso'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($deck['utilisateur_id'] == $utilisateur['id']): ?>
                            <div class="carte-actions">
                                <a href="carte_modifier.php?id=<?php echo $carte['id']; ?>" class="btn-secondary">Modifier</a>
                                <a href="carte_supprimer.php?id=<?php echo $carte['id']; ?>" class="btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?');">Supprimer</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

