<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Activite;

/*
 * Espace d'administration, réservé aux utilisateurs de rôle « admin ».
 * Les non-administrateurs (et les visiteurs) reçoivent un 404 : la page
 * n'est pas révélée.
 */
class AdminController
{
    public function index(): void
    {
        if (!Auth::estAdmin()) {
            http_response_code(404);
            (new View())->render('errors/404', ['title' => t('page.404.title')]);
            return;
        }

        // Filtre par type d'activité (barre latérale) : valeur validée ou null.
        $types  = ['membre', 'vol', 'lieu', 'commentaire', 'note'];
        $brut   = (string) ($_GET['filtre'] ?? '');
        $filtre = in_array($brut, $types, true) ? $brut : null;

        (new View())->render('admin/index', [
            'title'     => t('page.admin.title'),
            'activites' => Activite::recentes(60, $filtre),
            'filtre'    => $filtre,
        ]);
    }
}
