<?php
require_once 'config.php';
redirigerSiNonEnseignant();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();
$erreur = '';
$succes = '';

// Créer une assignation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'creer') {
    $deck_id = $_POST['deck_id'] ?? 0;
    $filiere = trim($_POST['filiere'] ?? '');
    $annee = trim($_POST['annee'] ?? '');
    $utilisateur_id = !empty($_POST['utilisateur_id']) ? $_POST['utilisateur_id'] : null;
    
    // Vérifier que le deck appartient à l'enseignant
    $requete = $connexion->prepare("SELECT * FROM decks WHERE id = ? AND utilisateur_id = ?");
    $requete->execute([$deck_id, $utilisateur['id']]);
    $deck = $requete->fetch();
    
    if (!$deck) {
        $erreur = 'Deck introuvable';
    } elseif (empty($filiere) && empty($annee) && empty($utilisateur_id)) {
        $erreur = 'Veuillez sélectionner une filière, une année ou un étudiant';
    } else {
        $requete = $connexion->prepare("
            INSERT INTO assignations_decks (deck_id, assigne_par, filiere, annee, utilisateur_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $requete->execute([
            $deck_id,
            $utilisateur['id'],
            $filiere ?: null,
            $annee ?: null,
            $utilisateur_id
        ]);
        $succes = 'Assignation créée avec succès';
    }
}

// Supprimer une assignation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer') {
    $assignation_id = $_POST['assignation_id'] ?? 0;
    $requete = $connexion->prepare("
        DELETE FROM assignations_decks 
        WHERE id = ? AND assigne_par = ?
    ");
    $requete->execute([$assignation_id, $utilisateur['id']]);
    $succes = 'Assignation supprimée avec succès';
}

// Récupérer les decks de l'enseignant
$requete = $connexion->prepare("SELECT * FROM decks WHERE utilisateur_id = ? ORDER BY titre");
$requete->execute([$utilisateur['id']]);
$decks = $requete->fetchAll();

// Récupérer les assignations
$requete = $connexion->prepare("
    SELECT a.*, d.titre as deck_titre, u.nom as etudiant_nom
    FROM assignations_decks a
    JOIN decks d ON a.deck_id = d.id
    LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE a.assigne_par = ?
    ORDER BY a.date_assignation DESC
");
$requete->execute([$utilisateur['id']]);
$assignations = $requete->fetchAll();

// Récupérer les étudiants pour la sélection
$requete = $connexion->prepare("SELECT * FROM utilisateurs WHERE role = 'etudiant' ORDER BY nom");
$requete->execute();
$etudiants = $requete->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignations - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Assignations de Decks</h1>
        
        <?php if ($erreur): ?>
            <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>
        <?php if ($succes): ?>
            <div class="message-succes"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>
        
        <h2>Créer une assignation</h2>
        <form method="POST" action="assignations.php" class="form-assignation">
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
                        <form method="POST" action="assignations.php" style="display: inline;">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="assignation_id" value="<?php echo $assignation['id']; ?>">
                            <button type="submit" class="btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette assignation ?');">Supprimer</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

