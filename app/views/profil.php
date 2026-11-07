<?php $titre = 'Miaoumi — Profil de ' . $utilisateur['nom']; require __DIR__ . '/header.php'; ?>
<h1>Profil de <?= e($utilisateur['nom']) ?></h1>
<div class="carte">
    <p class="meta">Note moyenne</p>
    <?php if ($moyenne === null): ?>
        <p><strong>Pas encore d’avis</strong></p>
    <?php else: ?>
        <p><strong><?= e(number_format((float) $moyenne, 1, ',', '')) ?> / 5</strong></p>
    <?php endif; ?>
    <p class="meta"><?= count($avis) ?> avis reçu<?= count($avis) > 1 ? 's' : '' ?></p>
</div>

<h2>Avis reçus</h2>
<?php if (!$avis): ?>
    <div class="etat-vide"><p>Aucun avis reçu pour le moment.</p></div>
<?php endif; ?>
<?php foreach ($avis as $evaluation): ?>
    <article class="carte avis-carte">
        <h3><?= (int) $evaluation['note'] ?> / 5 — par <a href="?page=profil&amp;id=<?= (int) $evaluation['id_auteur'] ?>"><?= e($evaluation['auteur']) ?></a></h3>
        <?php if (($evaluation['commentaire'] ?? '') !== ''): ?><p><?= nl2br(e($evaluation['commentaire'])) ?></p><?php endif; ?>
        <p class="meta">Garde « <a href="?page=avis&amp;annonce=<?= (int) $evaluation['id_annonce'] ?>"><?= e($evaluation['titre']) ?></a> » · <?= e(date('d/m/Y', strtotime($evaluation['dates']))) ?></p>
    </article>
<?php endforeach; ?>
<?php require __DIR__ . '/footer.php'; ?>
