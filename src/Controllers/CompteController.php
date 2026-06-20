<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\CleApi;

/*
 * Espace compte : génération et révocation des clés API de l'utilisateur.
 * La clé en clair n'est affichée qu'une seule fois (flash de session).
 */
class CompteController
{
    public function index(): void
    {
        $this->exigeConnexion();

        // Clé fraîchement créée : reste visible tant qu'on est sur cette page
        // (View::render l'oublie dès qu'une autre page de contenu est affichée).
        $nouvelleCle = $_SESSION['nouvelle_cle'] ?? null;

        (new View())->render('compte/index', [
            'title'       => t('page.account.title'),
            'cles'        => CleApi::parUtilisateur(Auth::id()),
            'nouvelleCle' => $nouvelleCle,
        ]);
    }

    public function creerCle(): void
    {
        $this->exigeConnexion();

        if (Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            $libelle = trim($_POST['libelle'] ?? '');
            $_SESSION['nouvelle_cle'] = CleApi::creer(Auth::id(), $libelle !== '' ? $libelle : null);
        }

        $this->rediriger('/compte');
    }

    public function supprimerCle(): void
    {
        $this->exigeConnexion();

        if (Auth::verifierCsrf($_POST['csrf'] ?? null)) {
            CleApi::supprimer((int) ($_POST['id'] ?? 0), Auth::id());
        }

        $this->rediriger('/compte');
    }

    private function exigeConnexion(): void
    {
        if (!Auth::estConnecte()) {
            $this->rediriger('/connexion');
        }
    }

    private function rediriger(string $chemin): void
    {
        header('Location: ' . BASE_URL . $chemin);
        exit;
    }
}
