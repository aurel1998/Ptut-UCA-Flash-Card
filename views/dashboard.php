<?php
$titre_page = 'Tableau de bord';
?>
<div class="container">
    <h1>Tableau de bord</h1>
    <p>Bienvenue, <?php echo htmlspecialchars($utilisateur['nom']); ?> !</p>
    
    <div class="stats-grid">
        <?php if ($utilisateur['role'] === 'etudiant'): ?>
            <div class="stat-card">
                <h3>Decks disponibles</h3>
                <p class="stat-number"><?php echo $stats_decks['total']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Cartes disponibles</h3>
                <p class="stat-number"><?php echo $stats_revisions['total']; ?></p>
            </div>
        <?php else: ?>
            <div class="stat-card">
                <h3>Mes decks</h3>
                <p class="stat-number"><?php echo $stats_decks['total']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Mes cartes</h3>
                <p class="stat-number"><?php echo $stats_revisions['total']; ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="actions-grid">
        <?php if ($utilisateur['role'] === 'etudiant'): ?>
            <a href="/index.php?controller=deck&action=liste" class="action-card">
                <h3>Mes decks</h3>
                <p>Consulter tous vos decks de révision</p>
            </a>
            <a href="/index.php?controller=deck&action=partages" class="action-card">
                <h3>Decks partagés</h3>
                <p>Voir les decks partagés avec vous</p>
            </a>
            <a href="/index.php?controller=deck&action=creer" class="action-card">
                <h3>Créer un deck</h3>
                <p>Créer votre propre deck de révision</p>
            </a>
            <a href="/index.php?controller=revision&action=index" class="action-card">
                <h3>Réviser</h3>
                <p>Commencer une session de révision</p>
            </a>
            <a href="/index.php?controller=progression&action=index" class="action-card">
                <h3>Ma progression</h3>
                <p>Voir vos statistiques de révision</p>
            </a>
        <?php else: ?>
            <a href="/index.php?controller=deck&action=liste" class="action-card">
                <h3>Mes decks</h3>
                <p>Gérer vos decks de cartes</p>
            </a>
            <a href="/index.php?controller=assignation&action=index" class="action-card">
                <h3>Assignations</h3>
                <p>Assigner des decks aux étudiants</p>
            </a>
        <?php endif; ?>
    </div>
</div>

