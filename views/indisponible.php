<?php $titre = 'Miaoumi — Base indisponible'; require __DIR__ . '/header.php'; ?>
<h1>La base de données est indisponible</h1>
<div class="carte">
    <?php if (!is_file(__DIR__ . '/../config/config.php')): ?>
        <p>Le fichier <strong>config/config.php</strong> manque dans cette copie du projet.</p>
        <p>Duplique <strong>config/config.example.php</strong> dans le même dossier et renomme la copie <strong>config.php</strong>. L’exemple est prêt pour une installation XAMPP locale standard ; adapte les identifiants si nécessaire.</p>
    <?php endif; ?>
    <p>Vérifie que MySQL est démarré dans XAMPP, que la base miaoumi est importée et que config/config.php contient les bons identifiants.</p>
    <p>Les étapes sont expliquées dans docs/DEMARRAGE-WINDOWS.md.</p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
