<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/*
 * Gestion des IP bannies par un administrateur.
 */
class IpBannie
{
    public static function estBannie(string $ip): bool
    {
        $st = Database::pdo()->prepare('SELECT 1 FROM ip_bannies WHERE ip = ? LIMIT 1');
        $st->execute([$ip]);
        return $st->fetchColumn() !== false;
    }

    public static function bannir(string $ip, int $idAdmin, string $raison = ''): void
    {
        Database::pdo()
            ->prepare(
                'INSERT INTO ip_bannies (ip, raison, id_admin) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE raison = VALUES(raison),
                                         id_admin = VALUES(id_admin),
                                         cree_le  = CURRENT_TIMESTAMP'
            )
            ->execute([$ip, $raison, $idAdmin]);
    }
}
