<?php
// Script de migration pour le nouveau système de tags
require_once 'config.php';

echo "Migration vers le système de tags...\n\n";

try {
    $connexion = obtenirConnexion();
    $connexion->exec("PRAGMA foreign_keys = ON");
    
    // 1. Créer la table des tags
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom VARCHAR(255) UNIQUE NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_tag_nom ON tags(nom)");
    echo "✓ Table 'tags' créée\n";
    
    // 2. Créer la table de relation cartes-tags (many-to-many)
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS cartes_tags (
            carte_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (carte_id, tag_id),
            FOREIGN KEY (carte_id) REFERENCES cartes(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_carte_tag ON cartes_tags(carte_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_tag_carte ON cartes_tags(tag_id)");
    echo "✓ Table 'cartes_tags' créée\n";
    
    // 3. Créer la table de relation cartes-decks (many-to-many)
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS cartes_decks (
            carte_id INTEGER NOT NULL,
            deck_id INTEGER NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (carte_id, deck_id),
            FOREIGN KEY (carte_id) REFERENCES cartes(id) ON DELETE CASCADE,
            FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_carte_deck ON cartes_decks(carte_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_deck_carte ON cartes_decks(deck_id)");
    echo "✓ Table 'cartes_decks' créée\n";
    
    // 4. Migrer les données existantes : créer les relations cartes-decks
    $connexion->exec("
        INSERT INTO cartes_decks (carte_id, deck_id)
        SELECT id, deck_id FROM cartes
        WHERE NOT EXISTS (
            SELECT 1 FROM cartes_decks cd 
            WHERE cd.carte_id = cartes.id AND cd.deck_id = cartes.deck_id
        )
    ");
    echo "✓ Relations cartes-decks migrées\n";
    
    // 5. Créer la table des sessions de révision
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS sessions_revision (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            deck_id INTEGER NULL,
            date_debut DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_fin DATETIME NULL,
            duree_secondes INTEGER NULL,
            nombre_cartes INTEGER DEFAULT 0,
            nombre_correctes INTEGER DEFAULT 0,
            nombre_incorrectes INTEGER DEFAULT 0,
            taux_succes REAL NULL,
            statut TEXT CHECK(statut IN ('en_cours', 'terminee', 'abandonnee')) DEFAULT 'en_cours',
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_session_utilisateur ON sessions_revision(utilisateur_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_session_deck ON sessions_revision(deck_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_session_statut ON sessions_revision(statut)");
    echo "✓ Table 'sessions_revision' créée\n";
    
    // 6. Ajouter session_id à l'historique des révisions
    $connexion->exec("
        ALTER TABLE historique_revisions 
        ADD COLUMN session_id INTEGER NULL
    ");
    $connexion->exec("
        CREATE INDEX IF NOT EXISTS idx_session_hist ON historique_revisions(session_id)
    ");
    echo "✓ Colonne 'session_id' ajoutée à 'historique_revisions'\n";
    
    // 7. Créer la table des méta-tags (pour composition/exclusion)
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS meta_tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            nom VARCHAR(255) NOT NULL,
            description TEXT NULL,
            type_composition TEXT CHECK(type_composition IN ('et', 'ou', 'sauf')) DEFAULT 'et',
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_meta_tag_utilisateur ON meta_tags(utilisateur_id)");
    echo "✓ Table 'meta_tags' créée\n";
    
    // 8. Créer la table de relation meta_tags-tags
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS meta_tags_tags (
            meta_tag_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL,
            operation TEXT CHECK(operation IN ('inclure', 'exclure')) DEFAULT 'inclure',
            PRIMARY KEY (meta_tag_id, tag_id),
            FOREIGN KEY (meta_tag_id) REFERENCES meta_tags(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Table 'meta_tags_tags' créée\n";
    
    // 9. Supprimer la colonne deck_id de cartes (maintenant dans cartes_decks)
    // On garde deck_id pour compatibilité mais on ne l'utilisera plus
    
    echo "\n✅ Migration terminée avec succès !\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}

