<?php

declare(strict_types=1);

namespace App\Core;

/*
 * Attribution des écussons (badges) aux pilotes selon leurs compteurs.
 *
 * Paliers : la SOURCE DE VÉRITÉ est l'ensemble des images PNG présentes dans
 * public/assets/img/png-{flights,countries,landings}. Un palier sans image
 * n'existe pas (ex. vols 75 : pas de flgts75.png → on garde l'écusson 50).
 *
 * Un pilote ne porte qu'UN écusson par catégorie : le plus haut palier atteint.
 * Le titre et la description proviennent de public/assets/ecussons-awards.dat
 * (UTF-8, « catégorie;nombre;titre;description »), dans la langue active, avec
 * repli sur l'autre langue puis sur un libellé générique si le texte manque
 * (ex. cntrs40.png n'a aucune ligne pays;40 / countries;40 dans le .dat).
 */
final class Ecussons
{
    /** Config par catégorie : clés .dat FR/EN, dossier + préfixe des PNG. */
    private const CATEGORIES = [
        'flights'   => ['fr' => 'vols',          'en' => 'flights',   'dir' => 'png-flights',   'prefix' => 'flgts'],
        'countries' => ['fr' => 'pays',          'en' => 'countries', 'dir' => 'png-countries', 'prefix' => 'cntrs'],
        'landings'  => ['fr' => 'atterrissages', 'en' => 'landings',  'dir' => 'png-landings',  'prefix' => 'lndg'],
    ];

    /** @var array<string,array<int,array{titre:string,desc:string}>>|null  textes[cleDat][num] */
    private static ?array $textes = null;
    /** @var array<string,array<int,string>>|null  tiers[categorie][num] => nom de fichier PNG */
    private static ?array $tiers = null;

    /**
     * Écussons obtenus par un pilote (le plus haut palier atteint par catégorie,
     * dans l'ordre flights, countries, landings ; catégories non atteintes omises).
     *
     * @param array{flights?:int,countries?:int,landings?:int} $compteurs
     * @return array<int,array{categorie:string,palier:int,image:string,titre:string,description:string}>
     */
    public static function pour(array $compteurs): array
    {
        self::charger();
        $lang  = Lang::actuelle() === 'en' ? 'en' : 'fr';
        $autre = $lang === 'fr' ? 'en' : 'fr';
        $resultat = [];

        foreach (self::CATEGORIES as $cat => $cfg) {
            $palier = self::palierAtteint($cat, (int) ($compteurs[$cat] ?? 0));
            if ($palier === null) {
                continue;                                  // aucun écusson dans cette catégorie
            }
            $txt = self::$textes[$cfg[$lang]][$palier]
                ?? self::$textes[$cfg[$autre]][$palier]
                ?? ['titre' => (string) $palier, 'desc' => ''];

            $resultat[] = [
                'categorie'   => $cat,
                'palier'      => $palier,
                'image'       => asset('img/' . $cfg['dir'] . '/' . self::$tiers[$cat][$palier]),
                'titre'       => $txt['titre'],
                'description' => $txt['desc'],
            ];
        }

        return $resultat;
    }

    /** Plus haut palier (avec image) inférieur ou égal à $compte, ou null si aucun. */
    private static function palierAtteint(string $cat, int $compte): ?int
    {
        $atteint = null;
        foreach (array_keys(self::$tiers[$cat] ?? []) as $num) {   // triés croissant
            if ($compte >= $num) {
                $atteint = $num;
            }
        }
        return $atteint;
    }

    private static function charger(): void
    {
        if (self::$textes !== null) {
            return;
        }
        self::$textes = [];
        self::$tiers  = [];
        $racine = dirname(__DIR__, 2);

        // 1) Paliers disponibles = images PNG présentes (source de vérité).
        foreach (self::CATEGORIES as $cat => $cfg) {
            $dir = $racine . '/public/assets/img/' . $cfg['dir'];
            $map = [];
            $motif = '/^' . preg_quote($cfg['prefix'], '/') . '0*([0-9]+)\.png$/i';
            foreach (glob($dir . '/' . $cfg['prefix'] . '*.png') ?: [] as $f) {
                if (preg_match($motif, basename($f), $m)) {
                    $map[(int) $m[1]] = basename($f);
                }
            }
            ksort($map);
            self::$tiers[$cat] = $map;
        }

        // 2) Titres/descriptions depuis le .dat. Les 3 premiers « ; » délimitent ;
        //    la description peut donc contenir des « ; » (et être entre guillemets).
        $fichier = $racine . '/public/assets/ecussons-awards.dat';
        if (!is_file($fichier)) {
            return;
        }
        foreach (file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $ligne) {
            $ligne = trim($ligne);                         // retire aussi le \r des fins CRLF
            if ($ligne === '') {
                continue;
            }
            $p = explode(';', $ligne, 4);
            if (count($p) < 4) {
                continue;
            }
            [$catKey, $num, $titre, $desc] = $p;
            $desc = trim($desc);
            if (strlen($desc) >= 2 && $desc[0] === '"' && substr($desc, -1) === '"') {
                $desc = substr($desc, 1, -1);              // description entre guillemets
            }
            self::$textes[strtolower(trim($catKey))][(int) $num] = [
                'titre' => trim($titre),
                'desc'  => $desc,
            ];
        }
    }
}
