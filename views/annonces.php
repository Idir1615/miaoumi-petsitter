<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Miaoumi - Annonces</title>
</head>
<body>
    <h1>Annonces de garde</h1>

    <?php if (empty($annonces)): ?>
        <p>Aucune annonce pour le moment.</p>
    <?php else: ?>
        <?php foreach ($annonces as $a): ?>
            <div>
                <h2><?= htmlspecialchars($a['titre']) ?></h2>
                <p><?= htmlspecialchars($a['type_animal']) ?> · du <?= $a['date_debut'] ?> au <?= $a['date_fin'] ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
