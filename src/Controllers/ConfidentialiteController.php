<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

/*
 * Politique de confidentialité (RGPD), bilingue. Page statique : le contenu
 * vit dans les fichiers de langue (clés privacy.*).
 */
class ConfidentialiteController
{
    public function index(): void
    {
        (new View())->render('confidentialite', [
            'title' => t('privacy.title'),
        ]);
    }
}
