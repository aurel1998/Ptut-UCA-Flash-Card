<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();
$erreur = '';

$deck_id = $_GET['id'] ?? 0;
$requete = $connexion->prepare("SELECT * FROM decks WHERE id = ? AND utilisateur_id = ?");
$requete->execute([$deck_id, $utilisateur['id']]);
$deck = $requete->fetch();

if (!$deck) {
    header('Location: decks.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    
    if (empty($titre)) {
        $erreur = 'Le titre est obligatoire';
    } else {
        $requete = $connexion->prepare("
            UPDATE decks 
            SET titre = ?, description = ?, tags = ?
            WHERE id = ? AND utilisateur_id = ?
        ");
        $requete->execute([$titre, $description, $tags, $deck_id, $utilisateur['id']]);
        header('Location: deck_voir.php?id=' . $deck_id);
        exit;
    }
} else {
    $titre = $deck['titre'];
    $description = $deck['description'];
    $tags = $deck['tags'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Deck - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Modifier le deck</h1>
        
        <?php if ($erreur): ?>
            <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="deck_modifier.php?id=<?php echo $deck_id; ?>" class="form-deck">
            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($titre); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="tags">Tags (séparés par des virgules)</label>
                <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($tags ?? ''); ?>" placeholder="ex: math, physique, chimie">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="deck_voir.php?id=<?php echo $deck_id; ?>" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

