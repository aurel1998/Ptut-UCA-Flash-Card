<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Deck.php';

class DeckController {
    private $deck;
    
    public function __construct() {
        $this->deck = new Deck();
    }
    
    // Afficher le formulaire de création
    public function creer() {
        redirigerSiNonConnecte();
        
        $erreur = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $utilisateur = obtenirUtilisateurConnecte();
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            
            if (empty($titre)) {
                $erreur = 'Le titre est obligatoire';
            } else {
                $deck_id = $this->deck->creer($utilisateur['id'], $titre, $description, $tags);
                header('Location: /index.php?controller=deck&action=voir&id=' . $deck_id);
                exit;
            }
        }
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/decks/creer.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    // Voir un deck
    public function voir() {
        redirigerSiNonConnecte();
        
        $id = $_GET['id'] ?? 0;
        $deck = $this->deck->obtenirParId($id);
        
        if (!$deck) {
            header('Location: /index.php?controller=deck&action=liste');
            exit;
        }
        
        require_once __DIR__ . '/../models/Card.php';
        $card = new Card();
        $cartes = $card->obtenirParDeck($id);
        
        // Pour chaque carte QCM, récupérer les choix
        foreach ($cartes as &$carte) {
            if ($carte['type'] === 'qcm') {
                $carte['choix'] = $card->obtenirChoix($carte['id']);
            }
        }
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/decks/voir.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    // Liste des decks
    public function liste() {
        redirigerSiNonConnecte();
        
        $utilisateur = obtenirUtilisateurConnecte();
        
        if ($utilisateur['role'] === 'etudiant') {
            $decks = $this->deck->obtenirDecksDisponibles(
                $utilisateur['id'],
                $utilisateur['filiere'],
                $utilisateur['annee']
            );
        } else {
            $decks = $this->deck->obtenirParUtilisateur($utilisateur['id']);
        }
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/decks/liste.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    // Modifier un deck
    public function modifier() {
        redirigerSiNonConnecte();
        
        $id = $_GET['id'] ?? 0;
        $utilisateur = obtenirUtilisateurConnecte();
        
        if (!$this->deck->appartientA($id, $utilisateur['id'])) {
            header('Location: /index.php?controller=deck&action=liste');
            exit;
        }
        
        $deck = $this->deck->obtenirParId($id);
        $erreur = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            
            if (empty($titre)) {
                $erreur = 'Le titre est obligatoire';
            } else {
                $this->deck->modifier($id, $titre, $description, $tags);
                header('Location: /index.php?controller=deck&action=voir&id=' . $id);
                exit;
            }
        }
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/decks/modifier.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    // Supprimer un deck
    public function supprimer() {
        redirigerSiNonConnecte();
        
        $id = $_GET['id'] ?? 0;
        $utilisateur = obtenirUtilisateurConnecte();
        
        if ($this->deck->appartientA($id, $utilisateur['id'])) {
            $this->deck->supprimer($id);
        }
        
        header('Location: /index.php?controller=deck&action=liste');
        exit;
    }
    
    // Voir les decks partagés
    public function partages() {
        redirigerSiNonEtudiant();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $connexion = obtenirConnexion();
        
        // Récupérer les decks partagés avec l'étudiant
        $requete = $connexion->prepare("
            SELECT d.*, u.nom as createur_nom, u.email as createur_email,
            (SELECT COUNT(*) FROM cartes WHERE deck_id = d.id) as nombre_cartes,
            p.date_creation as date_partage
            FROM decks d
            JOIN partages_decks p ON d.id = p.deck_id
            JOIN utilisateurs u ON d.utilisateur_id = u.id
            WHERE p.utilisateur_partage_id = ?
            ORDER BY p.date_creation DESC
        ");
        $requete->execute([$utilisateur['id']]);
        $decks_partages = $requete->fetchAll();
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/decks/partages.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    // Imprimer un deck
    public function imprimer() {
        redirigerSiNonConnecte();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $connexion = obtenirConnexion();
        $deck_id = $_GET['id'] ?? 0;
        
        // Récupérer le deck selon le rôle
        if ($utilisateur['role'] === 'etudiant') {
            $requete = $connexion->prepare("
                SELECT d.*, u.nom as createur_nom
                FROM decks d
                LEFT JOIN utilisateurs u ON d.utilisateur_id = u.id
                LEFT JOIN assignations_decks a ON d.id = a.deck_id
                LEFT JOIN partages_decks p ON d.id = p.deck_id AND p.utilisateur_partage_id = ?
                WHERE d.id = ? 
                AND ((a.utilisateur_id = ? OR (a.filiere = ? AND a.annee = ?))
                OR p.id IS NOT NULL
                OR d.utilisateur_id = ?)
            ");
            $requete->execute([
                $utilisateur['id'],
                $deck_id,
                $utilisateur['id'],
                $utilisateur['filiere'],
                $utilisateur['annee'],
                $utilisateur['id']
            ]);
        } else {
            $requete = $connexion->prepare("SELECT * FROM decks WHERE id = ?");
            $requete->execute([$deck_id]);
        }
        
        $deck = $requete->fetch();
        
        if (!$deck) {
            header('Location: /index.php?controller=deck&action=liste');
            exit;
        }
        
        // Récupérer les cartes du deck
        require_once __DIR__ . '/../models/Card.php';
        $card = new Card();
        $cartes = $card->obtenirParDeck($deck_id);
        
        // Pour chaque carte QCM, récupérer les choix
        foreach ($cartes as &$carte) {
            if ($carte['type'] === 'qcm') {
                $carte['choix'] = $card->obtenirChoix($carte['id']);
            }
        }
        
        require_once __DIR__ . '/../views/decks/imprimer.php';
    }
}

