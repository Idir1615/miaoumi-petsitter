<?php $titre = 'Miaoumi — Postuler'; require __DIR__ . '/header.php'; ?>
<?php if ($annonce): ?>
    <h1>Postuler — <?= e($annonce['titre']) ?></h1>
    <p class="intro"><?= e($annonce['type_animal']) ?> · du <?= e($annonce['date_debut']) ?> au <?= e($annonce['date_fin']) ?></p>
<?php else: ?>
    <h1>Postuler</h1>
<?php endif; ?>

<?php if ($erreurs): ?>
    <div class="message message-erreur" role="alert">
        <?php foreach ($erreurs as $erreur): ?><p><?= e($erreur) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($annonce && $peutPostuler): ?>
    <form class="formulaire" method="post" action="?page=postuler&amp;annonce=<?= (int) $annonce['id'] ?>">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <div class="champ">
            <label for="message-candidature">Message (facultatif, 2 000 caractères maximum)</label>
            <textarea id="message-candidature" name="message" maxlength="2000" rows="6"><?= e($message) ?></textarea>
        </div>
        <button type="submit">Envoyer ma candidature</button>
        <a href="?page=annonces">Annuler</a>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
