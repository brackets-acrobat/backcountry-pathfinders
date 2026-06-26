<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

/*
 * Page de présentation du projet (« À propos »), bilingue.
 */
class PresentationController
{
    public function index(): void
    {
        (new View())->render('presentation', [
            'title' => t('page.presentation.title'),
        ]);
    }
}
