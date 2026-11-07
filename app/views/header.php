<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre ?? 'Miaoumi') ?></title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="site-header">
        <?php require __DIR__ . '/navigation.php'; ?>
    </header>
    <main class="conteneur">
        <?php foreach (lireFlash() as $flash): ?>
            <p class="message <?= $flash['type'] === 'erreur' ? 'message-erreur' : 'message-succes' ?>" role="status"><?= e($flash['message']) ?></p>
        <?php endforeach; ?>
