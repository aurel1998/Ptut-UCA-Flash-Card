<?php
require_once 'config.php';
redirigerSiNonConnecte();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: decks.php');
    exit;
}

$partage_id = $_POST['partage_id'] ?? 0;
$deck_id = $_POST['deck_id'] ?? 0;

// Vérifier que le partage appartient à l'utilisateur
$requete = $connexion->prepare("
    SELECT p.* FROM partages_decks p
    WHERE p.id = ? AND p.cree_par = ?
");
$requete->execute([$partage_id, $utilisateur['id']]);
$partage = $requete->fetch();

if (!$partage) {
    header('Location: decks.php');
    exit;
}

// Supprimer le partage
$requete = $connexion->prepare("DELETE FROM partages_decks WHERE id = ?");
$requete->execute([$partage_id]);

header('Location: partage_creer.php?deck_id=' . $deck_id);
exit;

