<nav aria-label="Navigation principale">
    <a href="?page=annonces"><img src="assets/miaoumi-logo.png" alt="Miaoumi — accueil" width="110" height="110" style="max-width:100%;height:auto;vertical-align:middle"></a>
    <a href="?page=annonces">Annonces</a>
    <?php if (isset($_SESSION['utilisateur'])): ?>
        <span>Bonjour <?= e($_SESSION['utilisateur']['nom']) ?> !</span>
        <form method="post" action="?page=deconnexion" style="display:inline">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <button type="submit">Se déconnecter</button>
        </form>
    <?php else: ?>
        <a href="?page=inscription">S’inscrire</a>
        <a href="?page=connexion">Se connecter</a>
    <?php endif; ?>
</nav>
