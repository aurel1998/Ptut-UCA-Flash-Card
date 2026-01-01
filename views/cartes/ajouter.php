<?php
$titre_page = 'Ajouter une carte';
?>
<div class="container">
    <h1>Ajouter une carte</h1>
    <p>Deck : <strong><?php echo htmlspecialchars($deck['titre']); ?></strong></p>
    
    <?php if (isset($erreur) && $erreur): ?>
        <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="/index.php?controller=card&action=ajouter&deck_id=<?php echo $deck['id']; ?>" class="form-carte" id="form-carte">
        <div class="form-group">
            <label for="type">Type de carte</label>
            <select id="type" name="type" onchange="toggleChoixQCM()">
                <option value="texte">Texte</option>
                <option value="qcm">QCM</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="question">Question *</label>
            <textarea id="question" name="question" rows="4" required></textarea>
        </div>
        
        <div class="form-group" id="reponse-group">
            <label for="reponse">Réponse *</label>
            <textarea id="reponse" name="reponse" rows="4" required></textarea>
        </div>
        
        <div id="choix-qcm" style="display: none;">
            <h3>Choix pour le QCM (vous pouvez sélectionner plusieurs réponses correctes)</h3>
            <div id="choix-container">
                <div class="choix-item">
                    <input type="text" name="choix[]" placeholder="Choix 1">
                    <label><input type="checkbox" name="choix_correct[]" value="0"> Correct</label>
                </div>
                <div class="choix-item">
                    <input type="text" name="choix[]" placeholder="Choix 2">
                    <label><input type="checkbox" name="choix_correct[]" value="1"> Correct</label>
                </div>
            </div>
            <button type="button" onclick="ajouterChoix()" class="btn-secondary">Ajouter un choix</button>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Ajouter la carte</button>
            <a href="/index.php?controller=deck&action=voir&id=<?php echo $deck['id']; ?>" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
<script src="/script.js"></script>
<script>
function toggleChoixQCM() {
    var type = document.getElementById('type').value;
    var reponseGroup = document.getElementById('reponse-group');
    var choixQCM = document.getElementById('choix-qcm');
    
    if (type === 'qcm') {
        reponseGroup.style.display = 'none';
        choixQCM.style.display = 'block';
        document.getElementById('reponse').removeAttribute('required');
    } else {
        reponseGroup.style.display = 'block';
        choixQCM.style.display = 'none';
        document.getElementById('reponse').setAttribute('required', 'required');
    }
}
</script>

