<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

/*
 * Page d'accueil du site. Volontairement minimale pour l'instant
 * (la carte a été déplacée vers sa propre page /carte).
 */
class AccueilController
{
    public function index(): void
    {
        (new View())->render('accueil', [
            'title' => t('page.home.title'),
        ]);
    }
}
