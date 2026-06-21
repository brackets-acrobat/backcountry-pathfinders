<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Turnstile;
use App\Core\View;
use App\Models\ConnexionPersistante;
use App\Models\Utilisateur;
use PDOException;

/*
 * Inscription, connexion, déconnexion.
 * En cas d'erreur de validation, on ré-affiche le formulaire (avec les erreurs
 * et les anciennes saisies) ; en cas de succès, on redirige.
 */
class AuthController
{
    public function formulaireInscription(): void
    {
        if (Auth::estConnecte()) {
            $this->rediriger('/');
            return;
        }
        (new View())->render('auth/inscription', [
            'title'     => t('page.register.title'),
            'erreurs'   => [],
            'anciennes' => [],
        ]);
    }

    public function inscription(): void
    {
        if (Auth::estConnecte()) {
            $this->rediriger('/');
            return;
        }

        $pseudo = trim($_POST['pseudo'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $mdp    = (string) ($_POST['mot_de_passe'] ?? '');
        $mdp2   = (string) ($_POST['confirmation'] ?? '');
        $erreurs = [];

        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $erreurs[] = 'error.csrf';
        }
        if (!Turnstile::verifier($_POST[Turnstile::champ()] ?? null, $_SERVER['REMOTE_ADDR'] ?? null)) {
            $erreurs[] = 'error.captcha';
        }
        if (mb_strlen($pseudo) < 3 || mb_strlen($pseudo) > 40) {
            $erreurs[] = 'error.pseudo_length';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'error.email_invalid';
        }
        if (!Utilisateur::motDePasseValide($mdp)) {
            $erreurs[] = 'error.password_weak';
        }
        if ($mdp !== $mdp2) {
            $erreurs[] = 'error.password_mismatch';
        }

        if (!$erreurs) {
            try {
                $id = Utilisateur::creer($pseudo, $email, $mdp);
                Auth::connecter(Utilisateur::parId($id));
                $this->rediriger('/');
                return;
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {           // violation de clé unique
                    $erreurs[] = 'error.duplicate';
                } else {
                    throw $e;
                }
            }
        }

        (new View())->render('auth/inscription', [
            'title'     => t('page.register.title'),
            'erreurs'   => $erreurs,
            'anciennes' => ['pseudo' => $pseudo, 'email' => $email],
        ]);
    }

    public function formulaireConnexion(): void
    {
        if (Auth::estConnecte()) {
            $this->rediriger('/');
            return;
        }
        (new View())->render('auth/connexion', [
            'title'     => t('page.login.title'),
            'erreurs'   => [],
            'anciennes' => [],
        ]);
    }

    public function connexion(): void
    {
        if (Auth::estConnecte()) {
            $this->rediriger('/');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $mdp   = (string) ($_POST['mot_de_passe'] ?? '');
        $erreurs = [];

        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $erreurs[] = 'error.csrf';
        }
        if (!Turnstile::verifier($_POST[Turnstile::champ()] ?? null, $_SERVER['REMOTE_ADDR'] ?? null)) {
            $erreurs[] = 'error.captcha';
        }

        if (!$erreurs) {
            $utilisateur = Utilisateur::verifierConnexion($email, $mdp);
            if ($utilisateur !== null) {
                Auth::connecter($utilisateur);
                if (($_POST['souvenir'] ?? '') !== '') {
                    ConnexionPersistante::emettre((int) $utilisateur['id']);
                }
                $this->rediriger('/');
                return;
            }
            $erreurs[] = 'error.login_failed';
        }

        (new View())->render('auth/connexion', [
            'title'     => t('page.login.title'),
            'erreurs'   => $erreurs,
            'anciennes' => ['email' => $email],
        ]);
    }

    public function deconnexion(): void
    {
        ConnexionPersistante::revoquer();
        Auth::deconnecter();
        $this->rediriger('/');
    }

    private function rediriger(string $chemin): void
    {
        header('Location: ' . BASE_URL . $chemin);
        exit;
    }
}
