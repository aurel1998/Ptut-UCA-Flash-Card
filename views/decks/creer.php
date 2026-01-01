<?php
$titre_page = 'Créer un nouveau deck';
?>
<div class="container">
    <h1>Créer un nouveau deck</h1>
    
    <?php if (isset($erreur) && $erreur): ?>
        <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="/index.php?controller=deck&action=creer" class="form-deck">
        <div class="form-group">
            <label for="titre">Titre *</label>
            <input type="text" id="titre" name="titre" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        
        <div class="form-group">
            <label for="tags">Tags (séparés par des virgules)</label>
            <input type="text" id="tags" name="tags" placeholder="ex: math, physique, chimie">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Créer le deck</button>
            <a href="/index.php?controller=deck&action=liste" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>

