<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

$deck_id = $_GET['id'] ?? 0;
$requete = $connexion->prepare("SELECT * FROM decks WHERE id = ? AND utilisateur_id = ?");
$requete->execute([$deck_id, $utilisateur['id']]);
$deck = $requete->fetch();

if (!$deck) {
    header('Location: decks.php');
    exit;
}

// Supprimer le deck 
$requete = $connexion->prepare("DELETE FROM decks WHERE id = ? AND utilisateur_id = ?");
$requete->execute([$deck_id, $utilisateur['id']]);

header('Location: decks.php');
exit;

