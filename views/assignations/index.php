<?php
$titre_page = 'Assignations de Decks';
?>
<div class="container">
    <h1>Assignations de Decks</h1>
    
    <?php if ($erreur): ?>
        <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>
    <?php if ($succes): ?>
        <div class="message-succes"><?php echo htmlspecialchars($succes); ?></div>
    <?php endif; ?>
    
    <h2>Créer une assignation</h2>
    <form method="POST" action="/index.php?controller=assignation&action=index" class="form-assignation">
        <input type="hidden" name="action" value="creer">
        <div class="form-group">
            <label for="deck_id">Deck *</label>
            <select id="deck_id" name="deck_id" required>
                <option value="">-- Sélectionner un deck --</option>
                <?php foreach ($decks as $deck): ?>
                    <option value="<?php echo $deck['id']; ?>"><?php echo htmlspecialchars($deck['titre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="filiere">Filière (optionnel)</label>
            <select id="filiere" name="filiere">
                <option value="">-- Sélectionner --</option>
                <option value="VCOD">VCOD</option>
                <option value="EMS">EMS</option>
                <option value="BIOSTAT">BIOSTAT</option>
                <option value="GEA">GEA</option>
            </select>
        </div>
        <div class="form-group">
            <label for="annee">Année (optionnel)</label>
            <select id="annee" name="annee">
                <option value="">-- Sélectionner --</option>
                <option value="1ère année">1ère année</option>
                <option value="2ème année">2ème année</option>
                <option value="3ème année">3ème année</option>
            </select>
        </div>
        <div class="form-group">
            <label for="utilisateur_id">Étudiant spécifique (optionnel)</label>
            <select id="utilisateur_id" name="utilisateur_id">
                <option value="">-- Sélectionner un étudiant --</option>
                <?php foreach ($etudiants as $etudiant): ?>
                    <option value="<?php echo $etudiant['id']; ?>"><?php echo htmlspecialchars($etudiant['nom']); ?> (<?php echo htmlspecialchars($etudiant['email']); ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-primary">Créer l'assignation</button>
    </form>
    
    <h2>Assignations existantes</h2>
    <?php if (empty($assignations)): ?>
        <div class="message-info">
            <p>Aucune assignation pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="assignations-list">
            <?php foreach ($assignations as $assignation): ?>
                <div class="assignation-card">
                    <h3><?php echo htmlspecialchars($assignation['deck_titre']); ?></h3>
                    <div class="assignation-info">
                        <?php if ($assignation['utilisateur_id']): ?>
                            <p><strong>Étudiant :</strong> <?php echo htmlspecialchars($assignation['etudiant_nom']); ?></p>
                        <?php else: ?>
                            <p><strong>Filière :</strong> <?php echo htmlspecialchars($assignation['filiere'] ?? 'Toutes'); ?></p>
                            <p><strong>Année :</strong> <?php echo htmlspecialchars($assignation['annee'] ?? 'Toutes'); ?></p>
                        <?php endif; ?>
                        <p><small>Assigné le <?php echo date('d/m/Y H:i', strtotime($assignation['date_assignation'])); ?></small></p>
                    </div>
                    <form method="POST" action="/index.php?controller=assignation&action=index" style="display: inline;">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="assignation_id" value="<?php echo $assignation['id']; ?>">
                        <button type="submit" class="btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette assignation ?');">Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

