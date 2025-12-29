<?php
require_once 'config.php';

// Si déjà connecté, je le redirige vers le dashboard
if (estConnecte()) {
    header('Location: dashboard.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = 'Veuillez remplir tous les champs';
    } else {
        $connexion = obtenirConnexion();
        $requete = $connexion->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $requete->execute([$email]);
        $utilisateur = $requete->fetch();
        
        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['utilisateur_nom'] = $utilisateur['nom'];
            $_SESSION['utilisateur_role'] = $utilisateur['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $erreur = 'Email ou mot de passe incorrect';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-auth">
        <div class="form-container">
            <h1>Connexion</h1>
            <?php if ($erreur): ?>
                <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                <button type="submit" class="btn-primary">Se connecter</button>
            </form>
            <p class="lien-inscription">Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
            <p class="lien-inscription"><a href="accueil.php">← Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>

