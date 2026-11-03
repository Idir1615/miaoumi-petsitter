<?php
$mesAnnonces = $mesAnnonces ?? false;
$recherche = $recherche ?? '';
$animal = $animal ?? '';
$ville = $ville ?? '';
$villeDisponible = $villeDisponible ?? false;
$total = $total ?? count($annonces);
$numero = $numero ?? 1;
$nombrePages = $nombrePages ?? 1;
$statut = $statut ?? '';
$pageListe = $mesAnnonces ? 'mes-annonces' : 'annonces';
$titre = $mesAnnonces ? 'Miaoumi — Mes annonces' : 'Miaoumi — Annonces de garde';
require __DIR__ . '/header.php';
?>
<div class="titre-page">
    <h1><?= $mesAnnonces ? 'Mes annonces' : 'Annonces de garde' ?></h1>
    <?php if ($mesAnnonces): ?><a class="bouton" href="?page=creer-annonce">Publier une annonce</a><?php endif; ?>
</div>
<?php if ($mesAnnonces): ?>
    <div class="statistiques"><div class="carte"><p class="meta">Annonces publiées</p><strong><?= (int) $statistiques['total'] ?></strong></div><div class="carte"><p class="meta">Gardes terminées</p><strong><?= (int) $statistiques['terminees'] ?></strong></div></div>
    <nav class="onglets-statut" aria-label="Statut des annonces"><?php foreach ([''=>'Toutes', 'ouverte'=>'Ouverte', 'pourvue'=>'Pourvue', 'terminee'=>'Terminée'] as $cle=>$libelle): ?><a <?= $statut === $cle ? 'aria-current="page"' : '' ?> href="?<?= e(http_build_query(['page'=>'mes-annonces','statut'=>$cle])) ?>"><?= e($libelle) ?></a><?php endforeach; ?></nav>
<?php endif; ?>
<div class="annonces-layout <?= $mesAnnonces ? 'mes-annonces-layout' : '' ?>">
    <aside class="filtres carte">
        <h2>Filtres</h2>
        <form method="get" action="">
            <input type="hidden" name="page" value="<?= e($pageListe) ?>">
            <?php if ($villeDisponible): ?><div class="champ"><label for="ville">Ville</label><input id="ville" name="ville" placeholder="Ex. Paris" maxlength="100" value="<?= e($ville) ?>"></div><?php endif; ?>
            <div class="champ"><label for="mots-cles">Rechercher</label><input id="mots-cles" name="q" placeholder="Ex. Garde de Milo" value="<?= e($recherche) ?>" maxlength="150"></div>
            <div class="champ"><label for="animal">Type d’animal</label><select id="animal" name="animal">
                <option value="">Tous</option>
                <?php foreach (['Chien', 'Chat', 'Lapin', 'Oiseau', 'Autre'] as $type): ?><option value="<?= e($type) ?>" <?= $animal === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?>
            </select></div>
            <button type="submit">Appliquer</button>
            <a class="reinitialiser" href="?page=<?= e($pageListe) ?>">Réinitialiser</a>
        </form>
    </aside>
    <section class="resultats" aria-label="Annonces">
        <p class="compteur"><?= $total ?> annonce<?= $total > 1 ? 's' : '' ?> <?= $mesAnnonces ? 'publiée' . ($total > 1 ? 's' : '') : 'ouverte' . ($total > 1 ? 's' : '') ?></p>
        <?php if (empty($annonces)): ?>
            <div class="etat-vide">
                <img src="assets/patte.svg" alt="" width="35" height="35">
                <h2><?= $mesAnnonces && $recherche === '' && $animal === '' ? 'Vous n’avez pas encore publié d’annonce' : 'Aucune annonce pour le moment.' ?></h2>
                <p><?= $recherche !== '' || $animal !== '' ? 'Aucune annonce ne correspond à votre recherche.' : 'Les prochaines gardes apparaîtront ici.' ?></p>
                <a class="bouton bouton-secondaire" href="<?= $mesAnnonces && $recherche === '' && $animal === '' ? '?page=creer-annonce' : '?page=' . e($pageListe) ?>"><?= $mesAnnonces && $recherche === '' && $animal === '' ? 'Publier ma première annonce' : 'Réinitialiser les filtres' ?></a>
            </div>
        <?php else: ?>
            <div class="grille">
                <?php foreach ($annonces as $a): ?>
                    <article class="annonce-carte">
                        <div class="annonce-visuel"><img src="assets/patte.svg" alt="" width="36" height="36"></div>
                        <div class="annonce-contenu">
                            <span class="badge"><?= e(['ouverte'=>'Ouverte', 'pourvue'=>'Pourvue', 'terminee'=>'Terminée'][$a['statut'] ?? 'ouverte'] ?? $a['statut']) ?></span>
                            <h2><?= e($a['titre']) ?></h2>
                            <p class="meta"><?= e($a['type_animal']) ?><?= !empty($a['ville']) ? ' · ' . e($a['ville']) : '' ?></p>
                            <p class="meta"><time datetime="<?= e($a['date_debut']) ?>"><?= e(date('d/m', strtotime($a['date_debut']))) ?></time> → <time datetime="<?= e($a['date_fin']) ?>"><?= e(date('d/m', strtotime($a['date_fin']))) ?></time></p>
                            <?php if (!empty($a['description'])): ?><p class="description-annonce"><?= nl2br(e($a['description'])) ?></p><?php endif; ?>
                            <a class="lien-avis" href="?page=avis&amp;annonce=<?= (int) $a['id'] ?>">Voir les avis / laisser un avis <span aria-hidden="true">→</span></a>
                            <?php if ($mesAnnonces): ?><a class="lien-avis" href="?page=candidatures-recues&amp;annonce=<?= (int) $a['id'] ?>">Candidatures reçues →</a><?php endif; ?>
                            <?php if ($mesAnnonces && ($a['statut'] ?? '') !== 'terminee'): ?>
                                <details class="cloture">
                                    <summary class="bouton bouton-secondaire">Terminer la garde</summary>
                                    <form class="form-cloture" method="post" action="?page=cloturer-annonce">
                                        <p>Clôturer « <?= e($a['titre']) ?> » ? Cette action est définitive.</p>
                                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                        <input type="hidden" name="annonce" value="<?= (int) $a['id'] ?>">
                                        <button type="submit">Confirmer la clôture</button>
                                    </form>
                                </details>
                            <?php endif; ?>
                            <?php
                            $connecte = isset($_SESSION['utilisateur']['id']);
                            $annonceAccessible = !$mesAnnonces
                                && ($a['statut'] ?? '') === 'ouverte'
                                && $a['date_fin'] >= $aujourdHui;
                            $estSaPropreAnnonce = $connecte
                                && (int) $_SESSION['utilisateur']['id'] === (int) $a['id_proprietaire'];
                            ?>
                            <?php if ($annonceAccessible && !$estSaPropreAnnonce): ?>
                                <a class="lien-avis" href="?page=postuler&amp;annonce=<?= (int) $a['id'] ?>"><?= $connecte ? 'Postuler à cette annonce' : 'Se connecter pour postuler' ?> →</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($nombrePages > 1): ?><nav class="pagination" aria-label="Pagination des annonces">
                <?php for ($i = 1; $i <= $nombrePages; $i++): ?><a <?= $i === $numero ? 'aria-current="page"' : '' ?> href="?<?= e(http_build_query(['page'=>$pageListe,'q'=>$recherche,'animal'=>$animal,'ville'=>$ville,'statut'=>$statut,'numero'=>$i])) ?>"><?= $i ?></a><?php endfor; ?>
            </nav><?php endif; ?>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/footer.php'; ?>
