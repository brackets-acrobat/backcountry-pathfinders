-- ------------------------------------------------------------
--  Migration : connexions persistantes (« se souvenir de moi »).
--  Date : 2026-06-22
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-22_add_remember_me.sql
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS connexions_persistantes (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_utilisateur  INT UNSIGNED NOT NULL,
    selecteur       CHAR(24) NOT NULL,
    validateur_hash CHAR(64) NOT NULL,
    expire_le       DATETIME NOT NULL,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cp_selecteur (selecteur),
    KEY idx_cp_user (id_utilisateur),
    CONSTRAINT fk_cp_user FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
