-- ------------------------------------------------------------
--  Migration : performances d'atterrissage sur les relevés.
--  Date : 2026-06-23
--
--  Deux données mesurées par l'appli desktop au poser :
--    - vitesse_toucher_kt : vitesse sol à l'instant du toucher des roues (kt) ;
--    - distance_roulage_m : distance de roulage jusqu'à ce que la vitesse sol
--                           repasse sous 5 kt (m).
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-23_add_landing_perf.sql
-- ------------------------------------------------------------

ALTER TABLE releves
    ADD COLUMN vitesse_toucher_kt DECIMAL(4,1) NULL       AFTER etat_surface,
    ADD COLUMN distance_roulage_m SMALLINT UNSIGNED NULL  AFTER vitesse_toucher_kt;
