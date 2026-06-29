<?php

declare(strict_types=1);

namespace App\Core;

/*
 * Assainisseur HTML par liste blanche, pour le HTML riche produit par TinyMCE
 * (actualités). Conserve une mise en page simple (titres, listes, gras, liens,
 * images, alignement) et les vidéos intégrées de YouTube/Vimeo ; retire scripts,
 * gestionnaires d'événements (on*), URLs javascript: et tout le reste.
 *
 * Les actualités sont rédigées par des administrateurs (de confiance) ; ce
 * filtre est une défense en profondeur avant un éventuel durcissement
 * (HTMLPurifier) si l'édition s'ouvrait un jour plus largement.
 */
final class HtmlSanitizer
{
    /** Balises autorisées => attributs autorisés pour cette balise. */
    private const TAGS = [
        'p' => ['style'], 'br' => [], 'hr' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'sub' => [], 'sup' => [],
        'h2' => ['style'], 'h3' => ['style'], 'h4' => ['style'],
        'blockquote' => ['style'], 'pre' => [], 'code' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'style'],
        'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder', 'title'],
        'figure' => ['style'], 'figcaption' => [],
        'span' => ['style'], 'div' => ['style'],
        'table' => ['style'], 'thead' => [], 'tbody' => [], 'tr' => [],
        'td' => ['style'], 'th' => ['style'],
    ];

    /** Balises supprimées AVEC leur contenu (jamais déballées). */
    private const TAGS_DANGEREUSES = ['script', 'style', 'head', 'meta', 'link', 'object', 'embed', 'form', 'input'];

    /** Hôtes autorisés pour les <iframe> (intégration vidéo). */
    private const HOTES_IFRAME = ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com'];

    /** Propriétés CSS inline autorisées (mise en page). */
    private const STYLES = ['text-align', 'float', 'margin', 'margin-left', 'margin-right',
                            'margin-top', 'margin-bottom', 'width', 'height', 'max-width'];

    public static function propre(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $doc = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = null;
        foreach ($doc->childNodes as $n) {
            if ($n instanceof \DOMElement) { $root = $n; break; }
        }
        if ($root === null) {
            return '';
        }

        self::nettoieEnfants($root);

        $out = '';
        foreach ($root->childNodes as $n) {
            $out .= $doc->saveHTML($n);
        }
        return trim($out);
    }

    private static function nettoieEnfants(\DOMElement $parent): void
    {
        // Copie de la liste : on modifie l'arbre pendant l'itération.
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child instanceof \DOMComment) {
                $parent->removeChild($child);
                continue;
            }
            if (!($child instanceof \DOMElement)) {
                continue;                                   // nœud texte : conservé
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::TAGS_DANGEREUSES, true)) {
                $parent->removeChild($child);
                continue;
            }

            if (!isset(self::TAGS[$tag])) {
                // Balise inconnue : on la « déballe » (on garde ses enfants nettoyés).
                self::nettoieEnfants($child);
                self::deballer($child);
                continue;
            }

            if ($tag === 'iframe' && !self::iframeAutorisee($child)) {
                $parent->removeChild($child);
                continue;
            }

            self::filtreAttributs($child, self::TAGS[$tag]);
            self::nettoieEnfants($child);
        }
    }

    /** Remplace un élément par ses enfants (dans l'ordre), puis le supprime. */
    private static function deballer(\DOMElement $el): void
    {
        $parent = $el->parentNode;
        if ($parent === null) {
            return;
        }
        while ($el->firstChild !== null) {
            $parent->insertBefore($el->firstChild, $el);
        }
        $parent->removeChild($el);
    }

    /** @param array<int,string> $autorises */
    private static function filtreAttributs(\DOMElement $el, array $autorises): void
    {
        foreach (iterator_to_array($el->attributes ?? []) as $attr) {
            $nom = strtolower($attr->nodeName);
            $val = $attr->nodeValue ?? '';

            if (str_starts_with($nom, 'on') || !in_array($nom, $autorises, true)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }

            if ($nom === 'href') {
                if (!self::lienSur($val)) { $el->removeAttribute('href'); }
            } elseif ($nom === 'src') {
                if (!self::sourceSure($val)) { $el->parentNode?->removeChild($el); return; }
            } elseif ($nom === 'style') {
                $propre = self::styleSur($val);
                if ($propre === '') { $el->removeAttribute('style'); }
                else { $el->setAttribute('style', $propre); }
            } elseif ($nom === 'target') {
                $el->setAttribute('rel', 'noopener noreferrer');   // sécurise target=_blank
            }
        }
    }

    private static function lienSur(string $url): bool
    {
        $url = trim($url);
        if ($url === '') { return false; }
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) { return true; }
        return (bool) preg_match('#^(https?:|mailto:)#i', $url);
    }

    private static function sourceSure(string $url): bool
    {
        $url = trim($url);
        if (str_starts_with($url, '/')) { return true; }            // image locale (/uploads/...)
        return (bool) preg_match('#^https?://#i', $url);
    }

    private static function iframeAutorisee(\DOMElement $el): bool
    {
        $src = trim($el->getAttribute('src'));
        $hote = strtolower((string) parse_url($src, PHP_URL_HOST));
        return $hote !== '' && in_array($hote, self::HOTES_IFRAME, true);
    }

    private static function styleSur(string $style): string
    {
        $sorties = [];
        foreach (explode(';', $style) as $decl) {
            if (!str_contains($decl, ':')) { continue; }
            [$prop, $val] = array_map('trim', explode(':', $decl, 2));
            $prop = strtolower($prop);
            if (!in_array($prop, self::STYLES, true)) { continue; }
            if (preg_match('#(javascript:|expression|url\s*\()#i', $val)) { continue; }
            $sorties[] = $prop . ': ' . $val;
        }
        return implode('; ', $sorties);
    }
}
