<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\HtmlSanitizer;
use App\Core\Upload;
use App\Core\View;
use App\Models\Activite;
use App\Models\Actualite;
use App\Models\IpBannie;

/*
 * Espace d'administration, réservé aux utilisateurs de rôle « admin ».
 * Les non-administrateurs (et les visiteurs) reçoivent un 404 : la page
 * n'est pas révélée.
 */
class AdminController
{
    private const TITRE_MAX   = 120;
    private const CONTENU_MAX = 3500;   // texte visible du corps

    public function index(): void
    {
        if (!Auth::estAdmin()) {
            http_response_code(404);
            (new View())->render('errors/404', ['title' => t('page.404.title')]);
            return;
        }

        $flash = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);

        // Onglet d'administration en cours (barre d'onglets en haut).
        $sections = ['activite', 'news', 'utilisateurs'];
        $brutSection = (string) ($_GET['section'] ?? '');
        $section = in_array($brutSection, $sections, true) ? $brutSection : 'activite';

        // Onglet « Utilisateurs » : liste complète des comptes (e-mail, rôle, 2FA).
        $utilisateurs = $section === 'utilisateurs' ? \App\Models\Utilisateur::tousPourAdmin() : [];

        $activites  = [];
        $filtre     = null;
        $hasIp      = [];
        $nbContribs = [];

        // Sous-vue de l'onglet « News » (menu vertical) : nouvelle | gérer.
        $newsVues = ['nouvelle', 'gerer'];
        $brutVue  = (string) ($_GET['vue'] ?? '');
        $newsVue  = in_array($brutVue, $newsVues, true) ? $brutVue : 'nouvelle';

        // Reprise de saisie du formulaire « Nouvelle actualité » après une erreur.
        $actuOld = $_SESSION['actu_old'] ?? null;
        unset($_SESSION['actu_old']);

        // Onglet News : actualité à éditer (formulaire prérempli) + liste à gérer.
        $actuEdit  = null;
        $actuListe = [];
        if ($section === 'news') {
            if ($newsVue === 'nouvelle' && (int) ($_GET['id'] ?? 0) > 0) {
                $actuEdit = Actualite::parId((int) $_GET['id']);
            } elseif ($newsVue === 'gerer') {
                $actuListe = Actualite::tous();
            }
        }

        // L'onglet « Activité récente » est seul à charger le flux et ses filtres.
        if ($section === 'activite') {
            // Filtre par type d'activité (barre latérale) : valeur validée ou null.
            $types  = ['membre', 'vol', 'lieu', 'commentaire', 'note'];
            $brut   = (string) ($_GET['filtre'] ?? '');
            $filtre = in_array($brut, $types, true) ? $brut : null;

            $activites = Activite::recentes(60, $filtre);

            // Enrichissement en 2 requêtes batch : has_ip (pilotes) + nb_contribs (lieux).
            $idsPilotes = [];
            $idsLieux   = [];
            foreach ($activites as $a) {
                if ($a['type'] === 'membre' && $a['acteur_id'] !== null) {
                    $idsPilotes[] = (int) $a['acteur_id'];
                }
                if ($a['type'] === 'lieu' && $a['id_entite'] !== null) {
                    $idsLieux[] = (int) $a['id_entite'];
                }
            }

            if ($idsPilotes) {
                $in = implode(',', array_unique($idsPilotes));
                try {
                    $rows = Database::pdo()->query(
                        "SELECT id,
                                (ip_derniere_connexion IS NOT NULL AND ip_derniere_connexion != '') AS has_ip
                         FROM utilisateurs WHERE id IN ($in)"
                    )->fetchAll(\PDO::FETCH_KEY_PAIR);
                    foreach ($rows as $uid => $hi) {
                        $hasIp[(int) $uid] = (bool) $hi;
                    }
                } catch (\Throwable) {
                    // Colonne ip_derniere_connexion absente si migration pas encore jouée.
                }
            }

            if ($idsLieux) {
                $in  = implode(',', array_unique($idsLieux));
                $rows = Database::pdo()->query(
                    "SELECT id_lieu, COUNT(DISTINCT id_utilisateur) AS nb
                     FROM releves WHERE id_lieu IN ($in) GROUP BY id_lieu"
                )->fetchAll(\PDO::FETCH_KEY_PAIR);
                foreach ($rows as $lid => $nb) {
                    $nbContribs[(int) $lid] = (int) $nb;
                }
            }
        }

        (new View())->render('admin/index', [
            'title'      => t('page.admin.title'),
            'section'    => $section,
            'newsVue'    => $newsVue,
            'actuOld'    => $actuOld,
            'actuEdit'   => $actuEdit,
            'actuListe'  => $actuListe,
            'utilisateurs' => $utilisateurs,
            'activites'  => $activites,
            'filtre'     => $filtre,
            'hasIp'      => $hasIp,
            'nbContribs' => $nbContribs,
            'flash'      => $flash,
        ]);
    }

    // -------------------------------------------------------------------------
    //  Actions de suppression (POST, CSRF requis, rôle admin requis)
    // -------------------------------------------------------------------------

    public function supprimerVol(int $id): void
    {
        if (!$this->garderAdmin()) return;
        if (!$this->garderCsrf())  return;

        Database::pdo()->prepare('DELETE FROM vols WHERE id = ?')->execute([$id]);
        $this->flash('ok', 'admin.deleted_vol');
        $this->rediriger('/admin');
    }

    public function supprimerLieu(int $id): void
    {
        if (!$this->garderAdmin()) return;
        if (!$this->garderCsrf())  return;

        Database::pdo()->prepare('DELETE FROM lieux WHERE id = ?')->execute([$id]);
        $this->flash('ok', 'admin.deleted_lieu');
        $this->rediriger('/admin');
    }

    public function supprimerCommentaire(int $id): void
    {
        if (!$this->garderAdmin()) return;
        if (!$this->garderCsrf())  return;

        Database::pdo()->prepare('DELETE FROM commentaires WHERE id = ?')->execute([$id]);
        $this->flash('ok', 'admin.deleted_commentaire');
        $this->rediriger('/admin');
    }

    public function supprimerNote(int $id): void
    {
        if (!$this->garderAdmin()) return;
        if (!$this->garderCsrf())  return;

        Database::pdo()->prepare('DELETE FROM notes WHERE id = ?')->execute([$id]);
        $this->flash('ok', 'admin.deleted_note');
        $this->rediriger('/admin');
    }

    public function supprimerPilote(int $id): void
    {
        if (!$this->garderAdmin()) return;
        if (!$this->garderCsrf())  return;

        // Retour vers l'onglet d'où vient l'action (liste « Utilisateurs ») si demandé.
        $retour = ($_POST['retour'] ?? '') === 'utilisateurs' ? '/admin?section=utilisateurs' : '/admin';

        // Interdire l'auto-suppression.
        if ($id === Auth::id()) {
            $this->flash('err', 'admin.error_self');
            $this->rediriger($retour);
            return;
        }

        Database::pdo()->prepare('DELETE FROM utilisateurs WHERE id = ?')->execute([$id]);
        $this->flash('ok', 'admin.deleted_pilote');
        $this->rediriger($retour);
    }

    public function bannirPilote(int $id): void
    {
        if (!$this->garderAdmin()) return;
        if (!$this->garderCsrf())  return;

        if ($id === Auth::id()) {
            $this->flash('err', 'admin.error_self');
            $this->rediriger('/admin');
            return;
        }

        // Récupère l'IP avant de supprimer le compte.
        $st = Database::pdo()->prepare('SELECT ip_derniere_connexion FROM utilisateurs WHERE id = ?');
        $st->execute([$id]);
        $ip = (string) ($st->fetchColumn() ?? '');

        Database::pdo()->prepare('DELETE FROM utilisateurs WHERE id = ?')->execute([$id]);

        if ($ip !== '') {
            IpBannie::bannir($ip, (int) Auth::id(), 'banni par admin');
            $this->flash('ok', 'admin.banned_pilote');
        } else {
            $this->flash('ok', 'admin.banned_pilote_no_ip');
        }

        $this->rediriger('/admin');
    }

    // -------------------------------------------------------------------------
    //  Actualités (« News »)
    // -------------------------------------------------------------------------

    /** Enregistre une actualité : création, ou modification si un id est fourni. */
    public function enregistrerActualite(): void
    {
        if (!$this->garderAdmin()) return;
        if (!$this->garderCsrf())  return;

        $id      = (int) ($_POST['id'] ?? 0);
        $titre   = trim((string) ($_POST['titre'] ?? ''));
        $titre   = trim(strip_tags($titre));                 // titre = texte simple
        $contenu = HtmlSanitizer::propre((string) ($_POST['contenu'] ?? ''));

        // Validation (longueur sur le texte VISIBLE pour le HTML riche).
        $erreur = null;
        if ($titre === '' || mb_strlen($titre) > self::TITRE_MAX) {
            $erreur = 'admin.actu_err_titre';
        } elseif (Actualite::longueurTexte($contenu) === 0) {
            $erreur = 'admin.actu_err_contenu_vide';
        } elseif (Actualite::longueurTexte($contenu) > self::CONTENU_MAX) {
            $erreur = 'admin.actu_err_contenu_long';
        }

        if ($erreur !== null) {
            // Reprise de saisie : on conserve ce que l'admin a tapé (et l'id éventuel).
            $_SESSION['actu_old'] = ['id' => $id, 'titre' => $titre, 'contenu' => $contenu];
            $this->flash('err', $erreur);
            $this->rediriger('/admin?section=news' . ($id > 0 ? '&id=' . $id : ''));
            return;
        }

        if ($id > 0 && Actualite::parId($id) !== null) {
            Actualite::modifier($id, $titre, $contenu);
            $this->flash('ok', 'admin.actu_updated');
        } else {
            Actualite::creer((int) Auth::id(), $titre, $contenu);
            $this->flash('ok', 'admin.actu_saved');
        }
        $this->rediriger('/admin?section=news&vue=gerer');
    }

    /** Supprime une actualité. */
    public function supprimerActualite(int $id): void
    {
        if (!$this->garderAdmin()) return;
        if (!$this->garderCsrf())  return;

        Actualite::supprimer($id);
        $this->flash('ok', 'admin.actu_deleted');
        $this->rediriger('/admin?section=news&vue=gerer');
    }

    /**
     * Endpoint d'upload d'image pour l'éditeur TinyMCE. Reçoit un fichier
     * ($_FILES['file']), le convertit en WebP (storage/uploads) et renvoie
     * { location } en JSON. CSRF + rôle admin requis.
     */
    public function televerserImage(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::estAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        if (!Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['error' => 'csrf']);
            return;
        }

        try {
            $uid  = 'actu_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
            $nom  = Upload::enregistrerCapture($_FILES['file'] ?? [], $uid, (int) Auth::id());
            echo json_encode(['location' => BASE_URL . '/uploads/' . $nom]);
        } catch (\Throwable $e) {
            http_response_code(422);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    //  Helpers privés
    // -------------------------------------------------------------------------

    private function garderAdmin(): bool
    {
        if (Auth::estAdmin()) {
            return true;
        }
        http_response_code(404);
        (new View())->render('errors/404', ['title' => t('page.404.title')]);
        return false;
    }

    private function garderCsrf(): bool
    {
        if (Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            return true;
        }
        $this->flash('err', 'error.csrf');
        $this->rediriger('/admin');
        return false;
    }

    private function flash(string $type, string $cle): void
    {
        $_SESSION['admin_flash'] = ['type' => $type, 'cle' => $cle];
    }

    private function rediriger(string $chemin): void
    {
        header('Location: ' . BASE_URL . $chemin);
        exit;
    }
}
