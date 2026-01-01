<?php
$titre_page = 'Mes Decks';
?>
<div class="container">
    <h1>Mes Decks</h1>
    
    <div class="page-actions">
        <a href="/index.php?controller=deck&action=creer" class="btn-primary">Créer un nouveau deck</a>
    </div>
    
    <div class="decks-grid">
        <?php if (empty($decks)): ?>
            <p class="message-info">Aucun deck disponible. Créez-en un pour commencer !</p>
        <?php else: ?>
            <?php foreach ($decks as $deck): ?>
                <div class="deck-card">
                    <h3><?php echo htmlspecialchars($deck['titre']); ?></h3>
                    <p class="deck-description"><?php echo htmlspecialchars($deck['description'] ?? ''); ?></p>
                    <div class="deck-info">
                        <span class="deck-cartes"><?php echo $deck['nombre_cartes'] ?? 0; ?> cartes</span>
                        <?php if (isset($deck['type_deck'])): ?>
                            <?php if ($deck['type_deck'] === 'partage'): ?>
                                <span class="badge-partage">Partagé avec moi</span>
                            <?php elseif ($deck['type_deck'] === 'mes_decks'): ?>
                                <span class="badge-mes-decks">Mon deck</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="deck-actions">
                        <a href="/index.php?controller=deck&action=voir&id=<?php echo $deck['id']; ?>" class="btn-primary">Voir</a>
                        <a href="/index.php?controller=deck&action=imprimer&id=<?php echo $deck['id']; ?>" class="btn-secondary" target="_blank">Imprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

