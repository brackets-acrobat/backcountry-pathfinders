<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Api;
use App\Core\View;
use App\Models\Lieu;

/*
 * Page principale : la carte des lieux de poser, et l'endpoint JSON
 * qui l'alimente en marqueurs.
 */
class CarteController
{
    /** Page HTML de la carte. */
    public function index(): void
    {
        (new View())->render('carte', [
            'title' => t('page.map.title'),
        ]);
    }

    /** Données des lieux pour la carte (JSON public, consommé en JS). */
    public function lieux(): void
    {
        Api::repondre([
            'ok'    => true,
            'lieux' => Lieu::tousPourCarte(),
        ]);
    }
}
