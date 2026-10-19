<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Miaoumi — <?= $page === 'inscription' ? 'Inscription' : 'Connexion' ?></title>
</head>
<body>
    <?php require __DIR__ . '/navigation.php'; ?>
    <h1><?= $page === 'inscription' ? 'Créer un compte' : 'Se connecter' ?></h1>
    <?php if ($page === 'connexion' && ($_GET['inscrit'] ?? '') === '1'): ?>
        <p>Ton compte a été créé. Tu peux te connecter.</p>
    <?php endif; ?>
    <?php if ($erreurs): ?>
        <ul role="alert">
            <?php foreach ($erreurs as $erreur): ?><li><?= e($erreur) ?></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="post" action="?page=<?= e($page) ?>">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <?php if ($page === 'inscription'): ?>
            <p><label>Nom <input name="nom" autocomplete="name" maxlength="100" required value="<?= e($nom) ?>"></label></p>
        <?php endif; ?>
        <p><label>E-mail <input type="email" name="email" autocomplete="email" maxlength="150" required value="<?= e($email) ?>"></label></p>
        <p><label>Mot de passe <input type="password" name="password" autocomplete="<?= $page === 'inscription' ? 'new-password' : 'current-password' ?>" <?= $page === 'inscription' ? 'minlength="8"' : '' ?> required></label></p>
        <?php if ($page === 'inscription'): ?>
            <p><label>Confirmer le mot de passe <input type="password" name="confirmation" autocomplete="new-password" required></label></p>
        <?php endif; ?>
        <button type="submit"><?= $page === 'inscription' ? 'Créer mon compte' : 'Me connecter' ?></button>
    </form>
</body>
</html>
