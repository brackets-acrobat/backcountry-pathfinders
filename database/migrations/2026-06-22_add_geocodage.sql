-- ------------------------------------------------------------
--  Migration : géocodage inverse des lieux (pays + région, bilingue).
--  Date : 2026-06-22
--
--  La colonne `pays` (CHAR(2), code ISO 3166-1) existe déjà. On ajoute :
--   - region_code : subdivision ISO 3166-2 (ex. FR-ARA, US-TX, AU-QLD)
--   - pays_fr / pays_en     : nom du pays selon la langue (ex. États-Unis / United States)
--   - region_fr / region_en : nom de la région selon la langue (ex. Bavière / Bavaria)
--
--  Les libellés diffèrent réellement selon la langue ; on les stocke donc dans
--  les deux langues (renseignés en une fois à la création via BigDataCloud, qui
--  ne traduit pas hors-ligne). Les codes restent la clé canonique.
--
--  Renseignées automatiquement à la création d'un lieu (voir App\Core\Geocodage).
--  Les lieux antérieurs restent NULL (un backfill peut les compléter).
--
--  À appliquer sur une base déjà créée (le schema.sql complet contient déjà
--  les colonnes pour les nouvelles installations).
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-22_add_geocodage.sql
--  ou via phpMyAdmin (onglet SQL).
-- ------------------------------------------------------------

ALTER TABLE lieux
    ADD COLUMN region_code VARCHAR(10)  NULL AFTER pays,
    ADD COLUMN pays_fr     VARCHAR(80)  NULL AFTER region_code,
    ADD COLUMN pays_en     VARCHAR(80)  NULL AFTER pays_fr,
    ADD COLUMN region_fr   VARCHAR(120) NULL AFTER pays_en,
    ADD COLUMN region_en   VARCHAR(120) NULL AFTER region_fr;
