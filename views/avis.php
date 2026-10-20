<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Miaoumi — Avis</title>
</head>
<body>
    <?php require __DIR__ . '/navigation.php'; ?>
    <h1>Avis — <?= e($annonce['titre']) ?></h1>
    <?php if (($_GET['ajoute'] ?? '') === '1'): ?><p role="status">Ton avis a été enregistré.</p><?php endif; ?>
    <?php if ($erreurs): ?>
        <ul role="alert"><?php foreach ($erreurs as $erreur): ?><li><?= e($erreur) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <?php if (!$auteur): ?>
        <p><a href="?page=connexion">Connecte-toi</a> pour laisser un avis si tu as participé à cette garde.</p>
    <?php elseif ($destinataires): ?>
        <h2>Laisser un avis</h2>
        <form method="post" action="?page=avis&amp;annonce=<?= (int) $annonce['id'] ?>">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <p><label>Personne évaluée <select name="cible" required>
                <?php foreach ($destinataires as $personne): ?>
                    <option value="<?= (int) $personne['id'] ?>"><?= e($personne['nom']) ?></option>
                <?php endforeach; ?>
            </select></label></p>
            <p><label>Note <select name="note" required>
                <option value="">Choisir une note</option>
                <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>" <?= $note === (string) $i ? 'selected' : '' ?>><?= $i ?> / 5</option><?php endfor; ?>
            </select></label></p>
            <p><label>Commentaire (facultatif, 2 000 caractères maximum)<br>
                <textarea name="commentaire" maxlength="2000" rows="5" cols="40"><?= e($commentaire) ?></textarea>
            </label></p>
            <button type="submit">Publier mon avis</button>
        </form>
    <?php else: ?>
        <p>Tu peux déposer un avis après la date de fin de la garde, entre le propriétaire et un gardien dont la candidature est acceptée. Un seul avis par personne évaluée est possible pour cette annonce.</p>
    <?php endif; ?>

    <h2>Avis publiés</h2>
    <?php if (!$avis): ?><p>Aucun avis pour cette annonce.</p><?php endif; ?>
    <?php foreach ($avis as $evaluation): ?>
        <article>
            <h3><?= e($evaluation['auteur']) ?> → <?= e($evaluation['cible']) ?> : <?= (int) $evaluation['note'] ?> / 5</h3>
            <p><?= nl2br(e($evaluation['commentaire'] ?? '')) ?></p>
            <p>Enregistré le <?= e($evaluation['dates']) ?></p>
        </article>
    <?php endforeach; ?>
</body>
</html>
