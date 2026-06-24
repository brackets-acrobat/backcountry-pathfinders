-- ------------------------------------------------------------
--  Migration : entité « vol » (regroupe les posers d'une même sortie).
--  Date : 2026-06-24
--
--  L'appli desktop envoie désormais un VOL entier (temps bloc + tous ses
--  posers) en une seule requête. Chaque relevé appartient obligatoirement à
--  un vol (vol_id NOT NULL) : il ne peut plus exister de lieu sans vol associé.
--
--  Suppression d'un vol : ON DELETE CASCADE retire ses relevés ; le nettoyage
--  des lieux devenus vides est fait côté application (Vol::supprimer), afin de
--  PRÉSERVER un lieu partagé qui porte encore le relevé d'un autre pilote.
--
--  Reprise des relevés existants : on crée un vol « legacy » par pilote
--  contributeur (plage de dates de ses relevés) et on y rattache ses relevés,
--  sans aucune perte. ⚠ Pré-requis : aucun relevé avec id_utilisateur NULL
--  (sinon l'étape NOT NULL échoue — à résoudre avant de jouer la migration).
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-24_add_vols.sql
-- ------------------------------------------------------------

-- 1. Table des vols.
CREATE TABLE IF NOT EXISTS vols (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_utilisateur   INT UNSIGNED NOT NULL,                 -- pilote (un vol est toujours rattaché à son pilote)
    date_debut       DATETIME NOT NULL,                     -- heure sim locale du 1er décollage
    date_fin         DATETIME NULL,                         -- arrêt moteur final
    duree_sec        INT UNSIGNED NULL,                     -- temps bloc (s)
    aeronef          VARCHAR(80) NULL,
    depart_icao      VARCHAR(8) NULL,
    arrivee_icao     VARCHAR(8) NULL,
    nb_atterrissages SMALLINT UNSIGNED NOT NULL DEFAULT 0,  -- dénormalisé (liste)
    date_creation    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_vols_user (id_utilisateur),
    CONSTRAINT fk_vols_user FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Colonne vol_id sur les relevés (nullable le temps du backfill).
ALTER TABLE releves
    ADD COLUMN vol_id INT UNSIGNED NULL AFTER id_lieu;

-- 3. Backfill : un vol « legacy » par pilote contributeur…
INSERT INTO vols (id_utilisateur, date_debut, date_fin, duree_sec, aeronef, nb_atterrissages)
SELECT r.id_utilisateur, MIN(r.date_releve), MAX(r.date_releve), NULL, NULL, COUNT(*)
FROM releves r
WHERE r.id_utilisateur IS NOT NULL
GROUP BY r.id_utilisateur;

--    …puis on rattache chaque relevé existant au vol legacy de son pilote.
--    (À cet instant, vols ne contient QUE des vols legacy : 1 par pilote.)
UPDATE releves r
JOIN vols v ON v.id_utilisateur = r.id_utilisateur
SET r.vol_id = v.id
WHERE r.vol_id IS NULL;

-- 4. Verrouillage : vol_id obligatoire + clé étrangère (CASCADE) + index.
ALTER TABLE releves
    MODIFY COLUMN vol_id INT UNSIGNED NOT NULL,
    ADD KEY idx_releves_vol (vol_id),
    ADD CONSTRAINT fk_releves_vol FOREIGN KEY (vol_id)
        REFERENCES vols(id) ON DELETE CASCADE;
