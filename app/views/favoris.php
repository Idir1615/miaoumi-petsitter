<?php
$titre = 'Miaoumi — Mes favoris';
$libellesStatut = ['ouverte' => 'Ouverte', 'pourvue' => 'Pourvue', 'terminee' => 'Terminée'];
$modeleAnnonce = new Annonce();
require __DIR__ . '/header.php';
?>
<div class="titre-page">
    <h1>Mes favoris</h1>
</div>
<p class="compteur"><?= count($annonces) ?> annonce<?= count($annonces) > 1 ? 's' : '' ?> mise<?= count($annonces) > 1 ? 's' : '' ?> de côté, la dernière ajoutée en premier.</p>
<?php if (!$annonces): ?>
    <div class="etat-vide">
        <img src="assets/patte.svg" alt="" width="35" height="35">
        <h2>Aucun favori pour le moment.</h2>
        <p>Sur la fiche d’une annonce, clique sur « Ajouter aux favoris » pour la retrouver ici.</p>
        <a class="bouton bouton-secondaire" href="?page=annonces">Voir les annonces</a>
    </div>
<?php else: ?>
    <div class="grille">
        <?php foreach ($annonces as $a): ?>
            <article class="annonce-carte">
                <a class="annonce-visuel" href="?page=annonce&amp;id=<?= (int) $a['id'] ?>" tabindex="-1" aria-hidden="true">
                    <img class="photo" src="<?= e($modeleAnnonce->photoUrl($a)) ?>" alt="" loading="lazy">
                    <span class="badge badge-statut statut-<?= e($a['statut']) ?>"><?= e($libellesStatut[$a['statut']] ?? $a['statut']) ?></span>
                </a>
                <div class="annonce-contenu">
                    <h2><a href="?page=annonce&amp;id=<?= (int) $a['id'] ?>"><?= e($a['titre']) ?></a></h2>
                    <p class="meta meta-icone"><?= icone('patte', 16) ?><?= e($a['type_animal']) ?><?= !empty($a['ville']) ? ' · ' . e($a['ville']) : '' ?></p>
                    <p class="meta meta-icone"><?= icone('calendrier', 16) ?>Du <?= e(date('d/m', strtotime($a['date_debut']))) ?> au <?= e(date('d/m', strtotime($a['date_fin']))) ?></p>
                    <p class="proprietaire"><span class="avatar avatar-petit" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($a['nom_proprietaire'], 0, 1))) ?></span><a href="?page=profil&amp;id=<?= (int) $a['id_proprietaire'] ?>"><?= e($a['nom_proprietaire']) ?></a></p>
                    <form class="form-retirer" method="post" action="?page=basculer_favori">
                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                        <input type="hidden" name="retour" value="mes_favoris">
                        <button class="bouton-secondaire" type="submit">Retirer</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
