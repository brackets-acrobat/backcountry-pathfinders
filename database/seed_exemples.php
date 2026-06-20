<?php

declare(strict_types=1);

/*
 * Seed de démonstration : insère 3 lieux d'exemple (Colombie-Britannique,
 * Alpes françaises, outback australien) avec un relevé, une note et un
 * commentaire chacun, attribués à un compte « pilote de démo ».
 *
 * Idempotent : relancer ne crée pas de doublon (on saute un lieu dont le
 * nom existe déjà). Lancer depuis la racine du projet :
 *
 *   C:\xampp\php\php.exe database/seed_exemples.php
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\Lieu;
use App\Models\Releve;
use App\Models\Utilisateur;

$config = require __DIR__ . '/../config/config.php';
Database::configure($config['db']);
$pdo = Database::pdo();

// --- Compte « pilote de démo » (créé une seule fois) ---
$emailDemo = 'demo@backcountry.local';
$demo = Utilisateur::parEmail($emailDemo);
if ($demo === null) {
    $idDemo = Utilisateur::creer('PiloteDemo', $emailDemo, bin2hex(random_bytes(16)));
    echo "Compte de démo créé (id {$idDemo}).\n";
} else {
    $idDemo = (int) $demo['id'];
    echo "Compte de démo déjà présent (id {$idDemo}).\n";
}

/**
 * Construit un profil de relief simple (passage bas) : liste {d, alt} en mètres.
 */
function profil(int $altBase, array $deltas): array
{
    $pts = [];
    $d = 0;
    foreach ($deltas as $delta) {
        $pts[] = ['d' => $d, 'alt' => $altBase + $delta];
        $d += 50;
    }
    return $pts;
}

// --- Définition des lieux d'exemple ---
$exemples = [
    [
        'nom'         => 'Banc de gravier de la Bella Coola',
        'lat'         => 52.221500,
        'lon'         => -126.512000,
        'alt'         => 38,
        'surface'     => 'Sand',
        'etat'        => 'Normal',
        'friction'    => 0.420,
        'longueur'    => 410,
        'pente'       => 1.2,
        'denivele'    => 5,
        'aeronef'     => 'Cessna 170B',
        'profil'      => profil(38, [0, 1, 2, 1, 3, 2, 4, 3, 5]),
        'commentaire' => 'Banc de gravier le long de la rivière, bien plat sur 400 m. Attention aux galets en bout de bande.',
        'note'        => 4,
        'difficulte'  => 3,
        'discussion'  => 'Posé tôt le matin, vent calme. Surface roulante mais ferme, rien à signaler.',
    ],
    [
        // 2e lieu en Colombie-Britannique, à ~3,5 km du précédent : se regroupe
        // avec lui quand la carte est dézoomée (démonstration du clustering).
        'nom'         => 'Prairie d\'amont, vallée de Bella Coola',
        'lat'         => 52.238000,
        'lon'         => -126.470000,
        'alt'         => 122,
        'surface'     => 'Grass',
        'etat'        => 'Normal',
        'friction'    => 0.540,
        'longueur'    => 320,
        'pente'       => 4.0,
        'denivele'    => 14,
        'aeronef'     => 'Piper PA-18 Super Cub',
        'profil'      => profil(122, [0, 2, 5, 8, 11, 13, 14, 13, 12]),
        'commentaire' => 'Prairie naturelle en léger faux-plat, herbe rase. Sol portant en été, plus mou après la pluie.',
        'note'        => 5,
        'difficulte'  => 2,
        'discussion'  => 'Beau terrain dégagé, approche sans obstacle. Idéal pour un premier poser en brousse dans la vallée.',
    ],
    [
        'nom'         => 'Alpage des Aravis',
        'lat'         => 45.901300,
        'lon'         => 6.451800,
        'alt'         => 1620,
        'surface'     => 'Grass',
        'etat'        => 'Normal',
        'friction'    => 0.560,
        'longueur'    => 280,
        'pente'       => 8.5,
        'denivele'    => 24,
        'aeronef'     => 'Piper PA-18 Super Cub',
        'profil'      => profil(1620, [0, 4, 9, 14, 18, 21, 24, 25, 24]),
        'commentaire' => 'Prairie en pente montante, posé vers l\'amont obligatoire. Herbe rase, sol portant en été.',
        'note'        => 5,
        'difficulte'  => 4,
        'discussion'  => 'Pente soutenue, bien gérer l\'arrondi. Vue magnifique sur la chaîne des Aravis.',
    ],
    [
        'nom'         => 'Piste de station, Outback (NT)',
        'lat'         => -23.512000,
        'lon'         => 133.218000,
        'alt'         => 612,
        'surface'     => 'Dirt',
        'etat'        => 'Normal',
        'friction'    => 0.480,
        'longueur'    => 620,
        'pente'       => 0.6,
        'denivele'    => 3,
        'aeronef'     => 'Cessna 182 Skylane',
        'profil'      => profil(612, [0, 1, 0, 1, 2, 1, 2, 1, 2]),
        'commentaire' => 'Longue bande de terre rouge bien damée près d\'une station. Très dégagée, surface dure et sèche.',
        'note'        => 4,
        'difficulte'  => 2,
        'discussion'  => 'Poussière au roulage mais piste impeccable. Idéale pour s\'entraîner aux posers sur terre.',
    ],
];

foreach ($exemples as $ex) {
    // Idempotence : on saute si un lieu porte déjà ce nom.
    $stmt = $pdo->prepare('SELECT id FROM lieux WHERE nom = :nom LIMIT 1');
    $stmt->execute(['nom' => $ex['nom']]);
    if ($stmt->fetchColumn() !== false) {
        echo "Déjà présent, ignoré : {$ex['nom']}\n";
        continue;
    }

    // 1) Le lieu (avec nom + altitude + créateur).
    $idLieu = Lieu::creer($ex['lat'], $ex['lon'], $idDemo, $ex['alt'], $ex['nom']);

    // 2) Un relevé sur ce lieu (la dédup le rattache au lieu créé ci-dessus).
    Releve::enregistrer([
        'id_utilisateur'   => $idDemo,
        'date_releve'      => date('Y-m-d H:i:s'),
        'latitude'         => $ex['lat'],
        'longitude'        => $ex['lon'],
        'altitude_m'       => $ex['alt'],
        'type_surface'     => $ex['surface'],
        'etat_surface'     => $ex['etat'],
        'friction'         => $ex['friction'],
        'longueur_utile_m' => $ex['longueur'],
        'pente_max_pct'    => $ex['pente'],
        'denivele_m'       => $ex['denivele'],
        'profil_relief'    => $ex['profil'],
        'aeronef'          => $ex['aeronef'],
        'commentaire'      => $ex['commentaire'],
    ]);

    // 3) Une note (appréciation + difficulté) du pilote de démo.
    $pdo->prepare(
        'INSERT INTO notes (id_lieu, id_utilisateur, note, difficulte)
         VALUES (:lieu, :user, :note, :diff)'
    )->execute([
        'lieu' => $idLieu,
        'user' => $idDemo,
        'note' => $ex['note'],
        'diff' => $ex['difficulte'],
    ]);

    // 4) Un commentaire dans le fil communautaire.
    $pdo->prepare(
        'INSERT INTO commentaires (id_lieu, id_utilisateur, texte)
         VALUES (:lieu, :user, :texte)'
    )->execute([
        'lieu'  => $idLieu,
        'user'  => $idDemo,
        'texte' => $ex['discussion'],
    ]);

    echo "Créé : {$ex['nom']} (lieu id {$idLieu})\n";
}

echo "Terminé.\n";
