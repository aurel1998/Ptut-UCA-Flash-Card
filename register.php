<?php
require_once 'config.php';

// S'il est déjà connecté, je le redirige vers le dashboard
if (estConnecte()) {
    header('Location: dashboard.php');
    exit;
}

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation_mot_de_passe = $_POST['confirmation_mot_de_passe'] ?? '';
            $role = $_POST['role'] ?? 'etudiant';
            // Les enseignants n'ont pas de filière ni d'année
            $filiere = ($role === 'enseignant') ? null : trim($_POST['filiere'] ?? '');
            $annee = ($role === 'enseignant') ? null : trim($_POST['annee'] ?? '');
    
    if (empty($nom) || empty($email) || empty($mot_de_passe)) {
        $erreur = 'Veuillez remplir tous les champs obligatoires';
    } elseif ($mot_de_passe !== $confirmation_mot_de_passe) {
        $erreur = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($mot_de_passe) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        $connexion = obtenirConnexion();
        
        // Vérifier si l'email existe déjà
        $requete = $connexion->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $requete->execute([$email]);
        if ($requete->fetch()) {
            $erreur = 'Cet email est déjà utilisé';
        } else {
            // Créer l'utilisateur
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $requete = $connexion->prepare("
                INSERT INTO utilisateurs (nom, email, mot_de_passe, role, filiere, annee) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $requete->execute([$nom, $email, $mot_de_passe_hash, $role, $filiere, $annee]);
            
            $succes = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Plateforme de Révision</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-auth">
        <div class="form-container">
            <h1>Inscription</h1>
            <?php if ($erreur): ?>
                <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
            <?php endif; ?>
            <?php if ($succes): ?>
                <div class="message-succes"><?php echo htmlspecialchars($succes); ?></div>
            <?php endif; ?>
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="nom">Nom complet</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirmation_mot_de_passe">Confirmer le mot de passe</label>
                    <input type="password" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" required onchange="toggleFiliereAnnee()">
                        <option value="etudiant">Étudiant</option>
                        <option value="enseignant">Enseignant</option>
                    </select>
                </div>
                <div class="form-group" id="filiere-group">
                    <label for="filiere">Filière (optionnel)</label>
                    <select id="filiere" name="filiere">
                        <option value="">-- Sélectionner --</option>
                        <option value="VCOD">VCOD</option>
                        <option value="EMS">EMS</option>
                        <option value="BIOSTAT">BIOSTAT</option>
                        <option value="GEA">GEA</option>
                    </select>
                </div>
                <div class="form-group" id="annee-group">
                    <label for="annee">Année (optionnel)</label>
                    <select id="annee" name="annee">
                        <option value="">-- Sélectionner --</option>
                        <option value="1ère année">1ère année</option>
                        <option value="2ème année">2ème année</option>
                        <option value="3ème année">3ème année</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">S'inscrire</button>
            </form>
            <p class="lien-inscription">Déjà un compte ? <a href="login.php">Se connecter</a></p>
            <p class="lien-inscription"><a href="accueil.php">← Retour à l'accueil</a></p>
        </div>
    </div>
    <script>
        function toggleFiliereAnnee() {
            var role = document.getElementById('role').value;
            var filiereGroup = document.getElementById('filiere-group');
            var anneeGroup = document.getElementById('annee-group');
            
            if (role === 'enseignant') {
                filiereGroup.style.display = 'none';
                anneeGroup.style.display = 'none';
                document.getElementById('filiere').value = '';
                document.getElementById('annee').value = '';
            } else {
                filiereGroup.style.display = 'block';
                anneeGroup.style.display = 'block';
            }
        }
        
        // Appeler au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            toggleFiliereAnnee();
        });
    </script>
</body>
</html>

