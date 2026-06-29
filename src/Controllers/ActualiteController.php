<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Actualite;

/*
 * Page publique d'une actualité (« News »). Seules les actualités publiées
 * sont visibles ; sinon 404.
 */
class ActualiteController
{
    public function detail(string $id): void
    {
        $actualite = Actualite::detailPublie((int) $id);

        if ($actualite === null) {
            http_response_code(404);
            (new View())->render('errors/404', ['title' => t('page.404.title')]);
            return;
        }

        (new View())->render('actualite/detail', [
            'title'     => (string) $actualite['titre'],
            'actualite' => $actualite,
        ]);
    }
}
