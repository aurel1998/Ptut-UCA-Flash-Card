<?php
require_once __DIR__ . '/../config.php';

class Deck {
    private $connexion;
    
    public function __construct() {
        $this->connexion = obtenirConnexion();
    }
    
    // Créer un deck
    public function creer($utilisateur_id, $titre, $description = '', $tags = '') {
        $requete = $this->connexion->prepare("
            INSERT INTO decks (utilisateur_id, titre, description, tags)
            VALUES (?, ?, ?, ?)
        ");
        $requete->execute([$utilisateur_id, $titre, $description, $tags]);
        return $this->connexion->lastInsertId();
    }
    
    // Obtenir un deck par ID
    public function obtenirParId($id) {
        $requete = $this->connexion->prepare("SELECT * FROM decks WHERE id = ?");
        $requete->execute([$id]);
        return $requete->fetch();
    }
    
    // Obtenir tous les decks d'un utilisateur
    public function obtenirParUtilisateur($utilisateur_id) {
        $requete = $this->connexion->prepare("SELECT * FROM decks WHERE utilisateur_id = ? ORDER BY date_creation DESC");
        $requete->execute([$utilisateur_id]);
        return $requete->fetchAll();
    }
    
    // Vérifier si un deck appartient à un utilisateur
    public function appartientA($deck_id, $utilisateur_id) {
        $requete = $this->connexion->prepare("SELECT id FROM decks WHERE id = ? AND utilisateur_id = ?");
        $requete->execute([$deck_id, $utilisateur_id]);
        return $requete->fetch() !== false;
    }
    
    // Modifier un deck
    public function modifier($id, $titre, $description = '', $tags = '') {
        $requete = $this->connexion->prepare("
            UPDATE decks 
            SET titre = ?, description = ?, tags = ?, date_modification = datetime('now')
            WHERE id = ?
        ");
        return $requete->execute([$titre, $description, $tags, $id]);
    }
    
    // Supprimer un deck
    public function supprimer($id) {
        $requete = $this->connexion->prepare("DELETE FROM decks WHERE id = ?");
        return $requete->execute([$id]);
    }
    
    // Obtenir les decks disponibles pour un étudiant
    public function obtenirDecksDisponibles($utilisateur_id, $filiere, $annee) {
        $requete = $this->connexion->prepare("
            SELECT DISTINCT d.*, u.nom as createur_nom,
            (SELECT COUNT(*) FROM cartes WHERE deck_id = d.id) as nombre_cartes,
            CASE 
                WHEN d.utilisateur_id = ? THEN 'mes_decks'
                WHEN p.id IS NOT NULL THEN 'partage'
                WHEN a.id IS NOT NULL THEN 'assigne'
                ELSE 'public'
            END as type_deck
            FROM decks d
            LEFT JOIN utilisateurs u ON d.utilisateur_id = u.id
            LEFT JOIN assignations_decks a ON d.id = a.deck_id
            LEFT JOIN partages_decks p ON d.id = p.deck_id AND p.utilisateur_partage_id = ?
            WHERE (a.utilisateur_id = ? OR (a.filiere = ? AND a.annee = ?))
            OR p.id IS NOT NULL
            OR d.utilisateur_id = ?
            ORDER BY d.utilisateur_id = ? DESC, d.date_creation DESC
        ");
        $requete->execute([
            $utilisateur_id,
            $utilisateur_id,
            $utilisateur_id,
            $filiere,
            $annee,
            $utilisateur_id,
            $utilisateur_id
        ]);
        return $requete->fetchAll();
    }
}

