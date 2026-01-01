<?php
$titre_page = htmlspecialchars($deck['titre']);
?>
<div class="container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($deck['titre']); ?></h1>
        <div class="page-actions">
            <?php if ($deck['utilisateur_id'] == $utilisateur['id']): ?>
                <a href="/index.php?controller=card&action=ajouter&deck_id=<?php echo $deck['id']; ?>" class="btn-primary">Ajouter une carte</a>
                <a href="/index.php?controller=deck&action=modifier&id=<?php echo $deck['id']; ?>" class="btn-secondary">Modifier</a>
                <?php if ($utilisateur['role'] === 'etudiant'): ?>
                    <a href="/index.php?controller=partage&action=creer&deck_id=<?php echo $deck['id']; ?>" class="btn-secondary">Partager</a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="/index.php?controller=deck&action=imprimer&id=<?php echo $deck['id']; ?>" class="btn-secondary" target="_blank">Imprimer</a>
            <?php if ($utilisateur['role'] === 'etudiant'): ?>
                <a href="/index.php?controller=revision&action=index&deck_id=<?php echo $deck['id']; ?>" class="btn-primary">Réviser ce deck</a>
            <?php endif; ?>
            <a href="/index.php?controller=deck&action=liste" class="btn-secondary">Retour</a>
        </div>
    </div>
    
    <?php if ($deck['description']): ?>
        <p class="deck-description-full"><?php echo nl2br(htmlspecialchars($deck['description'])); ?></p>
    <?php endif; ?>
    
    <div class="cartes-list">
        <?php if (empty($cartes)): ?>
            <p class="message-info">Aucune carte dans ce deck. Ajoutez-en une pour commencer !</p>
        <?php else: ?>
            <?php foreach ($cartes as $carte): ?>
                <div class="carte-item">
                    <div class="carte-header">
                        <span class="carte-type"><?php echo $carte['type'] === 'qcm' ? 'QCM' : 'Texte'; ?></span>
                        <?php if ($deck['utilisateur_id'] == $utilisateur['id']): ?>
                            <div class="carte-actions">
                                <a href="/index.php?controller=card&action=modifier&id=<?php echo $carte['id']; ?>" class="btn-small">Modifier</a>
                                <a href="/index.php?controller=card&action=supprimer&id=<?php echo $carte['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?');">Supprimer</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="carte-content">
                        <div class="carte-question">
                            <strong>Question :</strong>
                            <p><?php echo nl2br(htmlspecialchars($carte['texte_recto'])); ?></p>
                        </div>
                        
                        <?php if ($carte['type'] === 'qcm'): ?>
                            <?php if (!empty($carte['choix'])): ?>
                                <?php $choix = $carte['choix']; ?>
                                <div class="carte-choix">
                                    <strong>Choix possibles :</strong>
                                    <ul>
                                        <?php foreach ($choix as $choix_item): ?>
                                            <li>
                                                <input type="checkbox" disabled <?php echo $choix_item['est_correct'] ? 'checked' : ''; ?>>
                                                <span><?php echo htmlspecialchars($choix_item['texte_choix']); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="carte-choix-correctes">
                                    <strong>Réponses correctes :</strong>
                                    <ul>
                                        <?php foreach ($choix as $choix_item): ?>
                                            <?php if ($choix_item['est_correct']): ?>
                                                <li>✓ <?php echo htmlspecialchars($choix_item['texte_choix']); ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="carte-reponse">
                                <strong>Réponse :</strong>
                                <p><?php echo nl2br(htmlspecialchars($carte['texte_verso'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

