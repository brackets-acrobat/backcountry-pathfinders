-- ============================================================
--  Schéma de la base Backcountry Pathfinders
--  MariaDB 10.4+ / MySQL 5.7+  —  encodage utf8mb4, moteur InnoDB
--
--  Import (depuis le dossier projet) :
--    mysql -u root  -e "CREATE DATABASE IF NOT EXISTS backcountry
--                       CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
--    mysql -u root backcountry < database/schema.sql
--
--  Modèle :
--    utilisateurs ──< cles_api            (auth de l'appli desktop)
--    utilisateurs ──< lieux (créateur)
--    lieux ──< releves        (un relevé = une contribution : profil + sol + friction)
--    lieux ──< commentaires   (fil communautaire)
--    lieux ──< notes          (appréciation + difficulté, 1 par utilisateur)
--
--  Déduplication des lieux : pas de fonction sphérique en 10.4, donc on
--  pré-filtre par boîte englobante via l'index (latitude, longitude) puis on
--  affine la distance (haversine) côté PHP, avec un rayon R de ~50–200 m.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ------------------------------------------------------------
--  Utilisateurs de la communauté
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pseudo           VARCHAR(40)  NOT NULL,
    email            VARCHAR(190) NOT NULL,
    avatar           VARCHAR(120) NULL,                     -- nom de fichier dans storage/uploads (avatar_{id}.webp)
    mot_de_passe     VARCHAR(255) NOT NULL,                 -- hash bcrypt (password_hash)
    role             ENUM('membre','moderateur','admin') NOT NULL DEFAULT 'membre',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_utilisateurs_pseudo (pseudo),
    UNIQUE KEY uq_utilisateurs_email  (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Connexions persistantes (« se souvenir de moi ») :
--  jeton sélecteur/validateur, le validateur n'est stocké que haché.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS connexions_persistantes (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_utilisateur  INT UNSIGNED NOT NULL,
    selecteur       CHAR(24) NOT NULL,                     -- identifiant public (cookie), indexé
    validateur_hash CHAR(64) NOT NULL,                     -- sha256 du validateur (jamais en clair)
    expire_le       DATETIME NOT NULL,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cp_selecteur (selecteur),
    KEY idx_cp_user (id_utilisateur),
    CONSTRAINT fk_cp_user FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Clés d'API : authentifient l'appli desktop qui envoie les relevés.
--  On stocke uniquement le hash SHA-256 de la clé, jamais la clé en clair.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cles_api (
    id                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_utilisateur       INT UNSIGNED NOT NULL,
    cle_hash             CHAR(64) NOT NULL,                 -- SHA-256 hexadécimal
    libelle              VARCHAR(60) NULL,                  -- ex. « PC salon »
    active               TINYINT(1) NOT NULL DEFAULT 1,
    date_creation        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    derniere_utilisation DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cles_api_hash (cle_hash),
    KEY idx_cles_api_user (id_utilisateur),
    CONSTRAINT fk_cles_api_user FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Lieux de poser (dédupliqués par coordonnées).
--  latitude/longitude = coordonnées canoniques du lieu.
--  Index composite (latitude, longitude) pour le pré-filtre géographique.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lieux (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom           VARCHAR(120) NULL,                        -- libellé éventuel donné par un contributeur
    latitude      DECIMAL(9,6) NOT NULL,                    -- ±90,  6 décimales ≈ 0,11 m
    longitude     DECIMAL(9,6) NOT NULL,                    -- ±180
    altitude_m    SMALLINT NULL,                            -- altitude sol approximative (m)
    pays          CHAR(2) NULL,                             -- code pays ISO 3166-1 alpha-2 (géocodage inverse)
    region_code   VARCHAR(10) NULL,                         -- subdivision ISO 3166-2 (ex. FR-NAQ, US-TX, AU-QLD)
    pays_fr       VARCHAR(80) NULL,                         -- nom du pays en français
    pays_en       VARCHAR(80) NULL,                         -- nom du pays en anglais
    region_fr     VARCHAR(120) NULL,                        -- nom de la région en français
    region_en     VARCHAR(120) NULL,                        -- nom de la région en anglais
    id_createur   INT UNSIGNED NULL,                        -- premier contributeur
    statut        ENUM('actif','masque') NOT NULL DEFAULT 'actif',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_lieux_latlon   (latitude, longitude),
    KEY idx_lieux_createur (id_createur),
    CONSTRAINT fk_lieux_createur FOREIGN KEY (id_createur)
        REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Relevés : une capture de données par un pilote sur un lieu.
--  Plusieurs relevés enrichissent un même lieu (le 1er crée le lieu).
--  Fusionne le profil de relief (passage bas) et la nature du sol (poser).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS releves (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_lieu          INT UNSIGNED NOT NULL,
    id_utilisateur   INT UNSIGNED NULL,                     -- contributeur (NULL si compte supprimé)
    date_releve      DATETIME NOT NULL,                     -- date du vol (heure sim locale)
    latitude         DECIMAL(9,6) NOT NULL,                 -- coordonnées exactes du poser de CE relevé
    longitude        DECIMAL(9,6) NOT NULL,
    altitude_m       SMALLINT NULL,
    type_surface     VARCHAR(32) NULL,                      -- MSFS SURFACE TYPE : Grass/Dirt/Sand/Snow/Ice...
    etat_surface     VARCHAR(16) NULL,                      -- SURFACE CONDITION : Normal/Wet/Icy/Snow
    vitesse_toucher_kt DECIMAL(4,1) NULL,                   -- vitesse sol à l'instant du toucher des roues (kt)
    distance_roulage_m SMALLINT UNSIGNED NULL,              -- distance de roulage à l'atterrissage jusqu'à < 5 kt (m)
    friction         DECIMAL(4,3) NULL,                     -- coefficient empirique (décélération au freinage)
    pente_max_pct    DECIMAL(4,1) NULL,                     -- pente max le long de l'axe (%)
    denivele_m       SMALLINT NULL,                         -- dénivelé sur la zone (m)
    cap_moyen_deg    DECIMAL(4,1) NULL,                     -- cap moyen au poser (°, moyenne circulaire jusqu'à < 20 kt)
    profil_relief    JSON NULL,                             -- échantillons du roulage : [{"d":0,"alt":...}, ...]
    aeronef          VARCHAR(80) NULL,                      -- avion utilisé (friction/longueur en dépendent)
    capture          VARCHAR(120) NULL,                     -- nom de fichier dans storage/uploads
    commentaire      TEXT NULL,                             -- note libre propre à ce relevé
    date_creation    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_releves_lieu (id_lieu),
    KEY idx_releves_user (id_utilisateur),
    CONSTRAINT fk_releves_lieu FOREIGN KEY (id_lieu)
        REFERENCES lieux(id) ON DELETE CASCADE,
    CONSTRAINT fk_releves_user FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Commentaires : fil de discussion communautaire sur un lieu.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS commentaires (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_lieu        INT UNSIGNED NOT NULL,
    id_utilisateur INT UNSIGNED NULL,
    texte          TEXT NOT NULL,
    date_creation  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_commentaires_lieu (id_lieu),
    KEY idx_commentaires_user (id_utilisateur),
    CONSTRAINT fk_commentaires_lieu FOREIGN KEY (id_lieu)
        REFERENCES lieux(id) ON DELETE CASCADE,
    CONSTRAINT fk_commentaires_user FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Notes : appréciation + difficulté, une seule par utilisateur et par lieu.
--  Permet de calculer des moyennes (note_moyenne, difficulte_moyenne).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notes (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_lieu        INT UNSIGNED NOT NULL,
    id_utilisateur INT UNSIGNED NOT NULL,
    note           TINYINT UNSIGNED NULL,                   -- 1..5 (appréciation générale)
    difficulte     TINYINT UNSIGNED NULL,                   -- 1..5 (difficulté du poser)
    date_creation  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_notes_lieu_user (id_lieu, id_utilisateur),
    KEY idx_notes_user (id_utilisateur),
    CONSTRAINT fk_notes_lieu FOREIGN KEY (id_lieu)
        REFERENCES lieux(id) ON DELETE CASCADE,
    CONSTRAINT fk_notes_user FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT chk_notes_note CHECK (note       IS NULL OR note       BETWEEN 1 AND 5),
    CONSTRAINT chk_notes_diff CHECK (difficulte IS NULL OR difficulte BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
