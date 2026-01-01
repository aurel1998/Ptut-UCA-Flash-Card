<?php
require_once __DIR__ . '/../config.php';

class SessionRevision {
    private $connexion;
    
    public function __construct() {
        $this->connexion = obtenirConnexion();
    }
    
    // Créer une nouvelle session
    public function creer($utilisateur_id, $deck_id = null) {
        $requete = $this->connexion->prepare("
            INSERT INTO sessions_revision (utilisateur_id, deck_id, date_debut)
            VALUES (?, ?, datetime('now'))
        ");
        $requete->execute([$utilisateur_id, $deck_id]);
        return $this->connexion->lastInsertId();
    }
    
    // Obtenir une session par ID
    public function obtenirParId($id) {
        $requete = $this->connexion->prepare("SELECT * FROM sessions_revision WHERE id = ?");
        $requete->execute([$id]);
        return $requete->fetch();
    }
    
    // Obtenir la session en cours d'un utilisateur
    public function obtenirEnCours($utilisateur_id, $deck_id = null) {
        if ($deck_id) {
            $requete = $this->connexion->prepare("
                SELECT * FROM sessions_revision
                WHERE utilisateur_id = ? AND deck_id = ? AND statut = 'en_cours'
                ORDER BY date_debut DESC
                LIMIT 1
            ");
            $requete->execute([$utilisateur_id, $deck_id]);
        } else {
            $requete = $this->connexion->prepare("
                SELECT * FROM sessions_revision
                WHERE utilisateur_id = ? AND statut = 'en_cours'
                ORDER BY date_debut DESC
                LIMIT 1
            ");
            $requete->execute([$utilisateur_id]);
        }
        return $requete->fetch();
    }
    
    // Terminer une session
    public function terminer($session_id) {
        $session = $this->obtenirParId($session_id);
        if (!$session) return false;
        
        $date_debut = new DateTime($session['date_debut']);
        $date_fin = new DateTime();
        $duree = $date_fin->getTimestamp() - $date_debut->getTimestamp();
        
        // Calculer les statistiques
        $requete = $this->connexion->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN resultat = 'correct' THEN 1 ELSE 0 END) as correctes,
                SUM(CASE WHEN resultat = 'incorrect' THEN 1 ELSE 0 END) as incorrectes
            FROM historique_revisions
            WHERE session_id = ?
        ");
        $requete->execute([$session_id]);
        $stats = $requete->fetch();
        
        $taux_succes = $stats['total'] > 0 ? ($stats['correctes'] / $stats['total']) * 100 : 0;
        
        $requete = $this->connexion->prepare("
            UPDATE sessions_revision
            SET date_fin = datetime('now'),
                duree_secondes = ?,
                nombre_cartes = ?,
                nombre_correctes = ?,
                nombre_incorrectes = ?,
                taux_succes = ?,
                statut = 'terminee'
            WHERE id = ?
        ");
        return $requete->execute([
            $duree,
            $stats['total'],
            $stats['correctes'],
            $stats['incorrectes'],
            $taux_succes,
            $session_id
        ]);
    }
    
    // Abandonner une session
    public function abandonner($session_id) {
        $requete = $this->connexion->prepare("
            UPDATE sessions_revision
            SET statut = 'abandonnee'
            WHERE id = ?
        ");
        return $requete->execute([$session_id]);
    }
    
    // Obtenir l'historique des sessions
    public function obtenirHistorique($utilisateur_id, $limit = 10) {
        $requete = $this->connexion->prepare("
            SELECT s.*, d.titre as deck_titre
            FROM sessions_revision s
            LEFT JOIN decks d ON s.deck_id = d.id
            WHERE s.utilisateur_id = ? AND s.statut = 'terminee'
            ORDER BY s.date_fin DESC
            LIMIT ?
        ");
        $requete->execute([$utilisateur_id, $limit]);
        return $requete->fetchAll();
    }
    
    // Mettre à jour le compteur de cartes
    public function incrementerCartes($session_id) {
        $requete = $this->connexion->prepare("
            UPDATE sessions_revision
            SET nombre_cartes = nombre_cartes + 1
            WHERE id = ?
        ");
        return $requete->execute([$session_id]);
    }
}

