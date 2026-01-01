<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimer - <?php echo htmlspecialchars($deck['titre']); ?></title>
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="/print.css">
</head>
<body>
    <div class="print-container">
        <div class="print-header">
            <h1><?php echo htmlspecialchars($deck['titre']); ?></h1>
            <?php if ($deck['description']): ?>
                <p class="deck-description-print"><?php echo nl2br(htmlspecialchars($deck['description'])); ?></p>
            <?php endif; ?>
            <?php if (isset($deck['createur_nom'])): ?>
                <p class="deck-createur-print">Créé par : <?php echo htmlspecialchars($deck['createur_nom']); ?></p>
            <?php endif; ?>
            <p class="deck-date-print">Date d'impression : <?php echo date('d/m/Y à H:i'); ?></p>
        </div>
        
        <div class="cartes-print">
            <?php foreach ($cartes as $index => $carte): ?>
                <div class="carte-print">
                    <div class="carte-print-header">
                        <span class="carte-numero-print">Carte <?php echo $index + 1; ?> / <?php echo count($cartes); ?></span>
                        <?php if ($carte['type'] === 'qcm'): ?>
                            <span class="carte-type-print">QCM</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="carte-print-content">
                        <?php if ($carte['type'] === 'qcm'): ?>
                            <div class="carte-recto-print">
                                <h3>Question</h3>
                                <p><?php echo nl2br(htmlspecialchars($carte['texte_recto'])); ?></p>
                            </div>
                            
                            <?php if (!empty($carte['choix'])): ?>
                                <div class="carte-choix-print">
                                    <h3>Réponses possibles (cochez celles que vous pensez correctes) :</h3>
                                    <ul class="choix-print-list">
                                        <?php foreach ($carte['choix'] as $choix_item): ?>
                                            <li>
                                                <span class="checkbox-print">☐</span>
                                                <?php echo htmlspecialchars($choix_item['texte_choix']); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <div class="carte-reponses-correctes-print">
                                    <h3>Réponses correctes :</h3>
                                    <ul>
                                        <?php foreach ($carte['choix'] as $choix_item): ?>
                                            <?php if ($choix_item['est_correct']): ?>
                                                <li class="choix-correct-print">
                                                    ✓ <?php echo htmlspecialchars($choix_item['texte_choix']); ?>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="carte-recto-print">
                                <h3>Question</h3>
                                <p><?php echo nl2br(htmlspecialchars($carte['texte_recto'])); ?></p>
                            </div>
                            
                            <div class="carte-verso-print">
                                <h3>Réponse</h3>
                                <p><?php echo nl2br(htmlspecialchars($carte['texte_verso'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="print-footer">
            <p>UCA Flash Card - Plateforme de Révision</p>
            <p>Université Clermont-Auvergne</p>
        </div>
    </div>
    
    <div class="print-actions">
        <button onclick="window.print()" class="btn-primary">Imprimer</button>
        <a href="/index.php?controller=deck&action=voir&id=<?php echo $deck_id; ?>" class="btn-secondary">Retour</a>
    </div>
</body>
</html>

