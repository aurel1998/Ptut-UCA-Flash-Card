<?php
$titre_page = 'Partager le deck';
?>
<div class="container">
    <h1>Partager le deck : <?php echo htmlspecialchars($deck['titre']); ?></h1>
    
    <?php if ($erreur): ?>
        <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>
    <?php if ($succes): ?>
        <div class="message-succes"><?php echo $succes; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="/index.php?controller=partage&action=creer&deck_id=<?php echo $deck_id; ?>" class="form-partage">
        <div class="form-group">
            <label for="email_partage">Adresse email de l'étudiant *</label>
            <input type="email" id="email_partage" name="email_partage" placeholder="exemple@email.com" required>
            <small>Entrez l'adresse email de l'étudiant avec qui vous souhaitez partager ce deck</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Partager</button>
            <a href="/index.php?controller=deck&action=voir&id=<?php echo $deck_id; ?>" class="btn-secondary">Retour</a>
        </div>
    </form>
    
    <h2>Partages existants</h2>
    <?php if (empty($partages)): ?>
        <div class="message-info">
            <p>Aucun partage pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="partages-list">
            <?php foreach ($partages as $partage): ?>
                <div class="partage-card">
                    <div class="partage-info">
                        <strong><?php echo htmlspecialchars($partage['nom']); ?></strong>
                        <p><?php echo htmlspecialchars($partage['email']); ?></p>
                        <small>Partagé le <?php echo date('d/m/Y à H:i', strtotime($partage['date_creation'])); ?></small>
                    </div>
                    <form method="POST" action="/index.php?controller=partage&action=supprimer" style="display: inline;">
                        <input type="hidden" name="partage_id" value="<?php echo $partage['id']; ?>">
                        <input type="hidden" name="deck_id" value="<?php echo $deck_id; ?>">
                        <button type="submit" class="btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir retirer le partage avec cet utilisateur ?');">Retirer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

