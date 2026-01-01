<?php
$titre_page = 'Modifier une carte';
?>
<div class="container">
    <h1>Modifier une carte</h1>
    <p>Deck : <strong><?php echo htmlspecialchars($deck['titre']); ?></strong></p>
    
    <?php if (isset($erreur) && $erreur): ?>
        <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="/index.php?controller=card&action=modifier&id=<?php echo $carte['id']; ?>" class="form-carte" id="form-carte">
        <div class="form-group">
            <label for="type">Type de carte</label>
            <select id="type" name="type" disabled>
                <option value="texte" <?php echo $carte['type'] === 'texte' ? 'selected' : ''; ?>>Texte</option>
                <option value="qcm" <?php echo $carte['type'] === 'qcm' ? 'selected' : ''; ?>>QCM</option>
            </select>
            <small>Le type de carte ne peut pas être modifié</small>
        </div>
        
        <div class="form-group">
            <label for="question">Question *</label>
            <textarea id="question" name="question" rows="4" required><?php echo htmlspecialchars($carte['texte_recto']); ?></textarea>
        </div>
        
        <?php if ($carte['type'] === 'texte'): ?>
            <div class="form-group" id="reponse-group">
                <label for="reponse">Réponse *</label>
                <textarea id="reponse" name="reponse" rows="4" required><?php echo htmlspecialchars($carte['texte_verso']); ?></textarea>
            </div>
        <?php else: ?>
            <div id="choix-qcm">
                <h3>Choix pour le QCM (vous pouvez sélectionner plusieurs réponses correctes)</h3>
                <div id="choix-container">
                    <?php foreach ($choix as $index => $choix_item): ?>
                        <div class="choix-item">
                            <input type="text" name="choix[]" value="<?php echo htmlspecialchars($choix_item['texte_choix']); ?>" placeholder="Choix <?php echo $index + 1; ?>">
                            <label><input type="checkbox" name="choix_correct[]" value="<?php echo $index; ?>" <?php echo $choix_item['est_correct'] ? 'checked' : ''; ?>> Correct</label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" onclick="ajouterChoix()" class="btn-secondary">Ajouter un choix</button>
            </div>
        <?php endif; ?>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Enregistrer les modifications</button>
            <a href="/index.php?controller=deck&action=voir&id=<?php echo $deck['id']; ?>" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
<script src="/script.js"></script>
<script>
function ajouterChoix() {
    var container = document.getElementById('choix-container');
    var index = container.children.length;
    var div = document.createElement('div');
    div.className = 'choix-item';
    div.innerHTML = '<input type="text" name="choix[]" placeholder="Choix ' + (index + 1) + '">' +
                    '<label><input type="checkbox" name="choix_correct[]" value="' + index + '"> Correct</label>';
    container.appendChild(div);
}
</script>

