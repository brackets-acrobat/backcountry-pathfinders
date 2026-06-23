<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Turnstile;
use App\Core\View;
use App\Models\Commentaire;
use App\Models\Lieu;
use App\Models\Note;
use App\Models\Releve;

/*
 * Fiche détail d'un lieu : informations, relevés (profil de relief, sol,
 * friction…), notes moyennes et fil de commentaires. Permet aussi, pour un
 * utilisateur connecté, d'ajouter un commentaire et de noter le lieu (POST).
 */
class LieuController
{
    private const COMMENTAIRE_MAX = 2000;

    public function detail(string $id): void
    {
        $idLieu = (int) $id;
        $lieu = $idLieu > 0 ? Lieu::parId($idLieu) : null;

        if ($lieu === null || ($lieu['statut'] ?? 'actif') === 'masque') {
            http_response_code(404);
            (new View())->render('errors/404', ['title' => t('page.404.title')]);
            return;
        }

        // Message flash après un POST (schéma Post/Redirect/Get) : lu puis oublié.
        $flash = $_SESSION['flash_lieu'] ?? null;
        unset($_SESSION['flash_lieu']);

        (new View())->render('lieu/detail', [
            'title'        => $lieu['nom'] ?: t('place.untitled'),
            'lieu'         => $lieu,
            'agregats'     => Lieu::agregats($idLieu),
            'releves'      => Releve::parLieuAvecAuteur($idLieu),
            'avisPilotes'  => Note::commentairesPourLieu($idLieu),
            'commentaires' => Commentaire::parLieu($idLieu),
            'maNote'       => Auth::estConnecte() ? Note::pourUtilisateur($idLieu, Auth::id()) : null,
            'flash'        => $flash,
        ]);
    }

    /** POST : ajoute un commentaire au fil du lieu (connecté + CSRF). */
    public function ajouterCommentaire(string $id): void
    {
        $idLieu = $this->lieuActifOuRedirige($id);

        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $this->flashEtRetour($idLieu, 'error', 'error.csrf');
        }
        if (!Turnstile::verifier($_POST[Turnstile::champ()] ?? null, $_SERVER['REMOTE_ADDR'] ?? null)) {
            $this->flashEtRetour($idLieu, 'error', 'error.captcha');
        }

        $texte = trim((string) ($_POST['texte'] ?? ''));
        if ($texte === '') {
            $this->flashEtRetour($idLieu, 'error', 'error.comment_empty');
        }
        if (mb_strlen($texte) > self::COMMENTAIRE_MAX) {
            $texte = mb_substr($texte, 0, self::COMMENTAIRE_MAX);
        }

        Commentaire::ajouter($idLieu, Auth::id(), $texte);
        $this->flashEtRetour($idLieu, 'success', 'place.comment_added', '#commentaires');
    }

    /** POST : enregistre (ou met à jour) la note de l'utilisateur (connecté + CSRF). */
    public function enregistrerNote(string $id): void
    {
        $idLieu = $this->lieuActifOuRedirige($id);

        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $this->flashEtRetour($idLieu, 'error', 'error.csrf');
        }

        $note = $this->valeurNote($_POST['note'] ?? '');
        $diff = $this->valeurNote($_POST['difficulte'] ?? '');

        if ($note === false || $diff === false) {
            $this->flashEtRetour($idLieu, 'error', 'error.rating_invalid');
        }
        if ($note === null && $diff === null) {
            $this->flashEtRetour($idLieu, 'error', 'error.rating_empty');
        }

        Note::enregistrer($idLieu, Auth::id(), $note, $diff);
        $this->flashEtRetour($idLieu, 'success', 'place.rating_saved', '#avis');
    }

    /**
     * Valide une valeur de note reçue : '' → null, '1'..'5' → int, sinon false (invalide).
     *
     * @param mixed $brut
     * @return int|null|false
     */
    private function valeurNote($brut)
    {
        $brut = trim((string) $brut);
        if ($brut === '') {
            return null;
        }
        if (!ctype_digit($brut)) {
            return false;
        }
        $v = (int) $brut;

        return ($v >= 1 && $v <= 5) ? $v : false;
    }

    /** Retourne l'id du lieu actif, ou redirige (404 / accueil / connexion) et coupe. */
    private function lieuActifOuRedirige(string $id): int
    {
        if (!Auth::estConnecte()) {
            $this->rediriger('/connexion');
        }

        $idLieu = (int) $id;
        $lieu = $idLieu > 0 ? Lieu::parId($idLieu) : null;
        if ($lieu === null || ($lieu['statut'] ?? 'actif') === 'masque') {
            $this->rediriger('/');
        }

        return $idLieu;
    }

    /** Pose un message flash puis redirige vers la fiche du lieu (PRG). */
    private function flashEtRetour(int $idLieu, string $type, string $cle, string $ancre = ''): void
    {
        $_SESSION['flash_lieu'] = ['type' => $type, 'cle' => $cle];
        $this->rediriger('/lieu/' . $idLieu . $ancre);
    }

    private function rediriger(string $chemin): void
    {
        header('Location: ' . BASE_URL . $chemin);
        exit;
    }
}
