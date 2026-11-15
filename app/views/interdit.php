<?php $titre = 'Miaoumi — Accès refusé'; require __DIR__ . '/header.php'; ?>
<h1>Accès refusé</h1>
<div class="carte">
    <p><?= e($messageInterdit ?? 'Tu n’as pas le droit de faire cette action.') ?></p>
    <p><a class="bouton" href="?page=annonces">Retour aux annonces</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
