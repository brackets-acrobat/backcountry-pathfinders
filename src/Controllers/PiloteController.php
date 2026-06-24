<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Utilisateur;

/*
 * Liste publique des pilotes de la communauté, avec leurs statistiques
 * de contribution (lieux visités, relevés).
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
}
