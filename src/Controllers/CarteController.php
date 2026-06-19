<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

/*
 * Page principale : la carte des lieux de poser.
 * (La carte Leaflet et les données seront branchées dans une étape suivante.)
 */
class CarteController
{
    public function index(): void
    {
        (new View())->render('carte', [
            'title' => 'Carte des lieux',
        ]);
    }
}
