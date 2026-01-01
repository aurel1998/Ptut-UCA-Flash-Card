<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Deck.php';
require_once __DIR__ . '/../models/SessionRevision.php';

class RevisionController {
    private $deck;
    private $session;
    
    public function __construct() {
        $this->deck = new Deck();
        $this->session = new SessionRevision();
    }
    
    public function index() {
        redirigerSiNonEtudiant();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $connexion = obtenirConnexion();
        
        // Récupérer les decks disponibles
        $decks = $this->deck->obtenirDecksDisponibles(
            $utilisateur['id'],
            $utilisateur['filiere'],
            $utilisateur['annee']
        );
        
        // Pour chaque deck, compter les cartes
        foreach ($decks as &$deck) {
            $requete = $connexion->prepare("
                SELECT COUNT(*) as total
                FROM cartes
                WHERE deck_id = ?
            ");
            $requete->execute([$deck['id']]);
            $result = $requete->fetch();
            $deck['cartes_a_reviser'] = $result['total'] ?? 0;
            
            // Vérifier s'il y a une session en cours
            $session_en_cours = $this->session->obtenirEnCours($utilisateur['id'], $deck['id']);
            $deck['session_en_cours'] = $session_en_cours ? $session_en_cours['id'] : null;
        }
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/revision/liste.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    public function demarrer() {
        redirigerSiNonEtudiant();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $deck_id = $_GET['deck_id'] ?? 0;
        $reprendre = $_GET['reprendre'] ?? false;
        
        // Vérifier que le deck est accessible
        $decks = $this->deck->obtenirDecksDisponibles(
            $utilisateur['id'],
            $utilisateur['filiere'],
            $utilisateur['annee']
        );
        
        $deck = null;
        foreach ($decks as $d) {
            if ($d['id'] == $deck_id) {
                $deck = $d;
                break;
            }
        }
        
        if (!$deck) {
            header('Location: /index.php?controller=revision&action=index');
            exit;
        }
        
        // Vérifier s'il y a une session en cours
        $session_en_cours = $this->session->obtenirEnCours($utilisateur['id'], $deck_id);
        
        if ($reprendre && $session_en_cours) {
            // Reprendre la session existante
            $session_id = $session_en_cours['id'];
        } else {
            // Créer une nouvelle session
            $session_id = $this->session->creer($utilisateur['id'], $deck_id);
        }
        
        // Rediriger vers la session de révision
        header('Location: /index.php?controller=revision&action=session&session_id=' . $session_id);
        exit;
    }
    
    public function session() {
        redirigerSiNonEtudiant();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $session_id = $_GET['session_id'] ?? 0;
        
        $session = $this->session->obtenirParId($session_id);
        
        if (!$session || $session['utilisateur_id'] != $utilisateur['id'] || $session['statut'] != 'en_cours') {
            header('Location: /index.php?controller=revision&action=index');
            exit;
        }
        
        $connexion = obtenirConnexion();
        
        // Récupérer une carte aléatoire non révisée dans cette session
        $requete = $connexion->prepare("
            SELECT DISTINCT c.*, 
            COALESCE(sl.pile, 1) as pile,
            sl.id as statut_id
            FROM cartes c
            LEFT JOIN statuts_leitner sl ON c.id = sl.carte_id AND sl.utilisateur_id = ?
            LEFT JOIN historique_revisions hr ON c.id = hr.carte_id 
                AND hr.utilisateur_id = ? 
                AND hr.session_id = ?
            WHERE c.deck_id = ?
            AND hr.id IS NULL
            ORDER BY ABS(RANDOM())
            LIMIT 1
        ");
        $requete->execute([$utilisateur['id'], $utilisateur['id'], $session_id, $session['deck_id']]);
        $carte = $requete->fetch();
        
        if (!$carte) {
            // Aucune carte restante, terminer la session
            $this->session->terminer($session_id);
            header('Location: /index.php?controller=revision&action=resultat&session_id=' . $session_id);
            exit;
        }
        
        // Récupérer les choix si QCM (mélangés aléatoirement)
        $choix = [];
        if ($carte['type'] === 'qcm') {
            require_once __DIR__ . '/../models/Card.php';
            $card = new Card();
            $choix = $card->obtenirChoix($carte['id'], true); // true = mélanger aléatoirement
        }
        
        $deck = $this->deck->obtenirParId($session['deck_id']);
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/revision/session.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    public function traiter() {
        redirigerSiNonEtudiant();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $connexion = obtenirConnexion();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php?controller=revision&action=index');
            exit;
        }
        
        $carte_id = $_POST['carte_id'] ?? 0;
        $session_id = $_POST['session_id'] ?? 0;
        $reponse_texte = trim($_POST['reponse_texte'] ?? '');
        $reponse_choix = $_POST['reponse_choix'] ?? [];
        
        $session = $this->session->obtenirParId($session_id);
        
        if (!$session || $session['utilisateur_id'] != $utilisateur['id'] || $session['statut'] != 'en_cours') {
            header('Location: /index.php?controller=revision&action=index');
            exit;
        }
        
        // Vérifier que cette carte n'a pas déjà été révisée dans cette session
        $requete = $connexion->prepare("
            SELECT id FROM historique_revisions
            WHERE utilisateur_id = ? AND carte_id = ? AND session_id = ?
            LIMIT 1
        ");
        $requete->execute([$utilisateur['id'], $carte_id, $session_id]);
        if ($requete->fetch()) {
            header('Location: /index.php?controller=revision&action=session&session_id=' . $session_id);
            exit;
        }
        
        // Récupérer la carte
        require_once __DIR__ . '/../models/Card.php';
        $card = new Card();
        $carte = $card->obtenirParId($carte_id);
        
        if (!$carte) {
            header('Location: /index.php?controller=revision&action=index');
            exit;
        }
        
        $resultat = 'incorrect';
        $reponse_utilisateur = '';
        
        // Comparer la réponse selon le type de carte
        if ($carte['type'] === 'qcm') {
            $tous_choix = $card->obtenirChoix($carte_id);
            
            $choix_corrects_ids = [];
            $choix_incorrects_ids = [];
            foreach ($tous_choix as $choix_item) {
                if ($choix_item['est_correct']) {
                    $choix_corrects_ids[] = $choix_item['id'];
                } else {
                    $choix_incorrects_ids[] = $choix_item['id'];
                }
            }
            
            $choix_selectionnes = is_array($reponse_choix) ? $reponse_choix : [];
            
            if (!empty($choix_selectionnes)) {
                $reponses_texte = [];
                foreach ($choix_selectionnes as $choix_id) {
                    foreach ($tous_choix as $choix_item) {
                        if ($choix_item['id'] == $choix_id) {
                            $reponses_texte[] = $choix_item['texte_choix'];
                            break;
                        }
                    }
                }
                $reponse_utilisateur = implode('; ', $reponses_texte);
                
                $toutes_bonnes_selectionnees = count($choix_corrects_ids) > 0 && 
                                               count(array_intersect($choix_selectionnes, $choix_corrects_ids)) === count($choix_corrects_ids);
                $aucune_mauvaise_selectionnee = count(array_intersect($choix_selectionnes, $choix_incorrects_ids)) === 0;
                
                $resultat = ($toutes_bonnes_selectionnees && $aucune_mauvaise_selectionnee) ? 'correct' : 'incorrect';
            }
        } else {
            $reponse_utilisateur = $reponse_texte;
            
            if (!empty($reponse_texte)) {
                $reponse_attendue = mb_strtolower(trim($carte['texte_verso']), 'UTF-8');
                $reponse_etudiant = mb_strtolower(trim($reponse_texte), 'UTF-8');
                
                $reponse_attendue_norm = preg_replace('/\s+/', ' ', preg_replace('/[^\w\s]/u', '', $reponse_attendue));
                $reponse_etudiant_norm = preg_replace('/\s+/', ' ', preg_replace('/[^\w\s]/u', '', $reponse_etudiant));
                
                if ($reponse_etudiant_norm === $reponse_attendue_norm) {
                    $resultat = 'correct';
                } else {
                    similar_text($reponse_attendue_norm, $reponse_etudiant_norm, $similarite);
                    if ($similarite >= 85) {
                        $resultat = 'correct';
                    }
                }
            }
        }
        
        // Mettre à jour le statut Leitner
        $requete = $connexion->prepare("SELECT * FROM statuts_leitner WHERE utilisateur_id = ? AND carte_id = ?");
        $requete->execute([$utilisateur['id'], $carte_id]);
        $statut = $requete->fetch();
        
        $pile_actuelle = $statut ? $statut['pile'] : 1;
        
        if ($resultat === 'correct') {
            $nouvelle_pile = min($pile_actuelle + 1, 5);
        } else {
            $nouvelle_pile = 1;
        }
        
        $intervalles = [1 => '+1 day', 2 => '+2 days', 3 => '+5 days', 4 => '+14 days', 5 => '+30 days'];
        $prochaine_revision = date('Y-m-d H:i:s', strtotime($intervalles[$nouvelle_pile]));
        
        if ($statut) {
            $requete = $connexion->prepare("
                UPDATE statuts_leitner 
                SET pile = ?, prochaine_revision = ?, derniere_revision = datetime('now')
                WHERE id = ?
            ");
            $requete->execute([$nouvelle_pile, $prochaine_revision, $statut['id']]);
        } else {
            $requete = $connexion->prepare("
                INSERT INTO statuts_leitner (utilisateur_id, carte_id, pile, prochaine_revision, derniere_revision)
                VALUES (?, ?, ?, ?, datetime('now'))
            ");
            $requete->execute([$utilisateur['id'], $carte_id, $nouvelle_pile, $prochaine_revision]);
        }
        
        // Enregistrer dans l'historique avec la session
        $requete = $connexion->prepare("
            INSERT INTO historique_revisions (utilisateur_id, carte_id, deck_id, resultat, reponse_utilisateur, session_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $requete->execute([
            $utilisateur['id'], 
            $carte_id, 
            $session['deck_id'], 
            $resultat, 
            $reponse_utilisateur,
            $session_id
        ]);
        
        // Stocker le résultat dans la session PHP
        $_SESSION['revision_resultat'] = $resultat;
        $_SESSION['revision_reponse_utilisateur'] = $reponse_utilisateur;
        $_SESSION['revision_carte_id'] = $carte_id;
        
        // Rediriger vers la page de résultat
        header('Location: /index.php?controller=revision&action=resultat&session_id=' . $session_id);
        exit;
    }
    
    public function resultat() {
        redirigerSiNonEtudiant();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $session_id = $_GET['session_id'] ?? 0;
        
        $session = $this->session->obtenirParId($session_id);
        
        if (!$session || $session['utilisateur_id'] != $utilisateur['id']) {
            header('Location: /index.php?controller=revision&action=index');
            exit;
        }
        
        $resultat = $_SESSION['revision_resultat'] ?? null;
        $reponse_utilisateur = $_SESSION['revision_reponse_utilisateur'] ?? '';
        $carte_id = $_SESSION['revision_carte_id'] ?? 0;
        
        if (!$resultat) {
            header('Location: /index.php?controller=revision&action=session&session_id=' . $session_id);
            exit;
        }
        
        // Nettoyer la session PHP
        unset($_SESSION['revision_resultat']);
        unset($_SESSION['revision_reponse_utilisateur']);
        unset($_SESSION['revision_carte_id']);
        
        // Récupérer la carte
        require_once __DIR__ . '/../models/Card.php';
        $card = new Card();
        $carte = $card->obtenirParId($carte_id);
        $deck = $this->deck->obtenirParId($session['deck_id']);
        
        if (!$carte || !$deck) {
            header('Location: /index.php?controller=revision&action=index');
            exit;
        }
        
        // Récupérer le statut Leitner
        $connexion = obtenirConnexion();
        $requete = $connexion->prepare("SELECT pile FROM statuts_leitner WHERE utilisateur_id = ? AND carte_id = ?");
        $requete->execute([$utilisateur['id'], $carte_id]);
        $statut = $requete->fetch();
        $nouvelle_pile = $statut ? $statut['pile'] : 1;
        
        // Vérifier s'il reste des cartes dans la session
        $requete = $connexion->prepare("
            SELECT 
                COUNT(DISTINCT c.id) as total,
                COUNT(DISTINCT hr.carte_id) as revisees
            FROM cartes c
            LEFT JOIN historique_revisions hr ON c.id = hr.carte_id 
                AND hr.utilisateur_id = ? 
                AND hr.session_id = ?
            WHERE c.deck_id = ?
        ");
        $requete->execute([$utilisateur['id'], $session_id, $session['deck_id']]);
        $stats = $requete->fetch();
        $reste_cartes = $stats && $stats['total'] > $stats['revisees'];
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/revision/resultat.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
