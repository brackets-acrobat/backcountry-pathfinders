-- ------------------------------------------------------------
--  Migration : suppression de la colonne longueur_utile_m.
--  Date : 2026-06-23
--
--  La « longueur utile » (longueur exploitable estimée du spot) n'est plus
--  exploitée : ni calculée par l'appli desktop, ni affichée sur le site.
--  On la retire de la table des relevés.
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-23_drop_longueur_utile.sql
-- ------------------------------------------------------------

ALTER TABLE releves DROP COLUMN longueur_utile_m;
