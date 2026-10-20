<?php $titre = 'Miaoumi — Annonces de garde'; require __DIR__ . '/header.php'; ?>
<h1>Annonces de garde</h1>
<p class="intro">Trouve une garde ou publie une annonce pour ton animal.</p>
<a class="bouton" href="?page=creer-annonce">Publier une annonce</a>
<?php if (empty($annonces)): ?>
    <p class="carte">Aucune annonce pour le moment.</p>
<?php else: ?>
    <div class="grille">
        <?php foreach ($annonces as $a): ?>
            <article class="carte">
                <h2><?= e($a['titre']) ?></h2>
                <p><?= e($a['type_animal']) ?> · du <?= e($a['date_debut']) ?> au <?= e($a['date_fin']) ?></p>
                <p><?= nl2br(e($a['description'] ?? '')) ?></p>
                <a href="?page=avis&amp;annonce=<?= (int) $a['id'] ?>">Voir les avis / laisser un avis</a>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
