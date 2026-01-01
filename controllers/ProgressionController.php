<?php
require_once __DIR__ . '/../config.php';

class ProgressionController {
    public function index() {
        redirigerSiNonEtudiant();
        
        $utilisateur = obtenirUtilisateurConnecte();
        $connexion = obtenirConnexion();
        
        // Statistiques globales
        $requete = $connexion->prepare("
            SELECT 
            COUNT(DISTINCT sl.carte_id) as total_cartes_etudiees,
            SUM(CASE WHEN sl.pile = 5 THEN 1 ELSE 0 END) as cartes_maitrisees
            FROM statuts_leitner sl
            WHERE sl.utilisateur_id = ?
        ");
        $requete->execute([$utilisateur['id']]);
        $stats_globales = $requete->fetch();
        
        // Total révisions
        $requete = $connexion->prepare("SELECT COUNT(*) as total_revisions FROM historique_revisions WHERE utilisateur_id = ?");
        $requete->execute([$utilisateur['id']]);
        $stats_revisions = $requete->fetch();
        $stats_globales['total_revisions'] = $stats_revisions['total_revisions'];
        
        // Répartitions par pile
        $requete = $connexion->prepare("
            SELECT pile, COUNT(*) as nombre
            FROM statuts_leitner
            WHERE utilisateur_id = ?
            GROUP BY pile
            ORDER BY pile
        ");
        $requete->execute([$utilisateur['id']]);
        $repartition_piles = $requete->fetchAll();
        
        // Historique récent avec sessions
        $requete = $connexion->prepare("
            SELECT hr.*, c.texte_recto, d.titre as deck_titre, s.duree_secondes, s.taux_succes
            FROM historique_revisions hr
            JOIN cartes c ON hr.carte_id = c.id
            JOIN decks d ON hr.deck_id = d.id
            LEFT JOIN sessions_revision s ON hr.session_id = s.id
            WHERE hr.utilisateur_id = ?
            ORDER BY hr.date_revision DESC
            LIMIT 20
        ");
        $requete->execute([$utilisateur['id']]);
        $historique = $requete->fetchAll();
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/progression/index.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}
