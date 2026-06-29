<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Actualite;

/*
 * Page d'accueil du site. La carte a sa propre page /carte ; l'accueil
 * affiche la lettre d'accueil puis les dernières actualités.
 */
class AccueilController
{
    public function index(): void
    {
        (new View())->render('accueil', [
            'title'      => t('page.home.title'),
            'actualites' => Actualite::dernieres(5),
        ]);
    }
}
