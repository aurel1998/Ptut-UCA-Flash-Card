<?php
$titre_page = 'Révision - ' . htmlspecialchars($deck['titre']);
?>
<div class="container">
    <div class="revision-header">
        <h1>Révision : <?php echo htmlspecialchars($deck['titre']); ?></h1>
        <p class="info-revision">Pile actuelle : <?php echo $carte['pile']; ?>/5</p>
    </div>
    
    <div class="revision-card">
        <div class="carte-recto-display">
            <h2>Question</h2>
            <p><?php echo nl2br(htmlspecialchars($carte['texte_recto'])); ?></p>
        </div>
        
        <form method="POST" action="/index.php?controller=revision&action=traiter" id="form-revision">
            <input type="hidden" name="carte_id" value="<?php echo $carte['id']; ?>">
            <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
            
            <?php if ($carte['type'] === 'qcm'): ?>
                <div class="choix-display">
                    <p class="info-qcm">Sélectionnez toutes les réponses correctes :</p>
                    <div class="choix-qcm-revision">
                        <?php foreach ($choix as $choix_item): ?>
                            <div class="choix-item-revision">
                                <label>
                                    <input type="checkbox" name="reponse_choix[]" value="<?php echo $choix_item['id']; ?>">
                                    <span><?php echo htmlspecialchars($choix_item['texte_choix']); ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="reponse-input-section">
                    <h3>Votre réponse :</h3>
                    <textarea name="reponse_texte" rows="6" required placeholder="Tapez votre réponse ici..."></textarea>
                </div>
            <?php endif; ?>
            
            <div class="revision-buttons">
                <button type="submit" class="btn-primary">Valider</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('form-revision').addEventListener('submit', function(e) {
    <?php if ($carte['type'] === 'qcm'): ?>
    var checkboxes = document.querySelectorAll('input[name="reponse_choix[]"]:checked');
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Veuillez sélectionner au moins une réponse');
        return false;
    }
    <?php endif; ?>
});
</script>

