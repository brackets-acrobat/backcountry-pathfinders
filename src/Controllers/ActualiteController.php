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
    /** Nombre d'actualités affichées par page sur la liste publique. */
    private const PAR_PAGE = 5;

    /**
     * Liste publique paginée des actualités (« News »), 5 par page,
     * avec liens « Précédent » / « Suivant ».
     */
    public function liste(): void
    {
        $total      = Actualite::compterPubliees();
        $nbPages    = max(1, (int) ceil($total / self::PAR_PAGE));
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $page       = min($page, $nbPages);
        $offset     = ($page - 1) * self::PAR_PAGE;

        $actualites = $total > 0
            ? Actualite::pagePubliees($offset, self::PAR_PAGE)
            : [];

        (new View())->render('actualite/liste', [
            'title'      => t('news.page_title'),
            'actualites' => $actualites,
            'page'       => $page,
            'nbPages'    => $nbPages,
        ]);
    }

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
