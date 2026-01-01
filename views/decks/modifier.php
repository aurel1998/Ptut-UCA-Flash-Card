<?php
$titre_page = 'Modifier le deck';
?>
<div class="container">
    <h1>Modifier le deck</h1>
    
    <?php if (isset($erreur) && $erreur): ?>
        <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="/index.php?controller=deck&action=modifier&id=<?php echo $deck['id']; ?>" class="form-deck">
        <div class="form-group">
            <label for="titre">Titre *</label>
            <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($deck['titre']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($deck['description'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="tags">Tags (séparés par des virgules)</label>
            <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($deck['tags'] ?? ''); ?>" placeholder="ex: math, physique, chimie">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Enregistrer les modifications</button>
            <a href="/index.php?controller=deck&action=voir&id=<?php echo $deck['id']; ?>" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>

