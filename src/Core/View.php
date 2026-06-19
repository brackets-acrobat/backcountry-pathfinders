<?php

declare(strict_types=1);

namespace App\Core;

/*
 * Rendu de vues PHP simples, enveloppées dans un layout.
 * Usage : (new View())->render('carte', ['title' => 'Carte']);
 */
class View
{
    private string $viewsPath;

    public function __construct()
    {
        $this->viewsPath = dirname(__DIR__) . '/Views/';
    }

    /**
     * @param string              $template  chemin de la vue sans extension (ex. "carte", "errors/404")
     * @param array<string,mixed> $data      variables exposées à la vue
     * @param string|null         $layout    layout enveloppant, ou null pour aucun
     */
    public function render(string $template, array $data = [], ?string $layout = 'layout'): void
    {
        extract($data, EXTR_SKIP);

        // Rendu de la vue dans un tampon → $content
        ob_start();
        require $this->viewsPath . $template . '.php';
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        require $this->viewsPath . $layout . '.php';
    }

    /** Échappement HTML pratique pour les vues. */
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
