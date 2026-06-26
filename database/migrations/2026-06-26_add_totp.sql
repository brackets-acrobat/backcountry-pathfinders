-- ------------------------------------------------------------
--  Migration : double authentification (TOTP) des administrateurs.
--  Date : 2026-06-26
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-26_add_totp.sql
--  ou via phpMyAdmin (onglet SQL).
-- ------------------------------------------------------------

ALTER TABLE utilisateurs
    ADD COLUMN totp_secret VARCHAR(64) NULL AFTER role,                 -- secret Base32 (généré à l'enrôlement)
    ADD COLUMN totp_actif  TINYINT(1) NOT NULL DEFAULT 0 AFTER totp_secret;
