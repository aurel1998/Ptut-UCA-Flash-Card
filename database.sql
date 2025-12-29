-- Base de données pour le projet de révision
CREATE DATABASE IF NOT EXISTS projet_tutore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projet_tutore;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('etudiant', 'enseignant') DEFAULT 'etudiant',
    filiere VARCHAR(50) NULL,
    annee VARCHAR(50) NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des decks (séries de cartes)
CREATE TABLE IF NOT EXISTS decks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NULL,
    visibilite ENUM('prive', 'public', 'non_liste') DEFAULT 'prive',
    tags VARCHAR(255) NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_visibilite (visibilite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des cartes
CREATE TABLE IF NOT EXISTS cartes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deck_id INT NOT NULL,
    type ENUM('texte', 'qcm') DEFAULT 'texte',
    texte_recto TEXT NOT NULL,
    texte_verso TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
    INDEX idx_deck (deck_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des choix pour les cartes QCM
CREATE TABLE IF NOT EXISTS choix_cartes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carte_id INT NOT NULL,
    texte_choix TEXT NOT NULL,
    est_correct BOOLEAN DEFAULT FALSE,
    ordre INT DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (carte_id) REFERENCES cartes(id) ON DELETE CASCADE,
    INDEX idx_carte (carte_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des statuts Leitner (système de révision espacée)
CREATE TABLE IF NOT EXISTS statuts_leitner (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    carte_id INT NOT NULL,
    pile TINYINT DEFAULT 1 COMMENT '1-5: numéro de la pile',
    prochaine_revision DATETIME NULL,
    derniere_revision DATETIME NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (carte_id) REFERENCES cartes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_utilisateur_carte (utilisateur_id, carte_id),
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_carte (carte_id),
    INDEX idx_prochaine_revision (prochaine_revision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de l'historique des révisions
CREATE TABLE IF NOT EXISTS historique_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    carte_id INT NOT NULL,
    deck_id INT NOT NULL,
    resultat ENUM('correct', 'incorrect') NOT NULL,
    reponse_utilisateur TEXT NULL,
    date_revision DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (carte_id) REFERENCES cartes(id) ON DELETE CASCADE,
    FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_carte (carte_id),
    INDEX idx_deck (deck_id),
    INDEX idx_date_revision (date_revision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des partages de decks
-- Supprimer la table si elle existe pour la recréer avec la bonne structure
DROP TABLE IF EXISTS partages_decks;

CREATE TABLE partages_decks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deck_id INT NOT NULL,
    utilisateur_partage_id INT NULL COMMENT 'ID de l''utilisateur avec qui le deck est partagé',
    token VARCHAR(255) NULL COMMENT 'Token pour partage public (optionnel)',
    date_expiration DATETIME NULL,
    cree_par INT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_partage_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (cree_par) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_deck_utilisateur (deck_id, utilisateur_partage_id),
    INDEX idx_token (token),
    INDEX idx_deck (deck_id),
    INDEX idx_utilisateur_partage (utilisateur_partage_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des assignations de decks (enseignants vers étudiants)
CREATE TABLE IF NOT EXISTS assignations_decks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deck_id INT NOT NULL,
    assigne_par INT NOT NULL,
    filiere VARCHAR(50) NULL,
    annee VARCHAR(50) NULL,
    utilisateur_id INT NULL COMMENT 'Si NULL, affectation à tous les étudiants de la filière/année',
    date_assignation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
    FOREIGN KEY (assigne_par) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_deck (deck_id),
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_filiere_annee (filiere, annee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

