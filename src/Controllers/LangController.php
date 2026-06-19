<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Lang;

/*
 * Changement de langue : mémorise le choix puis revient à la page précédente.
 */
class LangController
{
    public function changer(string $code): void
    {
        Lang::definir($code);

        // Retour à la page d'origine, mais uniquement si elle est sur notre site.
        $retour = $_SERVER['HTTP_REFERER'] ?? '';
        $hote   = $_SERVER['HTTP_HOST'] ?? '';
        if ($retour === '' || ($hote !== '' && strpos($retour, '://' . $hote) === false)) {
            $retour = BASE_URL . '/';
        }

        header('Location: ' . $retour);
        exit;
    }
}
