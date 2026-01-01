<?php
require_once __DIR__ . '/../config.php';

class AssignationController {
    public function index() {
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
        
        // Récupération des étudiants pour la sélection
        $requete = $connexion->prepare("SELECT * FROM utilisateurs WHERE role = 'etudiant' ORDER BY nom");
        $requete->execute();
        $etudiants = $requete->fetchAll();
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/assignations/index.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
