<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();
$erreur = '';

$carte_id = $_GET['id'] ?? 0;

// Récupérer la carte et vérifier les permissions
$requete = $connexion->prepare("
    SELECT c.*, d.utilisateur_id as deck_utilisateur_id, d.id as deck_id
    FROM cartes c
    JOIN decks d ON c.deck_id = d.id
    WHERE c.id = ? AND d.utilisateur_id = ?
");
$requete->execute([$carte_id, $utilisateur['id']]);
$carte = $requete->fetch();

if (!$carte) {
    header('Location: decks.php');
    exit;
}

// Récupérer les choix si QCM
$choix_existants = [];
if ($carte['type'] === 'qcm') {
    $requete_choix = $connexion->prepare("SELECT * FROM choix_cartes WHERE carte_id = ? ORDER BY ordre");
    $requete_choix->execute([$carte_id]);
    $choix_existants = $requete_choix->fetchAll();
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
        // Pour les QCM, on met une valeur vide
        $texte_verso = '';
    }
    
    if (empty($erreur)) {
        // Mettre à jour la carte
        $requete = $connexion->prepare("
            UPDATE cartes 
            SET type = ?, texte_recto = ?, texte_verso = ?
            WHERE id = ?
        ");
        $requete->execute([$type, $texte_recto, $texte_verso, $carte_id]);
        
        // Supprimer les anciens choix
        $requete_supp = $connexion->prepare("DELETE FROM choix_cartes WHERE carte_id = ?");
        $requete_supp->execute([$carte_id]);
        
        // Si c'est un QCM, ajouter les nouveaux choix
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
            header('Location: deck_voir.php?id=' . $carte['deck_id']);
            exit;
        }
    }
} else {
    $type = $carte['type'];
    $texte_recto = $carte['texte_recto'];
    $texte_verso = $carte['texte_verso'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la carte - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Modifier la carte</h1>
        
        <?php if ($erreur): ?>
            <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="carte_modifier.php?id=<?php echo $carte_id; ?>" class="form-carte">
            <div class="form-group">
                <label for="type">Type de carte</label>
                <select id="type" name="type" onchange="toggleChoixQCM()">
                    <option value="texte" <?php echo $type === 'texte' ? 'selected' : ''; ?>>Texte</option>
                    <option value="qcm" <?php echo $type === 'qcm' ? 'selected' : ''; ?>>QCM</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="texte_recto">Recto *</label>
                <textarea id="texte_recto" name="texte_recto" rows="4" required><?php echo htmlspecialchars($texte_recto); ?></textarea>
            </div>
            
            <div class="form-group" id="verso-group">
                <label for="texte_verso">Verso *</label>
                <textarea id="texte_verso" name="texte_verso" rows="4" required><?php echo htmlspecialchars($texte_verso); ?></textarea>
            </div>
            
            <div id="choix-qcm" style="display: <?php echo $type === 'qcm' ? 'block' : 'none'; ?>;">
                <h3>Choix pour le QCM (vous pouvez sélectionner plusieurs réponses correctes)</h3>
                <div id="choix-container">
                    <?php if (!empty($choix_existants)): ?>
                        <?php foreach ($choix_existants as $index => $choix_item): ?>
                            <div class="choix-item">
                                <input type="text" name="choix[]" value="<?php echo htmlspecialchars($choix_item['texte_choix']); ?>" placeholder="Choix <?php echo $index + 1; ?>">
                                <label><input type="checkbox" name="choix_correct[]" value="<?php echo $index; ?>" <?php echo $choix_item['est_correct'] ? 'checked' : ''; ?>> Correct</label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="choix-item">
                            <input type="text" name="choix[]" placeholder="Choix 1">
                            <label><input type="checkbox" name="choix_correct[]" value="0"> Correct</label>
                        </div>
                        <div class="choix-item">
                            <input type="text" name="choix[]" placeholder="Choix 2">
                            <label><input type="checkbox" name="choix_correct[]" value="1"> Correct</label>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="ajouterChoix()" class="btn-secondary">Ajouter un choix</button>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="deck_voir.php?id=<?php echo $carte['deck_id']; ?>" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
    
    <?php include 'footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>

