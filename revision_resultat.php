<?php
require_once 'config.php';
redirigerSiNonEtudiant();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

$carte_id = $_GET['carte_id'] ?? 0;
$deck_id = $_GET['deck_id'] ?? 0;

// Récupérer le résultat depuis la session
$resultat = $_SESSION['revision_resultat'] ?? null;
$reponse_utilisateur = $_SESSION['revision_reponse_utilisateur'] ?? '';

if (!$resultat) {
    header('Location: revision.php');
    exit;
}

// Nettoyer la session
unset($_SESSION['revision_resultat']);
unset($_SESSION['revision_reponse_utilisateur']);

// Récupérer la carte et le deck
$requete = $connexion->prepare("SELECT * FROM cartes WHERE id = ?");
$requete->execute([$carte_id]);
$carte = $requete->fetch();

$requete = $connexion->prepare("SELECT * FROM decks WHERE id = ?");
$requete->execute([$deck_id]);
$deck = $requete->fetch();

if (!$carte || !$deck) {
    header('Location: revision.php');
    exit;
}

// Récupérer le statut Leitner pour afficher la nouvelle pile
$requete = $connexion->prepare("SELECT pile FROM statuts_leitner WHERE utilisateur_id = ? AND carte_id = ?");
$requete->execute([$utilisateur['id'], $carte_id]);
$statut = $requete->fetch();
$nouvelle_pile = $statut ? $statut['pile'] : 1;

// Vérifier s'il reste des cartes à réviser aujourd'hui
$requete = $connexion->prepare("
    SELECT 
        COUNT(DISTINCT c.id) as total_cartes,
        COUNT(DISTINCT hr.carte_id) as cartes_revisees_aujourdhui
    FROM cartes c
    LEFT JOIN historique_revisions hr ON c.id = hr.carte_id 
        AND hr.utilisateur_id = ? 
        AND hr.deck_id = ?
        AND date(hr.date_revision) = date('now')
    WHERE c.deck_id = ?
");
$requete->execute([$utilisateur['id'], $deck_id, $deck_id]);
$stats_revision = $requete->fetch();
$reste_cartes = $stats_revision && $stats_revision['total_cartes'] > $stats_revision['cartes_revisees_aujourdhui'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat - <?php echo htmlspecialchars($deck['titre']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="revision-header">
            <h1><?php echo htmlspecialchars($deck['titre']); ?></h1>
            <p>Nouvelle pile : <?php echo $nouvelle_pile; ?>/5</p>
        </div>
        
        <div class="revision-card">
            <div class="carte-recto-display">
                <h2>Question</h2>
                <p><?php echo nl2br(htmlspecialchars($carte['texte_recto'])); ?></p>
            </div>
            
            <div class="resultat-message <?php echo $resultat === 'correct' ? 'resultat-correct' : 'resultat-incorrect'; ?>">
                <?php if ($resultat === 'correct'): ?>
                    <h2>✓ Réponse correcte !</h2>
                    <p>Félicitations ! Votre réponse est correcte.</p>
                <?php else: ?>
                    <h2>✗ Réponse incorrecte</h2>
                    <p>Votre réponse n'est pas correcte. Continuez à réviser !</p>
                <?php endif; ?>
                
                <?php if (!empty($reponse_utilisateur)): ?>
                    <div class="reponse-utilisateur">
                        <strong>Votre réponse :</strong>
                        <p><?php echo nl2br(htmlspecialchars($reponse_utilisateur)); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($carte['type'] === 'texte'): ?>
                <div class="carte-verso-display">
                    <h3>Réponse attendue :</h3>
                    <p><?php echo nl2br(htmlspecialchars($carte['texte_verso'])); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($carte['type'] === 'qcm'): ?>
                <?php
                $requete_choix = $connexion->prepare("SELECT * FROM choix_cartes WHERE carte_id = ? ORDER BY ordre");
                $requete_choix->execute([$carte_id]);
                $choix = $requete_choix->fetchAll();
                ?>
                <?php if (!empty($choix)): ?>
                    <div class="choix-display">
                        <h3>Réponses correctes attendues :</h3>
                        <ul>
                            <?php foreach ($choix as $choix_item): ?>
                                <li class="<?php echo $choix_item['est_correct'] ? 'choix-correct' : ''; ?>">
                                    <?php echo htmlspecialchars($choix_item['texte_choix']); ?>
                                    <?php if ($choix_item['est_correct']): ?>
                                        <span class="badge-correct">✓ Correct</span>
                                    <?php else: ?>
                                        <span class="badge-incorrect">✗ Incorrect</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="revision-buttons">
                <?php if ($reste_cartes): ?>
                    <a href="revision_session.php?deck_id=<?php echo $deck_id; ?>" class="btn-primary">Carte suivante</a>
                <?php else: ?>
                    <div class="message-succes">
                        <p>Félicitations ! Vous avez terminé la révision de ce deck pour aujourd'hui.</p>
                    </div>
                <?php endif; ?>
                <a href="revision.php" class="btn-secondary">Terminer la révision</a>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

