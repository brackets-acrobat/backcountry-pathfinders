-- ------------------------------------------------------------
--  Migration : jetons de réinitialisation de mot de passe.
--  Date : 2026-06-29
--
--  Un jeton à usage unique, à durée de vie courte (≈ 1 h), généré quand un
--  utilisateur demande « mot de passe oublié ». Seul le HASH du jeton est
--  stocké ; le jeton en clair n'existe que dans l'e-mail.
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-29_add_password_resets.sql
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS password_resets (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_utilisateur  INT UNSIGNED NOT NULL,
    jeton_hash      CHAR(64) NOT NULL,
    expire_le       DATETIME NOT NULL,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pr_jeton (jeton_hash),
    KEY idx_pr_user (id_utilisateur),
    CONSTRAINT fk_pr_user FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
