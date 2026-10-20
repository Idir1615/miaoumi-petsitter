<?php
$pageCourante = is_string($_GET['page'] ?? null) ? $_GET['page'] : 'annonces';
$authPage = in_array($pageCourante, ['connexion', 'inscription'], true);
?>
<nav class="navigation <?= $authPage ? 'navigation-auth' : '' ?>" aria-label="Navigation principale">
    <a class="marque" href="?page=annonces" aria-label="Miaoumi — accueil"><img src="assets/miaoumi-logo.png" alt="" width="48" height="48"></a>
    <?php if (!$authPage): ?>
        <form class="recherche-navigation" method="get" action="">
            <input type="hidden" name="page" value="annonces">
            <label class="visuellement-cache" for="recherche-nav">Rechercher une annonce</label>
            <input id="recherche-nav" name="q" type="search" placeholder="Rechercher une annonce..." maxlength="150" value="<?= e(is_string($_GET['q'] ?? null) ? $_GET['q'] : '') ?>">
        </form>
        <a class="lien-navigation <?= $pageCourante === 'annonces' ? 'actif' : '' ?>" href="?page=annonces">Annonces</a>
        <a class="lien-navigation <?= $pageCourante === 'mes-annonces' ? 'actif' : '' ?>" href="?page=mes-annonces">Mes annonces</a>
        <a class="lien-navigation <?= $pageCourante === 'mes-candidatures' ? 'actif' : '' ?>" href="?page=mes-candidatures">Mes candidatures</a>
        <a class="bouton bouton-deposer" href="?page=creer-annonce">Déposer une annonce</a>
    <?php endif; ?>
    <?php if (isset($_SESSION['utilisateur'])): ?>
        <details class="menu-compte">
            <summary class="avatar" aria-label="Menu du compte de <?= e($_SESSION['utilisateur']['nom']) ?>"><?= e(mb_strtoupper(mb_substr($_SESSION['utilisateur']['nom'], 0, 1))) ?></summary>
            <div class="menu-compte-contenu">
                <span>Bonjour <?= e($_SESSION['utilisateur']['nom']) ?> !</span>
                <form method="post" action="?page=deconnexion">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <button class="bouton-secondaire" type="submit">Se déconnecter</button>
                </form>
            </div>
        </details>
    <?php else: ?>
        <div class="liens-compte">
            <a class="bouton bouton-secondaire" href="?page=connexion">Connexion</a>
            <a class="bouton" href="?page=inscription">Inscription</a>
        </div>
    <?php endif; ?>
</nav>
