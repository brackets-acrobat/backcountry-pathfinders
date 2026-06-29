<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

/*
 * Mentions légales (LCEN), bilingue. Page statique : le contenu vit dans les
 * fichiers de langue (clés legal_notice.*).
 */
class MentionsLegalesController
{
    public function index(): void
    {
        (new View())->render('mentions-legales', [
            'title' => t('legal_notice.title'),
        ]);
    }
}
