<?php
require_once __DIR__ . '/../config.php';

class PartageController {
    public function creer() {
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
            header('Location: /index.php?controller=deck&action=liste');
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
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/partages/creer.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    public function supprimer() {
        redirigerSiNonConnecte();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $connexion = obtenirConnexion();
        
        $partage_id = $_POST['partage_id'] ?? 0;
        $deck_id = $_POST['deck_id'] ?? 0;
        
        // Vérifier que le partage appartient à l'utilisateur
        $requete = $connexion->prepare("
            DELETE FROM partages_decks 
            WHERE id = ? AND cree_par = ?
        ");
        $requete->execute([$partage_id, $utilisateur['id']]);
        
        header('Location: /index.php?controller=partage&action=creer&deck_id=' . $deck_id);
        exit;
    }
}

