<?php

declare(strict_types=1);

use App\Core\View;

/*
 * Partial : corps de la boîte « Écussons / Awards ».
 * Attend $ecussons (sortie de App\Core\Ecussons::pour()) dans la portée appelante.
 *
 * @var array<int,array{categorie:string,palier:int,image:string,titre:string,description:string}> $ecussons
 */

$ecussons = $ecussons ?? [];
?>
<?php if ($ecussons === []): ?>
    <p class="ecussons-vide muted"><?= t('awards.none') ?></p>
<?php else: ?>
    <ul class="ecussons-liste">
        <?php foreach ($ecussons as $e): ?>
            <?php $infobulle = $e['titre'] . ($e['description'] !== '' ? ' — ' . $e['description'] : ''); ?>
            <li class="ecusson ecusson-<?= View::e($e['categorie']) ?>" title="<?= View::e($infobulle) ?>">
                <img class="ecusson-img" src="<?= View::e($e['image']) ?>"
                     alt="<?= View::e($e['titre']) ?>" loading="lazy">
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
