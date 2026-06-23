-- ------------------------------------------------------------
--  Migration : commentaire du pilote sur un lieu.
--  Date : 2026-06-23
--
--  Un commentaire libre par pilote et par lieu, rangé dans la table notes
--  (déjà unique par couple lieu+utilisateur). Édité depuis « Mes lieux
--  visités », affiché « Commentaire de {pseudo} » sur la fiche du lieu.
--
--  Exécution (XAMPP / MySQL) :
--    mysql -u root backcountry < database/migrations/2026-06-23_add_note_commentaire.sql
-- ------------------------------------------------------------

ALTER TABLE notes
    ADD COLUMN commentaire TEXT NULL AFTER difficulte;
