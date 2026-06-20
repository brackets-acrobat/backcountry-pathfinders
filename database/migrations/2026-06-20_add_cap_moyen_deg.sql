-- ------------------------------------------------------------
--  Migration : ajout du cap moyen au poser sur les relevés.
--  Date : 2026-06-20
--
--  À appliquer sur une base déjà créée (le schema.sql complet contient
--  déjà la colonne pour les nouvelles installations).
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-20_add_cap_moyen_deg.sql
--  ou via phpMyAdmin (onglet SQL).
-- ------------------------------------------------------------

ALTER TABLE releves
    ADD COLUMN cap_moyen_deg DECIMAL(4,1) NULL AFTER denivele_m;
