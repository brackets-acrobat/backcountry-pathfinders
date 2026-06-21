-- ------------------------------------------------------------
--  Migration : ajout de l'avatar utilisateur.
--  Date : 2026-06-21
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-21_add_avatar.sql
--  ou via phpMyAdmin (onglet SQL).
-- ------------------------------------------------------------

ALTER TABLE utilisateurs
    ADD COLUMN avatar VARCHAR(120) NULL AFTER email;
