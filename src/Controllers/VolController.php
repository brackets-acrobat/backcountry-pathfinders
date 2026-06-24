<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Vol;

/*
 * Fiche détail d'un vol : en-tête (date, route, aéronef, temps de vol) et la
 * liste de tous ses posers (vignette, surface, perf), chaque poser renvoyant
 * vers son lieu. Consultation réservée aux membres connectés.
 */
class VolController
{
    public function detail(string $id): void
    {
        if (!Auth::estConnecte()) {
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        $idVol = (int) $id;
        $vol = $idVol > 0 ? Vol::parIdAvecPilote($idVol) : null;

        if ($vol === null) {
            http_response_code(404);
            (new View())->render('errors/404', ['title' => t('page.404.title')]);
            return;
        }

        (new View())->render('vol/detail', [
            'title'         => t('flight.detail_title'),
            'vol'           => $vol,
            'atterrissages' => Vol::atterrissages($idVol),
            'estProprio'    => (int) $vol['id_utilisateur'] === Auth::id(),
        ]);
    }
}
