<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();
$erreur = '';

$deck_id = $_GET['deck_id'] ?? 0;

// Vérifier que le deck appartient à l'utilisateur
$requete = $connexion->prepare("SELECT * FROM decks WHERE id = ? AND utilisateur_id = ?");
$requete->execute([$deck_id, $utilisateur['id']]);
$deck = $requete->fetch();

if (!$deck) {
    header('Location: decks.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'texte';
    $texte_recto = trim($_POST['texte_recto'] ?? '');
    $texte_verso = trim($_POST['texte_verso'] ?? '');
    
    // Validation selon le type
    if (empty($texte_recto)) {
        $erreur = 'Le recto est obligatoire';
    } elseif ($type === 'texte' && empty($texte_verso)) {
        $erreur = 'Le verso est obligatoire pour les cartes texte';
    } elseif ($type === 'qcm' && empty($texte_verso)) {
        // Pour les QCM, on met une valeur vide ou une valeur par défaut
        $texte_verso = '';
    }
    
    if (empty($erreur)) {
        // Créer la carte
        $requete = $connexion->prepare("
            INSERT INTO cartes (deck_id, type, texte_recto, texte_verso)
            VALUES (?, ?, ?, ?)
        ");
        $requete->execute([$deck_id, $type, $texte_recto, $texte_verso]);
        $carte_id = $connexion->lastInsertId();
        
        // Si c'est un QCM, ajouter les choix
        if ($type === 'qcm') {
            $choix = $_POST['choix'] ?? [];
            $choix_corrects = $_POST['choix_correct'] ?? []; // Tableau pour plusieurs réponses correctes
            
            if (empty($choix)) {
                $erreur = 'Veuillez ajouter au moins un choix';
            } elseif (empty($choix_corrects)) {
                $erreur = 'Veuillez sélectionner au moins une réponse correcte';
            } else {
                foreach ($choix as $index => $texte_choix) {
                    if (!empty(trim($texte_choix))) {
                        // Vérifier si ce choix est dans la liste des réponses correctes
                        $est_correct = in_array($index, $choix_corrects) ? 1 : 0;
                        $requete_choix = $connexion->prepare("
                            INSERT INTO choix_cartes (carte_id, texte_choix, est_correct, ordre)
                            VALUES (?, ?, ?, ?)
                        ");
                        $requete_choix->execute([$carte_id, trim($texte_choix), $est_correct, $index]);
                    }
                }
            }
        }
        
        if (empty($erreur)) {
            header('Location: deck_voir.php?id=' . $deck_id);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une carte - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Ajouter une carte</h1>
        <p>Deck : <strong><?php echo htmlspecialchars($deck['titre']); ?></strong></p>
        
        <?php if ($erreur): ?>
            <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="carte_ajouter.php?deck_id=<?php echo $deck_id; ?>" class="form-carte">
            <div class="form-group">
                <label for="type">Type de carte</label>
                <select id="type" name="type" onchange="toggleChoixQCM()">
                    <option value="texte">Texte</option>
                    <option value="qcm">QCM</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="texte_recto">Recto *</label>
                <textarea id="texte_recto" name="texte_recto" rows="4" required></textarea>
            </div>
            
            <div class="form-group" id="verso-group">
                <label for="texte_verso">Verso *</label>
                <textarea id="texte_verso" name="texte_verso" rows="4" required></textarea>
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
                <a href="deck_voir.php?id=<?php echo $deck_id; ?>" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
    
    <?php include 'footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>

