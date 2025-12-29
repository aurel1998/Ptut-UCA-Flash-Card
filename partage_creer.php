<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();
$erreur = '';
$succes = '';

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
    $email_partage = trim($_POST['email_partage'] ?? '');
    
    if (empty($email_partage)) {
        $erreur = 'Veuillez entrer une adresse email';
    } else {
        // Vérifier si l'utilisateur existe
        $requete = $connexion->prepare("SELECT id, nom FROM utilisateurs WHERE email = ?");
        $requete->execute([$email_partage]);
        $utilisateur_partage = $requete->fetch();
        
        if (!$utilisateur_partage) {
            $erreur = 'Aucun utilisateur trouvé avec cet email';
        } elseif ($utilisateur_partage['id'] == $utilisateur['id']) {
            $erreur = 'Vous ne pouvez pas partager un deck avec vous-même';
        } else {
            // Vérifier si le partage existe déjà
            $requete = $connexion->prepare("
                SELECT id FROM partages_decks 
                WHERE deck_id = ? AND utilisateur_partage_id = ?
            ");
            $requete->execute([$deck_id, $utilisateur_partage['id']]);
            $partage_existant = $requete->fetch();
            
            if ($partage_existant) {
                $erreur = 'Ce deck a déjà été partagé avec cet utilisateur';
            } else {
                // Créer le partage
                $requete = $connexion->prepare("
                    INSERT INTO partages_decks (deck_id, utilisateur_partage_id, cree_par)
                    VALUES (?, ?, ?)
                ");
                $requete->execute([$deck_id, $utilisateur_partage['id'], $utilisateur['id']]);
                $succes = 'Deck partagé avec succès avec ' . htmlspecialchars($utilisateur_partage['nom']);
            }
        }
    }
}

// Récupérer les partages existants
$requete = $connexion->prepare("
    SELECT p.*, u.nom, u.email
    FROM partages_decks p
    JOIN utilisateurs u ON p.utilisateur_partage_id = u.id
    WHERE p.deck_id = ? AND p.cree_par = ?
    ORDER BY p.date_creation DESC
");
$requete->execute([$deck_id, $utilisateur['id']]);
$partages = $requete->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partager le deck - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Partager le deck : <?php echo htmlspecialchars($deck['titre']); ?></h1>
        
        <?php if ($erreur): ?>
            <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>
        <?php if ($succes): ?>
            <div class="message-succes"><?php echo $succes; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="partage_creer.php?deck_id=<?php echo $deck_id; ?>" class="form-partage">
            <div class="form-group">
                <label for="email_partage">Adresse email de l'étudiant *</label>
                <input type="email" id="email_partage" name="email_partage" placeholder="exemple@email.com" required>
                <small>Entrez l'adresse email de l'étudiant avec qui vous souhaitez partager ce deck</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Partager</button>
                <a href="deck_voir.php?id=<?php echo $deck_id; ?>" class="btn-secondary">Retour</a>
            </div>
        </form>
        
        <h2>Partages existants</h2>
        <?php if (empty($partages)): ?>
            <div class="message-info">
                <p>Aucun partage pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="partages-list">
                <?php foreach ($partages as $partage): ?>
                    <div class="partage-card">
                        <div class="partage-info">
                            <strong><?php echo htmlspecialchars($partage['nom']); ?></strong>
                            <p><?php echo htmlspecialchars($partage['email']); ?></p>
                            <small>Partagé le <?php echo date('d/m/Y à H:i', strtotime($partage['date_creation'])); ?></small>
                        </div>
                        <form method="POST" action="partage_supprimer.php" style="display: inline;">
                            <input type="hidden" name="partage_id" value="<?php echo $partage['id']; ?>">
                            <input type="hidden" name="deck_id" value="<?php echo $deck_id; ?>">
                            <button type="submit" class="btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir retirer le partage avec cet utilisateur ?');">Retirer</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

