<?php
$titre_page = 'Révision';
?>
<div class="container">
    <h1>Choisir un deck à réviser</h1>
    
    <?php if (empty($decks)): ?>
        <p class="message-info">Aucun deck disponible pour la révision.</p>
    <?php else: ?>
        <div class="decks-grid">
            <?php foreach ($decks as $deck): ?>
                <div class="deck-card">
                    <h3><?php echo htmlspecialchars($deck['titre']); ?></h3>
                    <?php if ($deck['description']): ?>
                        <p class="deck-description"><?php echo htmlspecialchars($deck['description']); ?></p>
                    <?php endif; ?>
                    <div class="deck-info">
                        <span class="deck-cartes"><?php echo $deck['cartes_a_reviser']; ?> carte(s)</span>
                    </div>
                    <div class="deck-actions">
                        <?php if ($deck['session_en_cours']): ?>
                            <a href="/index.php?controller=revision&action=demarrer&deck_id=<?php echo $deck['id']; ?>&reprendre=1" class="btn-primary">Reprendre la session</a>
                        <?php else: ?>
                            <a href="/index.php?controller=revision&action=demarrer&deck_id=<?php echo $deck['id']; ?>" class="btn-primary">Commencer la révision</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

