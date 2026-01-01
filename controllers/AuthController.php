<?php
require_once __DIR__ . '/../config.php';

class AuthController {
    public function login() {
        if (estConnecte()) {
            header('Location: /index.php?controller=dashboard&action=index');
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
                    header('Location: /index.php?controller=dashboard&action=index');
                    exit;
                } else {
                    $erreur = 'Email ou mot de passe incorrect';
                }
            }
        }
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/auth/login.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    public function register() {
        if (estConnecte()) {
            header('Location: /index.php?controller=dashboard&action=index');
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
                $requete = $connexion->prepare("SELECT id FROM utilisateurs WHERE email = ?");
                $requete->execute([$email]);
                if ($requete->fetch()) {
                    $erreur = 'Cet email est déjà utilisé';
                } else {
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
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/auth/register.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    public function logout() {
        session_destroy();
        header('Location: /index.php?controller=accueil&action=index');
        exit;
    }
}

