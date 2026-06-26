<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Models\Activite;
use App\Models\IpBannie;

/*
 * Espace d'administration, réservé aux utilisateurs de rôle « admin ».
 * Les non-administrateurs (et les visiteurs) reçoivent un 404 : la page
 * n'est pas révélée.
 */
class AdminController
{
    public function index(): void
    {
        if (!Auth::estAdmin()) {
            http_response_code(404);
            (new View())->render('errors/404', ['title' => t('page.404.title')]);
            return;
        }

        $flash = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);

        // Filtre par type d'activité (barre latérale) : valeur validée ou null.
        $types  = ['membre', 'vol', 'lieu', 'commentaire', 'note'];
        $brut   = (string) ($_GET['filtre'] ?? '');
        $filtre = in_array($brut, $types, true) ? $brut : null;

        $activites = Activite::recentes(60, $filtre);

        // Enrichissement en 2 requêtes batch : has_ip (pilotes) + nb_contribs (lieux).
        $hasIp     = [];
        $nbContribs = [];

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

        (new View())->render('admin/index', [
            'title'      => t('page.admin.title'),
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

        // Interdire l'auto-suppression.
        if ($id === Auth::id()) {
            $this->flash('err', 'admin.error_self');
            $this->rediriger('/admin');
            return;
        }

        Database::pdo()->prepare('DELETE FROM utilisateurs WHERE id = ?')->execute([$id]);
        $this->flash('ok', 'admin.deleted_pilote');
        $this->rediriger('/admin');
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
