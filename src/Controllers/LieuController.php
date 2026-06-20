<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Commentaire;
use App\Models\Lieu;
use App\Models\Releve;

/*
 * Fiche détail d'un lieu : informations, relevés (profil de relief, sol,
 * friction…), notes moyennes et fil de commentaires.
 */
class LieuController
{
    public function detail(string $id): void
    {
        $idLieu = (int) $id;
        $lieu = $idLieu > 0 ? Lieu::parId($idLieu) : null;

        if ($lieu === null || ($lieu['statut'] ?? 'actif') === 'masque') {
            http_response_code(404);
            (new View())->render('errors/404', ['title' => t('page.404.title')]);
            return;
        }

        (new View())->render('lieu/detail', [
            'title'        => $lieu['nom'] ?: t('place.untitled'),
            'lieu'         => $lieu,
            'agregats'     => Lieu::agregats($idLieu),
            'releves'      => Releve::parLieuAvecAuteur($idLieu),
            'commentaires' => Commentaire::parLieu($idLieu),
        ]);
    }
}
