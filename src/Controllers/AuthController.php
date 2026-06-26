<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Totp;
use App\Core\Turnstile;
use App\Core\View;
use App\Models\ConnexionPersistante;
use App\Models\Utilisateur;
use PDOException;

/*
 * Inscription, connexion, déconnexion + double authentification (TOTP) des
 * administrateurs.
 * En cas d'erreur de validation, on ré-affiche le formulaire (avec les erreurs
 * et les anciennes saisies) ; en cas de succès, on redirige.
 */
class AuthController
{
    /** Émetteur affiché dans l'application d'authentification (= nom du site). */
    private const TOTP_ISSUER = 'Backcountry Pathfinders';
    /** Nombre d'essais de code 2FA avant de relancer la connexion. */
    private const MAX_ESSAIS_2FA = 5;

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
                $souvenir = ($_POST['souvenir'] ?? '') !== '';

                // Administrateurs : double authentification obligatoire.
                if (($utilisateur['role'] ?? '') === 'admin') {
                    $id = (int) $utilisateur['id'];
                    if ((int) ($utilisateur['totp_actif'] ?? 0) === 1
                        && ($utilisateur['totp_secret'] ?? '') !== '') {
                        // 2FA déjà configurée → on demande le code.
                        $_SESSION['2fa_defi'] = ['id' => $id, 'souvenir' => $souvenir, 'essais' => 0];
                        $this->rediriger('/connexion/2fa');
                        return;
                    }
                    // Pas encore configurée → enrôlement forcé (secret gardé en session
                    // jusqu'à confirmation d'un premier code).
                    $_SESSION['2fa_enrol'] = [
                        'id'       => $id,
                        'souvenir' => $souvenir,
                        'secret'   => Totp::genererSecret(),
                    ];
                    $this->rediriger('/connexion/2fa/configurer');
                    return;
                }

                // Membres : connexion directe.
                Auth::connecter($utilisateur);
                if ($souvenir) {
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

    /** Affiche le formulaire de saisie du code 2FA (après mot de passe correct). */
    public function formulaire2fa(): void
    {
        if (Auth::estConnecte()) { $this->rediriger('/'); return; }
        if (empty($_SESSION['2fa_defi'])) { $this->rediriger('/connexion'); return; }

        (new View())->render('auth/2fa', [
            'title'   => t('page.2fa.title'),
            'erreurs' => [],
        ]);
    }

    /** Vérifie le code 2FA et termine la connexion. */
    public function verifier2fa(): void
    {
        if (Auth::estConnecte()) { $this->rediriger('/'); return; }
        $defi = $_SESSION['2fa_defi'] ?? null;
        if (!$defi) { $this->rediriger('/connexion'); return; }

        $erreurs = [];
        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $erreurs[] = 'error.csrf';
        } else {
            $code = (string) ($_POST['code'] ?? '');
            $u = Utilisateur::parId((int) $defi['id']);
            if ($u !== null && ($u['totp_secret'] ?? '') !== ''
                && Totp::verifier((string) $u['totp_secret'], $code)) {
                unset($_SESSION['2fa_defi']);
                Auth::connecter($u);
                if (!empty($defi['souvenir'])) {
                    ConnexionPersistante::emettre((int) $u['id']);
                }
                $this->rediriger('/');
                return;
            }

            // Échec : on compte les essais et on relance la connexion si trop nombreux.
            $essais = (int) ($defi['essais'] ?? 0) + 1;
            if ($essais >= self::MAX_ESSAIS_2FA) {
                unset($_SESSION['2fa_defi']);
                $this->rediriger('/connexion');
                return;
            }
            $_SESSION['2fa_defi']['essais'] = $essais;
            $erreurs[] = 'error.2fa_invalid';
        }

        (new View())->render('auth/2fa', [
            'title'   => t('page.2fa.title'),
            'erreurs' => $erreurs,
        ]);
    }

    /** Affiche l'écran d'enrôlement 2FA (QR + secret) pour un admin non configuré. */
    public function formulaire2faSetup(): void
    {
        if (Auth::estConnecte()) { $this->rediriger('/'); return; }
        $enrol = $_SESSION['2fa_enrol'] ?? null;
        if (!$enrol) { $this->rediriger('/connexion'); return; }

        $u = Utilisateur::parId((int) $enrol['id']);
        if ($u === null) { unset($_SESSION['2fa_enrol']); $this->rediriger('/connexion'); return; }

        $secret = (string) $enrol['secret'];
        $compte = (string) ($u['email'] ?? $u['pseudo']);

        (new View())->render('auth/2fa-setup', [
            'title'   => t('page.2fa_setup.title'),
            'secret'  => $secret,
            'uri'     => Totp::uriOtpauth($secret, $compte, self::TOTP_ISSUER),
            'erreurs' => [],
        ]);
    }

    /** Confirme l'enrôlement : valide un premier code, enregistre et active la 2FA. */
    public function activer2fa(): void
    {
        if (Auth::estConnecte()) { $this->rediriger('/'); return; }
        $enrol = $_SESSION['2fa_enrol'] ?? null;
        if (!$enrol) { $this->rediriger('/connexion'); return; }

        $secret = (string) $enrol['secret'];
        $erreurs = [];

        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $erreurs[] = 'error.csrf';
        } elseif (Totp::verifier($secret, (string) ($_POST['code'] ?? ''))) {
            $id = (int) $enrol['id'];
            Utilisateur::definirSecretTotp($id, $secret);
            Utilisateur::activerTotp($id);
            unset($_SESSION['2fa_enrol']);

            $u = Utilisateur::parId($id);
            if ($u !== null) {
                Auth::connecter($u);
                if (!empty($enrol['souvenir'])) {
                    ConnexionPersistante::emettre($id);
                }
            }
            $this->rediriger('/');
            return;
        } else {
            $erreurs[] = 'error.2fa_invalid';
        }

        $u = Utilisateur::parId((int) $enrol['id']);
        $compte = $u !== null ? (string) ($u['email'] ?? $u['pseudo']) : '';
        (new View())->render('auth/2fa-setup', [
            'title'   => t('page.2fa_setup.title'),
            'secret'  => $secret,
            'uri'     => Totp::uriOtpauth($secret, $compte, self::TOTP_ISSUER),
            'erreurs' => $erreurs,
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
