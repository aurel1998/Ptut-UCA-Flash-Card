<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    
    if (empty($titre)) {
        $erreur = 'Le titre est obligatoire';
    } else {
        $requete = $connexion->prepare("
            INSERT INTO decks (utilisateur_id, titre, description, visibilite, tags)
            VALUES (?, ?, ?, 'prive', ?)
        ");
        $requete->execute([$utilisateur['id'], $titre, $description, $tags]);
        $deck_id = $connexion->lastInsertId();
        header('Location: deck_voir.php?id=' . $deck_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Deck - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Créer un nouveau deck</h1>
        
        <?php if ($erreur): ?>
            <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="deck_creer.php" class="form-deck">
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
                <a href="decks.php" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

