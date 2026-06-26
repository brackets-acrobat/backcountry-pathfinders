<?php

declare(strict_types=1);

namespace App\Core;

/*
 * Filtre de modération basique : détecte les insultes courantes (français +
 * anglais) par expression régulière, pour remplacer par défaut les commentaires
 * et noms de lieux injurieux.
 *
 * Volontairement conservateur : frontières de mots (\b) et UCP, pas de
 * sous-chaînes, afin d'éviter les faux positifs sur des mots légitimes
 * (« cockpit », « connexion », « en retard », « douche », « technique »…).
 * Certaines insultes très ambiguës (« con », « bite »…) sont donc écartées.
 */
final class Moderation
{
    /** Insultes courantes FR + EN. Casse ignorée (i), UTF-8 (u), \w/\b Unicode ((*UCP)). */
    private const PATTERN =
        '/(*UCP)\b(?:'
        // --- Anglais ---
        . 'fuck\w*|motherfuck\w*|shit\w*|bullshit|bitch\w*|'
        . 'ass(?:hole|holes|hat|wipe|wipes)?|bastard\w*|'
        . 'dick(?:head|heads|s)?|cocksucker\w*|cunt\w*|'
        . 'pussy|pussies|prick|pricks|whore\w*|slut\w*|douchebag\w*|'
        . 'wank\w*|twat\w*|bollocks|fag|fags|faggot|faggots|'
        . 'nigg(?:er|ers|a|az|as)|retarded|jackass|dumbass|'
        // --- Français ---
        . 'conn(?:ard|ards|asse|asses|e|es)|salop\w*|salaud[sx]?|'
        . 'pute|putes|putain\w*|encul\w*|enfoir\w*|merd\w*|'
        . 'b[\x{00E2}a]tard\w*|niqu\w*|p[\x{00E9}e]d[\x{00E9}e]s?|p[\x{00E9}e]tasse\w*|'
        . 'abruti\w*|cr[\x{00E9}e]tin\w*|d[\x{00E9}e]bile\w*|imb[\x{00E9}e]cile\w*|'
        . 'couille\w*|tafiole\w*|tarlouze\w*|trouduc\w*|ducon|'
        . 'sale\s+cons?|ta\s+gueule|ferme\s+ta\s+gueule|fils\s+de\s+pute|'
        . 'trou\s+du\s+cul|nique\s+ta\s+m[\x{00E8}e]re|fdp|ntm'
        . ')\b/iu';

    /**
     * Marqueur stocké en base pour un commentaire modéré. On stocke ce jeton
     * (et non le texte traduit) pour pouvoir l'afficher dans la langue du
     * LECTEUR. Caractères de contrôle (US) : un utilisateur ne peut pas le
     * saisir dans un formulaire, donc aucune usurpation possible.
     */
    public const MARQUEUR = "\x1Fcommentaire_modere\x1F";

    /** Vrai si le texte contient une insulte reconnue. */
    public static function estInsultant(string $texte): bool
    {
        return $texte !== '' && preg_match(self::PATTERN, $texte) === 1;
    }

    /**
     * Remplace un commentaire injurieux par le MARQUEUR de modération (traduit
     * à l'affichage via afficher()), sinon renvoie le texte inchangé.
     */
    public static function filtreCommentaire(string $texte): string
    {
        return self::estInsultant($texte) ? self::MARQUEUR : $texte;
    }

    /**
     * Texte d'un commentaire prêt à l'affichage : si c'est le marqueur de
     * modération, on le traduit dans la langue courante (« Commentaire
     * modéré » / « Moderated comment ») ; sinon le texte est renvoyé tel quel.
     */
    public static function afficher(?string $texte): string
    {
        if ($texte === null) {
            return '';
        }

        return $texte === self::MARQUEUR ? t('moderation.comment') : $texte;
    }

    /**
     * Annule un nom de lieu injurieux (NULL → affiché « Lieu sans nom »),
     * sinon renvoie le nom inchangé.
     */
    public static function filtreNomLieu(?string $nom): ?string
    {
        if ($nom === null || $nom === '') {
            return $nom;
        }

        return self::estInsultant($nom) ? null : $nom;
    }
}
