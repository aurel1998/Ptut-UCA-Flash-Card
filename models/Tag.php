<?php
require_once __DIR__ . '/../config.php';

class Tag {
    private $connexion;
    
    public function __construct() {
        $this->connexion = obtenirConnexion();
    }
    
    // Créer ou obtenir un tag
    public function creerOuObtenir($nom) {
        $requete = $this->connexion->prepare("SELECT id FROM tags WHERE nom = ?");
        $requete->execute([$nom]);
        $tag = $requete->fetch();
        
        if ($tag) {
            return $tag['id'];
        }
        
        $requete = $this->connexion->prepare("INSERT INTO tags (nom) VALUES (?)");
        $requete->execute([$nom]);
        return $this->connexion->lastInsertId();
    }
    
    // Obtenir tous les tags
    public function obtenirTous() {
        $requete = $this->connexion->prepare("SELECT * FROM tags ORDER BY nom");
        $requete->execute();
        return $requete->fetchAll();
    }
    
    // Obtenir les tags d'une carte
    public function obtenirParCarte($carte_id) {
        $requete = $this->connexion->prepare("
            SELECT t.* FROM tags t
            JOIN cartes_tags ct ON t.id = ct.tag_id
            WHERE ct.carte_id = ?
            ORDER BY t.nom
        ");
        $requete->execute([$carte_id]);
        return $requete->fetchAll();
    }
    
    // Obtenir les cartes d'un tag
    public function obtenirCartesParTag($tag_id) {
        $requete = $this->connexion->prepare("
            SELECT c.* FROM cartes c
            JOIN cartes_tags ct ON c.id = ct.carte_id
            WHERE ct.tag_id = ?
            ORDER BY c.date_creation
        ");
        $requete->execute([$tag_id]);
        return $requete->fetchAll();
    }
    
    // Ajouter un tag à une carte
    public function ajouterACarte($carte_id, $tag_id) {
        $requete = $this->connexion->prepare("
            INSERT OR IGNORE INTO cartes_tags (carte_id, tag_id)
            VALUES (?, ?)
        ");
        return $requete->execute([$carte_id, $tag_id]);
    }
    
    // Retirer un tag d'une carte
    public function retirerDeCarte($carte_id, $tag_id) {
        $requete = $this->connexion->prepare("
            DELETE FROM cartes_tags
            WHERE carte_id = ? AND tag_id = ?
        ");
        return $requete->execute([$carte_id, $tag_id]);
    }
    
    // Vérifier si un tag est un deck (format deck/nom)
    public function estDeck($nom_tag) {
        return strpos($nom_tag, 'deck/') === 0;
    }
}

