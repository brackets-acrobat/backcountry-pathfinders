-- Migration : bannissement par IP + stockage IP de dernière connexion
-- À jouer sur la base de PROD au déploiement.

-- 1. Stocke l'IP de dernière connexion (pour permettre le bannissement par IP depuis /admin).
ALTER TABLE utilisateurs
    ADD COLUMN ip_derniere_connexion VARCHAR(45) NULL DEFAULT NULL AFTER totp_actif;

-- 2. Rendre vols.id_utilisateur nullable + SET NULL (préserver les données de vol
--    lors d'une suppression de compte admin, au lieu de cascade-supprimer).
ALTER TABLE vols MODIFY COLUMN id_utilisateur INT UNSIGNED NULL;
ALTER TABLE vols DROP FOREIGN KEY fk_vols_user;
ALTER TABLE vols ADD CONSTRAINT fk_vols_user
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE SET NULL;

-- 3. Table des IP bannies.
CREATE TABLE IF NOT EXISTS ip_bannies (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip        VARCHAR(45)  NOT NULL,
    raison    VARCHAR(255) NOT NULL DEFAULT '',
    id_admin  INT UNSIGNED NULL,
    cree_le   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ip_bannies_ip (ip),
    KEY idx_ip_bannies_admin (id_admin),
    CONSTRAINT fk_ip_bannies_admin FOREIGN KEY (id_admin)
        REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
