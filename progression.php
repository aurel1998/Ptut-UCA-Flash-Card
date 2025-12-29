<?php
require_once 'config.php';
redirigerSiNonEtudiant();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

// Statistiques globales
$requete = $connexion->prepare("
    SELECT 
    COUNT(DISTINCT sl.carte_id) as total_cartes_etudiees,
    SUM(CASE WHEN sl.pile = 5 THEN 1 ELSE 0 END) as cartes_maitrisees
    FROM statuts_leitner sl
    WHERE sl.utilisateur_id = ?
");
$requete->execute([$utilisateur['id']]);
$stats_globales = $requete->fetch();

// Total révisions
$requete = $connexion->prepare("SELECT COUNT(*) as total_revisions FROM historique_revisions WHERE utilisateur_id = ?");
$requete->execute([$utilisateur['id']]);
$stats_revisions = $requete->fetch();
$stats_globales['total_revisions'] = $stats_revisions['total_revisions'];

// Répartitions par pile
$requete = $connexion->prepare("
    SELECT pile, COUNT(*) as nombre
    FROM statuts_leitner
    WHERE utilisateur_id = ?
    GROUP BY pile
    ORDER BY pile
");
$requete->execute([$utilisateur['id']]);
$repartition_piles = $requete->fetchAll();

// Historique récent
$requete = $connexion->prepare("
    SELECT hr.*, c.texte_recto, d.titre as deck_titre
    FROM historique_revisions hr
    JOIN cartes c ON hr.carte_id = c.id
    JOIN decks d ON hr.deck_id = d.id
    WHERE hr.utilisateur_id = ?
    ORDER BY hr.date_revision DESC
    LIMIT 20
");
$requete->execute([$utilisateur['id']]);
$historique = $requete->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Progression - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Ma Progression</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Cartes étudiées</h3>
                <p class="stat-number"><?php echo $stats_globales['total_cartes_etudiees'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <h3>Cartes maîtrisées</h3>
                <p class="stat-number"><?php echo $stats_globales['cartes_maitrisees'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total révisions</h3>
                <p class="stat-number"><?php echo $stats_globales['total_revisions'] ?? 0; ?></p>
            </div>
        </div>
        
        <h2>Répartition par pile</h2>
        <div class="piles-grid">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php
                $nombre = 0;
                foreach ($repartition_piles as $rep) {
                    if ($rep['pile'] == $i) {
                        $nombre = $rep['nombre'];
                        break;
                    }
                }
                ?>
                <div class="pile-card">
                    <h3>Pile <?php echo $i; ?></h3>
                    <p class="stat-number"><?php echo $nombre; ?></p>
                </div>
            <?php endfor; ?>
        </div>
        
        <h2>Historique récent</h2>
        <?php if (empty($historique)): ?>
            <div class="message-info">
                <p>Aucune révision effectuée pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="historique-list">
                <?php foreach ($historique as $item): ?>
                    <div class="historique-item">
                        <div class="historique-info">
                            <strong><?php echo htmlspecialchars($item['deck_titre']); ?></strong>
                            <p><?php echo htmlspecialchars(substr($item['texte_recto'], 0, 100)); ?>...</p>
                        </div>
                        <div class="historique-resultat">
                            <span class="badge-<?php echo $item['resultat'] === 'correct' ? 'success' : 'danger'; ?>">
                                <?php echo $item['resultat'] === 'correct' ? '✓ Correct' : '✗ Incorrect'; ?>
                            </span>
                            <small><?php echo date('d/m/Y H:i', strtotime($item['date_revision'])); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

