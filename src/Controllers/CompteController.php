<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\CleApi;
use App\Models\Lieu;
use App\Models\Utilisateur;

/*
 * Espace « Mon compte » : profil (pseudo, e-mail), mot de passe, avatar,
 * et clés API. Tout est protégé par CSRF et réservé à l'utilisateur connecté.
 */
class CompteController
{
    private const UPLOAD_DIR = __DIR__ . '/../../storage/uploads';
    private const AVATAR_MAX_BYTES = 500 * 1024;   // 500 Ko
    private const AVATAR_MAX_PX = 500;             // 500 × 500 px
    private const AVATAR_QUALITY = 85;

    public function index(): void
    {
        $this->exigeConnexion();

        $flash = $_SESSION['compte_flash'] ?? null;
        unset($_SESSION['compte_flash']);

        (new View())->render('compte/index', [
            'title'       => t('page.account.title'),
            'utilisateur' => Utilisateur::parId(Auth::id()),
            'cles'        => CleApi::parUtilisateur(Auth::id()),
            'nouvelleCle' => $_SESSION['nouvelle_cle'] ?? null,
            'flash'       => $flash,
        ]);
    }

    /** Page « Mes lieux visités » : lieux où l'utilisateur a posé un relevé. */
    public function mesLieux(): void
    {
        $this->exigeConnexion();
        (new View())->render('compte/mes-lieux', [
            'title' => t('page.my_places.title'),
            'lieux' => Lieu::visitesParUtilisateur(Auth::id()),
        ]);
    }

    /** Mise à jour du pseudo + e-mail. */
    public function majProfil(): void
    {
        $this->exigeConnexion();
        $id = Auth::id();
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $erreurs = [];

        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $erreurs[] = 'error.csrf';
        }
        if (mb_strlen($pseudo) < 3 || mb_strlen($pseudo) > 40) {
            $erreurs[] = 'error.pseudo_length';
        } elseif (Utilisateur::pseudoPris($pseudo, $id)) {
            $erreurs[] = 'error.pseudo_taken';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'error.email_invalid';
        } elseif (Utilisateur::emailPris($email, $id)) {
            $erreurs[] = 'error.email_taken';
        }

        if (!$erreurs) {
            Utilisateur::majProfil($id, $pseudo, $email);
            Auth::rafraichir(['pseudo' => $pseudo]);   // l'en-tête + jointures suivent
            $this->flash('ok', ['account.profile_saved']);
        } else {
            $this->flash('err', $erreurs);
        }
        $this->rediriger('/compte');
    }

    /** Changement de mot de passe (nouveau + confirmation). */
    public function majMotDePasse(): void
    {
        $this->exigeConnexion();
        $mdp  = (string) ($_POST['mot_de_passe'] ?? '');
        $mdp2 = (string) ($_POST['confirmation'] ?? '');
        $erreurs = [];

        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $erreurs[] = 'error.csrf';
        }
        if (!Utilisateur::motDePasseValide($mdp)) {
            $erreurs[] = 'error.password_weak';
        }
        if ($mdp !== $mdp2) {
            $erreurs[] = 'error.password_mismatch';
        }

        if (!$erreurs) {
            Utilisateur::majMotDePasse(Auth::id(), $mdp);
            $this->flash('ok', ['account.password_saved']);
        } else {
            $this->flash('err', $erreurs);
        }
        $this->rediriger('/compte');
    }

    /** Téléversement de l'avatar (PNG/JPG, ≤ 500 Ko, ≤ 500×500 px → WebP). */
    public function majAvatar(): void
    {
        $this->exigeConnexion();
        $id = Auth::id();

        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $this->flash('err', ['error.csrf']);
            $this->rediriger('/compte');
            return;
        }
        try {
            $fichier = $this->traiterAvatar($_FILES['avatar'] ?? null, $id);
            Utilisateur::majAvatar($id, $fichier);
            Auth::rafraichir(['avatar' => $fichier]);
            $this->flash('ok', ['account.avatar_saved']);
        } catch (\RuntimeException $e) {
            $this->flash('err', [$e->getMessage()]);
        }
        $this->rediriger('/compte');
    }

    /**
     * Valide l'image et l'enregistre en WebP (avatar_{id}.webp). Renvoie le nom.
     * Lève une RuntimeException dont le message est une CLÉ i18n d'erreur.
     *
     * @param array<string,mixed>|null $file
     */
    private function traiterAvatar(?array $file, int $id): string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new \RuntimeException('error.avatar_required');
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('error.avatar_failed');
        }
        if (($file['size'] ?? 0) > self::AVATAR_MAX_BYTES) {
            throw new \RuntimeException('error.avatar_size');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            throw new \RuntimeException('error.avatar_type');
        }
        $infos = @getimagesize($file['tmp_name']);
        if ($infos === false) {
            throw new \RuntimeException('error.avatar_failed');
        }
        if ($infos[0] > self::AVATAR_MAX_PX || $infos[1] > self::AVATAR_MAX_PX) {
            throw new \RuntimeException('error.avatar_dims');
        }

        $im = @imagecreatefromstring(file_get_contents($file['tmp_name']) ?: '');
        if ($im === false) {
            throw new \RuntimeException('error.avatar_failed');
        }
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0775, true);
        }
        $name = 'avatar_' . $id . '.webp';
        $ok = imagewebp($im, self::UPLOAD_DIR . '/' . $name, self::AVATAR_QUALITY);
        imagedestroy($im);
        if (!$ok) {
            throw new \RuntimeException('error.avatar_failed');
        }
        return $name;
    }

    public function creerCle(): void
    {
        $this->exigeConnexion();
        if (Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $libelle = trim($_POST['libelle'] ?? '');
            $_SESSION['nouvelle_cle'] = CleApi::creer(Auth::id(), $libelle !== '' ? $libelle : null);
        }
        $this->rediriger('/compte');
    }

    public function supprimerCle(): void
    {
        $this->exigeConnexion();
        if (Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            CleApi::supprimer((int) ($_POST['id'] ?? 0), Auth::id());
        }
        $this->rediriger('/compte');
    }

    /** @param array<int,string> $msgs */
    private function flash(string $type, array $msgs): void
    {
        $_SESSION['compte_flash'] = ['type' => $type, 'msgs' => $msgs];
    }

    private function exigeConnexion(): void
    {
        if (!Auth::estConnecte()) {
            $this->rediriger('/connexion');
        }
    }

    private function rediriger(string $chemin): void
    {
        header('Location: ' . BASE_URL . $chemin);
        exit;
    }
}
