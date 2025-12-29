<?php
// Script d'initialisation de la base de données SQLite
require_once 'config.php';

echo "Initialisation de la base de données SQLite...\n\n";

try {
    $connexion = obtenirConnexion();
    
    // Activer les clés étrangères
    $connexion->exec("PRAGMA foreign_keys = ON");
    
    // Table des utilisateurs
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS utilisateurs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            mot_de_passe VARCHAR(255) NOT NULL,
            role TEXT CHECK(role IN ('etudiant', 'enseignant')) DEFAULT 'etudiant',
            filiere VARCHAR(50) NULL,
            annee VARCHAR(50) NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_email ON utilisateurs(email)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_role ON utilisateurs(role)");
    echo "✓ Table 'utilisateurs' créée\n";
    
    // Table des decks
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS decks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            titre VARCHAR(255) NOT NULL,
            description TEXT NULL,
            tags VARCHAR(255) NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_utilisateur ON decks(utilisateur_id)");
    echo "✓ Table 'decks' créée\n";
    
    // Table des cartes
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS cartes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            deck_id INTEGER NOT NULL,
            type TEXT CHECK(type IN ('texte', 'qcm')) DEFAULT 'texte',
            texte_recto TEXT NOT NULL,
            texte_verso TEXT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_deck ON cartes(deck_id)");
    echo "✓ Table 'cartes' créée\n";
    
    // Table des choix pour les cartes QCM
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS choix_cartes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            carte_id INTEGER NOT NULL,
            texte_choix TEXT NOT NULL,
            est_correct INTEGER DEFAULT 0,
            ordre INTEGER DEFAULT 0,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (carte_id) REFERENCES cartes(id) ON DELETE CASCADE
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_carte ON choix_cartes(carte_id)");
    echo "✓ Table 'choix_cartes' créée\n";
    
    // Table des statuts Leitner
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS statuts_leitner (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            carte_id INTEGER NOT NULL,
            pile INTEGER DEFAULT 1,
            prochaine_revision DATETIME NULL,
            derniere_revision DATETIME NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (carte_id) REFERENCES cartes(id) ON DELETE CASCADE,
            UNIQUE(utilisateur_id, carte_id)
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_utilisateur_leitner ON statuts_leitner(utilisateur_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_carte_leitner ON statuts_leitner(carte_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_prochaine_revision ON statuts_leitner(prochaine_revision)");
    echo "✓ Table 'statuts_leitner' créée\n";
    
    // Table de l'historique des révisions
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS historique_revisions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            carte_id INTEGER NOT NULL,
            deck_id INTEGER NOT NULL,
            resultat TEXT CHECK(resultat IN ('correct', 'incorrect')) NOT NULL,
            reponse_utilisateur TEXT NULL,
            date_revision DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (carte_id) REFERENCES cartes(id) ON DELETE CASCADE,
            FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_utilisateur_hist ON historique_revisions(utilisateur_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_carte_hist ON historique_revisions(carte_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_deck_hist ON historique_revisions(deck_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_date_revision ON historique_revisions(date_revision)");
    echo "✓ Table 'historique_revisions' créée\n";
    
    // Table des partages de decks
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS partages_decks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            deck_id INTEGER NOT NULL,
            utilisateur_partage_id INTEGER NOT NULL,
            token VARCHAR(255) NULL,
            date_expiration DATETIME NULL,
            cree_par INTEGER NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_partage_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (cree_par) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            UNIQUE(deck_id, utilisateur_partage_id)
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_token ON partages_decks(token)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_deck_partage ON partages_decks(deck_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_utilisateur_partage ON partages_decks(utilisateur_partage_id)");
    echo "✓ Table 'partages_decks' créée\n";
    
    // Table des assignations de decks
    $connexion->exec("
        CREATE TABLE IF NOT EXISTS assignations_decks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            deck_id INTEGER NOT NULL,
            assigne_par INTEGER NOT NULL,
            filiere VARCHAR(50) NULL,
            annee VARCHAR(50) NULL,
            utilisateur_id INTEGER NULL,
            date_assignation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
            FOREIGN KEY (assigne_par) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        )
    ");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_deck_assign ON assignations_decks(deck_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_utilisateur_assign ON assignations_decks(utilisateur_id)");
    $connexion->exec("CREATE INDEX IF NOT EXISTS idx_filiere_annee ON assignations_decks(filiere, annee)");
    echo "✓ Table 'assignations_decks' créée\n";
    
    echo "\n✅ Base de données initialisée avec succès !\n";
    echo "Le fichier de base de données se trouve dans : database/projet_tutore.db\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de l'initialisation : " . $e->getMessage() . "\n";
    exit(1);
}

