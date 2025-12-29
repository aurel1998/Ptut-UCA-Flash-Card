<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

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

// Supprimer la carte 
$requete = $connexion->prepare("DELETE FROM cartes WHERE id = ?");
$requete->execute([$carte_id]);

header('Location: deck_voir.php?id=' . $carte['deck_id']);
exit;

