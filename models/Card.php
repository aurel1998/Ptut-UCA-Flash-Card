<?php
require_once __DIR__ . '/../config.php';

class Card {
    private $connexion;
    
    public function __construct() {
        $this->connexion = obtenirConnexion();
    }
    
    // Créer une carte texte
    public function creerTexte($deck_id, $question, $reponse, $tags = []) {
        $requete = $this->connexion->prepare("
            INSERT INTO cartes (deck_id, type, texte_recto, texte_verso)
            VALUES (?, 'texte', ?, ?)
        ");
        $requete->execute([$deck_id, $question, $reponse]);
        $carte_id = $this->connexion->lastInsertId();
        
        // Ajouter les tags
        if (!empty($tags)) {
            require_once __DIR__ . '/Tag.php';
            $tag = new Tag();
            foreach ($tags as $tag_nom) {
                $tag_id = $tag->creerOuObtenir($tag_nom);
                $tag->ajouterACarte($carte_id, $tag_id);
            }
        }
        
        return $carte_id;
    }
    
    // Créer une carte QCM
    public function creerQCM($deck_id, $question, $choix, $tags = []) {
        $requete = $this->connexion->prepare("
            INSERT INTO cartes (deck_id, type, texte_recto, texte_verso)
            VALUES (?, 'qcm', ?, '')
        ");
        $requete->execute([$deck_id, $question]);
        $carte_id = $this->connexion->lastInsertId();
        
        // Ajouter les tags
        if (!empty($tags)) {
            require_once __DIR__ . '/Tag.php';
            $tag = new Tag();
            foreach ($tags as $tag_nom) {
                $tag_id = $tag->creerOuObtenir($tag_nom);
                $tag->ajouterACarte($carte_id, $tag_id);
            }
        }
        
        // Ajouter les choix
        foreach ($choix as $index => $choix_item) {
            $requete_choix = $this->connexion->prepare("
                INSERT INTO choix_cartes (carte_id, texte_choix, est_correct, ordre)
                VALUES (?, ?, ?, ?)
            ");
            $requete_choix->execute([
                $carte_id,
                $choix_item['texte'],
                $choix_item['est_correct'] ? 1 : 0,
                $index
            ]);
        }
        
        return $carte_id;
    }
    
    // Obtenir une carte par ID
    public function obtenirParId($id) {
        $requete = $this->connexion->prepare("SELECT * FROM cartes WHERE id = ?");
        $requete->execute([$id]);
        return $requete->fetch();
    }
    
    // Obtenir les cartes d'un deck
    public function obtenirParDeck($deck_id) {
        $requete = $this->connexion->prepare("
            SELECT * FROM cartes
            WHERE deck_id = ?
            ORDER BY date_creation
        ");
        $requete->execute([$deck_id]);
        return $requete->fetchAll();
    }
    
    // Ajouter une carte à un deck (pour compatibilité future avec many-to-many)
    public function ajouterADeck($carte_id, $deck_id) {
        // Pour l'instant, la relation est gérée par cartes.deck_id
        // Cette méthode est conservée pour compatibilité future
        return true;
    }
    
    // Retirer une carte d'un deck (pour compatibilité future avec many-to-many)
    public function retirerDeDeck($carte_id, $deck_id) {
        // Pour l'instant, la relation est gérée par cartes.deck_id
        // Cette méthode est conservée pour compatibilité future
        return true;
    }
    
    // Obtenir les decks d'une carte
    public function obtenirDecks($carte_id) {
        $requete = $this->connexion->prepare("
            SELECT d.* FROM decks d
            JOIN cartes c ON d.id = c.deck_id
            WHERE c.id = ?
        ");
        $requete->execute([$carte_id]);
        return $requete->fetchAll();
    }
    
    // Obtenir les choix d'une carte QCM (mélangés aléatoirement)
    public function obtenirChoix($carte_id, $melanger = true) {
        $ordre = $melanger ? "ABS(RANDOM())" : "ordre";
        $requete = $this->connexion->prepare("SELECT * FROM choix_cartes WHERE carte_id = ? ORDER BY " . $ordre);
        $requete->execute([$carte_id]);
        return $requete->fetchAll();
    }
    
    // Modifier une carte texte
    public function modifierTexte($id, $question, $reponse) {
        $requete = $this->connexion->prepare("
            UPDATE cartes 
            SET texte_recto = ?, texte_verso = ?, date_modification = datetime('now')
            WHERE id = ? AND type = 'texte'
        ");
        return $requete->execute([$question, $reponse, $id]);
    }
    
    // Modifier une carte QCM
    public function modifierQCM($id, $question, $choix) {
        $requete = $this->connexion->prepare("
            UPDATE cartes 
            SET texte_recto = ?, date_modification = datetime('now')
            WHERE id = ? AND type = 'qcm'
        ");
        $requete->execute([$question, $id]);
        
        // Supprimer les anciens choix
        $requete_supp = $this->connexion->prepare("DELETE FROM choix_cartes WHERE carte_id = ?");
        $requete_supp->execute([$id]);
        
        // Ajouter les nouveaux choix
        foreach ($choix as $index => $choix_item) {
            $requete_choix = $this->connexion->prepare("
                INSERT INTO choix_cartes (carte_id, texte_choix, est_correct, ordre)
                VALUES (?, ?, ?, ?)
            ");
            $requete_choix->execute([
                $id,
                $choix_item['texte'],
                $choix_item['est_correct'] ? 1 : 0,
                $index
            ]);
        }
        
        return true;
    }
    
    // Supprimer une carte
    public function supprimer($id) {
        $requete = $this->connexion->prepare("DELETE FROM cartes WHERE id = ?");
        return $requete->execute([$id]);
    }
}

