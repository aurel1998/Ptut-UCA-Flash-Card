<?php
$titre_page = 'Ma Progression';
?>
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
                        <?php if (isset($item['duree_secondes']) && $item['duree_secondes']): ?>
                            <small>Durée : <?php echo gmdate('H:i:s', $item['duree_secondes']); ?></small>
                        <?php endif; ?>
                        <?php if (isset($item['taux_succes']) && $item['taux_succes'] !== null): ?>
                            <small>Taux de succès : <?php echo number_format($item['taux_succes'], 1); ?>%</small>
                        <?php endif; ?>
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

