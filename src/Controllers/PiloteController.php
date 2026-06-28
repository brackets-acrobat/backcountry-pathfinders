<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Ecussons;
use App\Core\View;
use App\Models\Utilisateur;
use App\Models\Vol;

/*
 * Liste publique des pilotes de la communauté, avec leurs statistiques
 * de contribution (lieux visités, relevés, vols). Le profil individuel et ses
 * vols sont réservés aux membres connectés.
 */
class PiloteController
{
    public function index(): void
    {
        (new View())->render('pilotes/index', [
            'title'   => t('page.pilots.title'),
            'pilotes' => Utilisateur::tousAvecStats(),
        ]);
    }

    /** Profil public d'un pilote : ses stats de vol + la liste de ses vols. */
    public function profil(string $id): void
    {
        if (!Auth::estConnecte()) {
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        $idPilote = (int) $id;
        $pilote = $idPilote > 0 ? Utilisateur::parId($idPilote) : null;

        if ($pilote === null) {
            http_response_code(404);
            (new View())->render('errors/404', ['title' => t('page.404.title')]);
            return;
        }

        $stats = Vol::statsParUtilisateur($idPilote);

        (new View())->render('pilotes/profil', [
            'title'    => $pilote['pseudo'],
            'pilote'   => $pilote,
            'stats'    => $stats,
            'vols'     => Vol::parUtilisateur($idPilote),
            'ecussons' => Ecussons::pour([
                'flights'   => $stats['nb_vols'],
                'countries' => $stats['nb_pays'],
                'landings'  => $stats['nb_landings'],
            ]),
        ]);
    }
}
