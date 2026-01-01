<?php
require_once __DIR__ . '/../config.php';

class MetaTag {
    private $connexion;
    
    public function __construct() {
        $this->connexion = obtenirConnexion();
    }
    
    // Créer un méta-tag
    public function creer($utilisateur_id, $nom, $description = '', $type_composition = 'et') {
        $requete = $this->connexion->prepare("
            INSERT INTO meta_tags (utilisateur_id, nom, description, type_composition)
            VALUES (?, ?, ?, ?)
        ");
        $requete->execute([$utilisateur_id, $nom, $description, $type_composition]);
        return $this->connexion->lastInsertId();
    }
    
    // Ajouter un tag à un méta-tag
    public function ajouterTag($meta_tag_id, $tag_id, $operation = 'inclure') {
        $requete = $this->connexion->prepare("
            INSERT OR IGNORE INTO meta_tags_tags (meta_tag_id, tag_id, operation)
            VALUES (?, ?, ?)
        ");
        return $requete->execute([$meta_tag_id, $tag_id, $operation]);
    }
    
    // Obtenir les cartes d'un méta-tag (composition/exclusion)
    public function obtenirCartes($meta_tag_id) {
        $meta_tag = $this->obtenirParId($meta_tag_id);
        if (!$meta_tag) return [];
        
        // Obtenir les tags associés
        $requete = $this->connexion->prepare("
            SELECT tag_id, operation FROM meta_tags_tags
            WHERE meta_tag_id = ?
        ");
        $requete->execute([$meta_tag_id]);
        $tags = $requete->fetchAll();
        
        if (empty($tags)) return [];
        
        $tags_inclure = [];
        $tags_exclure = [];
        
        foreach ($tags as $tag) {
            if ($tag['operation'] === 'inclure') {
                $tags_inclure[] = $tag['tag_id'];
            } else {
                $tags_exclure[] = $tag['tag_id'];
            }
        }
        
        // Construire la requête selon le type de composition
        if ($meta_tag['type_composition'] === 'et') {
            // Toutes les cartes qui ont TOUS les tags inclus et AUCUN des tags exclus
            if (empty($tags_inclure)) return [];
            
            $placeholders_inclure = implode(',', array_fill(0, count($tags_inclure), '?'));
            $sql = "
                SELECT DISTINCT c.*
                FROM cartes c
                WHERE c.id IN (
                    SELECT carte_id FROM cartes_tags
                    WHERE tag_id IN ($placeholders_inclure)
                    GROUP BY carte_id
                    HAVING COUNT(DISTINCT tag_id) = ?
                )
            ";
            
            $params = array_merge($tags_inclure, [count($tags_inclure)]);
            
            if (!empty($tags_exclure)) {
                $placeholders_exclure = implode(',', array_fill(0, count($tags_exclure), '?'));
                $sql .= " AND c.id NOT IN (
                    SELECT DISTINCT carte_id FROM cartes_tags
                    WHERE tag_id IN ($placeholders_exclure)
                )";
                $params = array_merge($params, $tags_exclure);
            }
            
        } elseif ($meta_tag['type_composition'] === 'ou') {
            // Toutes les cartes qui ont AU MOINS UN des tags inclus
            if (empty($tags_inclure)) return [];
            
            $placeholders_inclure = implode(',', array_fill(0, count($tags_inclure), '?'));
            $sql = "
                SELECT DISTINCT c.*
                FROM cartes c
                JOIN cartes_tags ct ON c.id = ct.carte_id
                WHERE ct.tag_id IN ($placeholders_inclure)
            ";
            
            $params = $tags_inclure;
            
            if (!empty($tags_exclure)) {
                $placeholders_exclure = implode(',', array_fill(0, count($tags_exclure), '?'));
                $sql .= " AND c.id NOT IN (
                    SELECT DISTINCT carte_id FROM cartes_tags
                    WHERE tag_id IN ($placeholders_exclure)
                )";
                $params = array_merge($params, $tags_exclure);
            }
            
        } else { // 'sauf'
            // Toutes les cartes SAUF celles qui ont les tags exclus
            if (empty($tags_exclure)) {
                // Si pas de tags exclus, retourner toutes les cartes
                $sql = "SELECT * FROM cartes";
                $params = [];
            } else {
                $placeholders_exclure = implode(',', array_fill(0, count($tags_exclure), '?'));
                $sql = "
                    SELECT * FROM cartes
                    WHERE id NOT IN (
                        SELECT DISTINCT carte_id FROM cartes_tags
                        WHERE tag_id IN ($placeholders_exclure)
                    )
                ";
                $params = $tags_exclure;
            }
        }
        
        $requete = $this->connexion->prepare($sql);
        $requete->execute($params);
        return $requete->fetchAll();
    }
    
    // Obtenir un méta-tag par ID
    public function obtenirParId($id) {
        $requete = $this->connexion->prepare("SELECT * FROM meta_tags WHERE id = ?");
        $requete->execute([$id]);
        return $requete->fetch();
    }
}

