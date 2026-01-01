<?php
require_once __DIR__ . '/../config.php';

class DashboardController {
    public function index() {
        redirigerSiNonConnecte();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $connexion = obtenirConnexion();
        
        // Statistiques selon le rôle
        if ($utilisateur['role'] === 'etudiant') {
            require_once __DIR__ . '/../models/Deck.php';
            $deck = new Deck();
            $decks = $deck->obtenirDecksDisponibles(
                $utilisateur['id'],
                $utilisateur['filiere'],
                $utilisateur['annee']
            );
            $stats_decks = ['total' => count($decks)];
            
            $requete = $connexion->prepare("
                SELECT COUNT(DISTINCT c.id) as total
                FROM cartes c
                JOIN decks d ON c.deck_id = d.id
                LEFT JOIN assignations_decks a ON d.id = a.deck_id
                LEFT JOIN partages_decks p ON d.id = p.deck_id AND p.utilisateur_partage_id = ?
                WHERE (a.utilisateur_id = ? OR (a.filiere = ? AND a.annee = ?))
                OR p.id IS NOT NULL
                OR d.utilisateur_id = ?
            ");
            $requete->execute([
                $utilisateur['id'],
                $utilisateur['id'],
                $utilisateur['filiere'],
                $utilisateur['annee'],
                $utilisateur['id']
            ]);
            $stats_revisions = $requete->fetch();
        } else {
            $requete = $connexion->prepare("SELECT COUNT(*) as total FROM decks WHERE utilisateur_id = ?");
            $requete->execute([$utilisateur['id']]);
            $stats_decks = $requete->fetch();
            
            $requete = $connexion->prepare("
                SELECT COUNT(*) as total 
                FROM cartes c
                JOIN decks d ON c.deck_id = d.id
                WHERE d.utilisateur_id = ?
            ");
            $requete->execute([$utilisateur['id']]);
            $stats_revisions = $requete->fetch();
        }
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/dashboard.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}

