<?php
$titre_page = 'Decks Partagés';
?>
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
                        <a href="/index.php?controller=deck&action=voir&id=<?php echo $deck['id']; ?>" class="btn-secondary">Voir</a>
                        <a href="/index.php?controller=revision&action=demarrer&deck_id=<?php echo $deck['id']; ?>" class="btn-primary">Réviser</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

