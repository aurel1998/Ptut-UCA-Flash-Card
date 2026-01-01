<?php
$titre_page = 'Résultat de la révision';
$est_correct = $resultat === 'correct';
?>
<div class="container">
    <div class="revision-header">
        <h1>Résultat</h1>
    </div>
    
    <div class="revision-card">
        <div class="resultat-message <?php echo $est_correct ? 'resultat-correct' : 'resultat-incorrect'; ?>">
            <h2><?php echo $est_correct ? '✓ Correct !' : '✗ Incorrect'; ?></h2>
            <p>Vous êtes maintenant dans la pile <?php echo $nouvelle_pile; ?>/5</p>
        </div>
        
        <div class="carte-recto-display">
            <h2>Question</h2>
            <p><?php echo nl2br(htmlspecialchars($carte['texte_recto'])); ?></p>
        </div>
        
        <?php if ($carte['type'] === 'qcm'): ?>
            <?php
            require_once __DIR__ . '/../../models/Card.php';
            $cardModel = new Card();
            $tous_choix = $cardModel->obtenirChoix($carte['id']);
            ?>
            <div class="choix-display">
                <h3>Vos réponses :</h3>
                <p><?php echo htmlspecialchars($reponse_utilisateur ?: 'Aucune réponse'); ?></p>
                
                <h3>Réponses correctes :</h3>
                <ul>
                    <?php foreach ($tous_choix as $choix_item): ?>
                        <?php if ($choix_item['est_correct']): ?>
                            <li>✓ <?php echo htmlspecialchars($choix_item['texte_choix']); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="carte-verso-display">
                <h3>Réponse attendue :</h3>
                <p><?php echo nl2br(htmlspecialchars($carte['texte_verso'])); ?></p>
            </div>
            
            <?php if ($reponse_utilisateur): ?>
                <div class="reponse-utilisateur">
                    <strong>Votre réponse :</strong>
                    <p><?php echo nl2br(htmlspecialchars($reponse_utilisateur)); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="revision-buttons">
            <?php if ($reste_cartes): ?>
                <a href="/index.php?controller=revision&action=session&session_id=<?php echo $session['id']; ?>" class="btn-primary">Carte suivante</a>
            <?php else: ?>
                <div class="message-succes">
                    <p>Félicitations ! Vous avez terminé la révision de ce deck.</p>
                </div>
                <a href="/index.php?controller=revision&action=index" class="btn-primary">Retour aux decks</a>
            <?php endif; ?>
        </div>
    </div>
</div>

