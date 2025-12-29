<?php
// Configuration de la base de données SQLite (On a laissé mysql)
define('DB_PATH', __DIR__ . '/database/projet_tutore.db');

// Configuration de la session
define('SESSION_LIFETIME', 3600); // 1 heure

// Ma Fonction de connexion à la base de données
function obtenirConnexion() {
    try {
        // Créer le dossier database s'il n'existe pas
        $dbDir = dirname(DB_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        $connexion = new PDO(
            "sqlite:" . DB_PATH,
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        // Activer les clés étrangères pour SQLite
        $connexion->exec("PRAGMA foreign_keys = ON");
        
        return $connexion;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour vérifier si l'utilisateur est connecté
function estConnecte() {
    return isset($_SESSION['utilisateur_id']);
}

// Fonction pour obtenir l'utilisateur connecté
function obtenirUtilisateurConnecte() {
    if (!estConnecte()) {
        return null;
    }
    
    $connexion = obtenirConnexion();
    $requete = $connexion->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $requete->execute([$_SESSION['utilisateur_id']]);
    return $requete->fetch();
}

// Fonction pour rediriger si non connecté
function redirigerSiNonConnecte() {
    if (!estConnecte()) {
        header('Location: login.php');
        exit;
    }
}

// Fonction pour rediriger si l'utilisateur n'est pas étudiant
function redirigerSiNonEtudiant() {
    redirigerSiNonConnecte();
    $utilisateur = obtenirUtilisateurConnecte();
    if ($utilisateur['role'] !== 'etudiant') {
        header('Location: dashboard.php');
        exit;
    }
}

// Fonction pour rediriger si l'utilisateur n'est pas enseignant
function redirigerSiNonEnseignant() {
    redirigerSiNonConnecte();
    $utilisateur = obtenirUtilisateurConnecte();
    if ($utilisateur['role'] !== 'enseignant') {
        header('Location: dashboard.php');
        exit;
    }
}

