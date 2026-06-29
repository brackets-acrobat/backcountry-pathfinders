-- ============================================================
--  Actualités (« News ») rédigées par les administrateurs.
--  titre = texte simple ; contenu = HTML riche (TinyMCE),
--  assaini côté serveur avant insertion.
-- ============================================================

CREATE TABLE IF NOT EXISTS actualites (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_auteur     INT UNSIGNED NULL,                       -- admin rédacteur (NULL si compte supprimé)
    titre         VARCHAR(120) NOT NULL,                   -- titre (texte + chiffres, 1 ligne)
    contenu       MEDIUMTEXT NOT NULL,                     -- corps (HTML riche : mise en page, image, vidéo)
    statut        ENUM('brouillon','publie') NOT NULL DEFAULT 'publie',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_maj      DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_actualites_auteur (id_auteur),
    KEY idx_actualites_date (date_creation),
    CONSTRAINT fk_actualites_auteur FOREIGN KEY (id_auteur)
        REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
