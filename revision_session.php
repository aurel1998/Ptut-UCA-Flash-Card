<?php
require_once 'config.php';
redirigerSiNonEtudiant();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

$deck_id = $_GET['deck_id'] ?? 0;

// Vérifier que le deck est accessible
$requete = $connexion->prepare("
    SELECT d.*
    FROM decks d
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
$deck = $requete->fetch();

if (!$deck) {
    header('Location: revision.php');
    exit;
}

// Vérifier s'il reste des cartes à réviser aujourd'hui
// On compte le nombre total de cartes et le nombre de cartes déjà révisées aujourd'hui
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

// Si toutes les cartes ont déjà été révisées aujourd'hui, on bloque
if ($stats_revision && $stats_revision['total_cartes'] > 0 && 
    $stats_revision['cartes_revisees_aujourdhui'] >= $stats_revision['total_cartes']) {
    header('Location: revision.php?message=deja_revise');
    exit;
}

// Récupérer les cartes à réviser (celles qui n'ont pas encore été révisées aujourd'hui)
// On priorise les cartes qui n'ont jamais été révisées, puis celles avec la pile la plus basse
$requete = $connexion->prepare("
    SELECT c.*, 
    COALESCE(sl.pile, 1) as pile,
    sl.id as statut_id
    FROM cartes c
    LEFT JOIN statuts_leitner sl ON c.id = sl.carte_id AND sl.utilisateur_id = ?
    LEFT JOIN historique_revisions hr ON c.id = hr.carte_id 
        AND hr.utilisateur_id = ? 
        AND hr.deck_id = ?
        AND date(hr.date_revision) = date('now')
    WHERE c.deck_id = ?
    AND hr.id IS NULL
    ORDER BY 
        CASE WHEN sl.id IS NULL THEN 0 ELSE 1 END,
        COALESCE(sl.pile, 1),
        COALESCE(sl.derniere_revision, '1970-01-01'),
        ABS(RANDOM())
    LIMIT 1
");
$requete->execute([$utilisateur['id'], $utilisateur['id'], $deck_id, $deck_id]);
$carte = $requete->fetch();

if (!$carte) {
    // Aucune carte à réviser aujourd'hui
    header('Location: revision.php?message=deja_revise');
    exit;
}

// Récupérer les choix si QCM
$choix = [];
if ($carte['type'] === 'qcm') {
    $requete_choix = $connexion->prepare("SELECT * FROM choix_cartes WHERE carte_id = ? ORDER BY ordre");
    $requete_choix->execute([$carte['id']]);
    $choix = $requete_choix->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Révision - <?php echo htmlspecialchars($deck['titre']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="revision-header">
            <h1><?php echo htmlspecialchars($deck['titre']); ?></h1>
            <p>Pile actuelle : <?php echo $carte['pile']; ?>/5</p>
        </div>
        
        <div class="revision-card">
            <div class="carte-recto-display">
                <h2>Question</h2>
                <p><?php echo nl2br(htmlspecialchars($carte['texte_recto'])); ?></p>
            </div>
            
            <form method="POST" action="revision_traiter.php" class="revision-form" id="form-revision" onsubmit="return validerFormulaire()">
                <input type="hidden" name="carte_id" value="<?php echo $carte['id']; ?>">
                <input type="hidden" name="deck_id" value="<?php echo $deck_id; ?>">
                
                <?php if ($carte['type'] === 'qcm' && !empty($choix)): ?>
                    <!-- Formulaire QCM -->
                    <div class="reponse-input-section">
                        <h3>Choisissez la ou les bonne(s) réponse(s) :</h3>
                        <p class="info-qcm">Vous pouvez sélectionner plusieurs réponses si nécessaire</p>
                        <div class="choix-qcm-revision">
                            <?php foreach ($choix as $index => $choix_item): ?>
                                <div class="choix-item-revision">
                                    <label>
                                        <input type="checkbox" name="reponse_choix[]" value="<?php echo $choix_item['id']; ?>" class="choix-checkbox">
                                        <span><?php echo htmlspecialchars($choix_item['texte_choix']); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Formulaire texte -->
                    <div class="reponse-input-section">
                        <h3>Votre réponse :</h3>
                        <textarea name="reponse_texte" id="reponse_texte" rows="5" placeholder="Tapez votre réponse ici..." required></textarea>
                    </div>
                <?php endif; ?>
                
                <div class="revision-buttons">
                    <button type="submit" class="btn-primary">Valider ma réponse</button>
                </div>
            </form>
            
            <div id="resultat-section" style="display: none;">
                <div id="resultat-message"></div>
                <div class="carte-verso-display" id="reponse-correcte-display">
                    <h3>Réponse attendue :</h3>
                    <p><?php echo nl2br(htmlspecialchars($carte['texte_verso'])); ?></p>
                </div>
                <div class="revision-buttons">
                    <a href="revision_session.php?deck_id=<?php echo $deck_id; ?>" class="btn-primary">Carte suivante</a>
                    <a href="revision.php" class="btn-secondary">Terminer la révision</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    <script>
        function validerFormulaire() {
            var typeCarte = '<?php echo $carte['type']; ?>';
            
            // Validation uniquement pour les QCM
            if (typeCarte === 'qcm') {
                var checkboxes = document.querySelectorAll('.choix-checkbox:checked');
                if (checkboxes.length === 0) {
                    alert('Veuillez sélectionner au moins une réponse');
                    return false;
                }
            } else {
                // Pour les cartes texte, vérifier que le textarea n'est pas vide
                var reponseTexte = document.getElementById('reponse_texte');
                if (reponseTexte && reponseTexte.value.trim() === '') {
                    alert('Veuillez taper votre réponse');
                    return false;
                }
            }
            
            return true;
        }
    </script>
</body>
</html>

