<?php
$titre = 'Miaoumi — ' . $annonce['titre'];
$image = photoAnimal($annonce); // photoUrl() de l'annonce, avec en plus le type d'image (photo ou illustration)
$libellesStatut = ['ouverte' => 'Ouverte', 'pourvue' => 'Pourvue', 'terminee' => 'Terminée'];
$debut = new DateTimeImmutable($annonce['date_debut']);
$fin = new DateTimeImmutable($annonce['date_fin']);
$duree = $debut->diff($fin)->days + 1; // le premier et le dernier jour comptent
require __DIR__ . '/header.php';
?>
<p class="fil-ariane"><a href="?page=annonces">← Retour aux annonces</a></p>
<div class="detail">
    <div class="detail-visuel">
        <img class="<?= $image['photo'] ? 'photo' : 'illustration' ?>" src="<?= e($image['src']) ?>" alt="Photo d’illustration : <?= e(mb_strtolower($annonce['type_animal'])) ?>">
    </div>
    <div class="detail-infos carte">
        <span class="badge badge-statut statut-<?= e($annonce['statut']) ?>"><?= e($libellesStatut[$annonce['statut']] ?? $annonce['statut']) ?></span>
        <h1><?= e($annonce['titre']) ?></h1>
        <dl class="caracteristiques">
            <div><dt>Animal</dt><dd><?= e($annonce['type_animal']) ?></dd></div>
            <?php if (!empty($annonce['ville'])): ?><div><dt>Ville</dt><dd><?= e($annonce['ville']) ?></dd></div><?php endif; ?>
            <div><dt>Dates</dt><dd>du <?= e($debut->format('d/m/Y')) ?> au <?= e($fin->format('d/m/Y')) ?> (<?= $duree ?> jour<?= $duree > 1 ? 's' : '' ?>)</dd></div>
            <div><dt>Candidatures</dt><dd><?= (int) $annonce['nombre_candidatures'] ?></dd></div>
        </dl>
        <a class="proprietaire carte-proprietaire" href="?page=profil&amp;id=<?= (int) $annonce['id_proprietaire'] ?>">
            <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($annonce['nom_proprietaire'], 0, 1))) ?></span>
            <span><strong><?= e($annonce['nom_proprietaire']) ?></strong><br><span class="meta"><?= $noteProprietaire !== null ? '★ ' . e(number_format($noteProprietaire, 1, ',', '')) . ' / 5' : 'Pas encore d’avis' ?> · voir le profil</span></span>
        </a>
        <?php if ($peutPostuler): ?>
            <a class="bouton bouton-large" href="?page=postuler&amp;annonce=<?= (int) $annonce['id'] ?>"><?= $utilisateur ? 'Postuler à cette annonce' : 'Se connecter pour postuler' ?></a>
        <?php elseif ($estProprietaire): ?>
            <a class="bouton bouton-secondaire bouton-large" href="?page=mes-annonces">Gérer dans Mes annonces</a>
        <?php else: ?>
            <p class="meta">Cette annonce n’accepte plus de candidatures.</p>
        <?php endif; ?>
        <?php if ($utilisateur && !$estProprietaire): ?>
            <form method="post" action="?page=basculer_favori">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <input type="hidden" name="id" value="<?= (int) $annonce['id'] ?>">
                <input type="hidden" name="retour" value="fiche">
                <button class="bouton-secondaire bouton-large bouton-favori-fiche <?= $estFavori ? 'actif' : '' ?>" type="submit" aria-pressed="<?= $estFavori ? 'true' : 'false' ?>"><?= icone('coeur') ?> <?= $estFavori ? 'Retirer des favoris' : 'Ajouter aux favoris' ?></button>
            </form>
        <?php endif; ?>
        <?php if ($estProprietaire): ?>
            <p class="meta"><?= (int) $interesses ?> gardien<?= $interesses > 1 ? 's' : '' ?> intéressé<?= $interesses > 1 ? 's' : '' ?> (en favori)</p>
        <?php endif; ?>
        <?php if ($peutModifier): ?>
            <div class="actions-formulaire">
                <a class="bouton bouton-secondaire" href="?page=modifier-annonce&amp;id=<?= (int) $annonce['id'] ?>">Modifier</a>
                <details class="cloture">
                    <summary class="bouton bouton-secondaire">Supprimer</summary>
                    <form class="form-cloture" method="post" action="?page=supprimer-annonce">
                        <p>Supprimer « <?= e($annonce['titre']) ?> » ? Ses candidatures seront aussi supprimées.</p>
                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                        <input type="hidden" name="annonce" value="<?= (int) $annonce['id'] ?>">
                        <button type="submit">Confirmer la suppression</button>
                    </form>
                </details>
            </div>
        <?php endif; ?>
    </div>
</div>

<section class="carte detail-description">
    <h2>Description des besoins</h2>
    <p><?= $annonce['description'] !== null && $annonce['description'] !== '' ? nl2br(e($annonce['description'])) : 'Le propriétaire n’a pas ajouté de description.' ?></p>
</section>

<h2 class="titre-section">Avis sur cette garde (<?= count($avis) ?>)</h2>
<?php if (!$avis): ?><div class="etat-vide"><p>Aucun avis pour cette garde.</p></div><?php endif; ?>
<?php foreach ($avis as $evaluation): ?>
    <article class="carte avis-carte">
        <div class="avis-entete">
            <span class="avatar avatar-petit" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($evaluation['auteur'], 0, 1))) ?></span>
            <p><a href="?page=profil&amp;id=<?= (int) $evaluation['id_auteur'] ?>"><?= e($evaluation['auteur']) ?></a> → <a href="?page=profil&amp;id=<?= (int) $evaluation['id_cible'] ?>"><?= e($evaluation['cible']) ?></a></p>
            <span class="etoiles" aria-label="<?= (int) $evaluation['note'] ?> sur 5"><?= str_repeat('★', (int) $evaluation['note']) . str_repeat('☆', 5 - (int) $evaluation['note']) ?></span>
        </div>
        <?php if (($evaluation['commentaire'] ?? '') !== ''): ?><p><?= nl2br(e($evaluation['commentaire'])) ?></p><?php endif; ?>
    </article>
<?php endforeach; ?>
<p><a class="lien-avis" href="?page=avis&amp;annonce=<?= (int) $annonce['id'] ?>">Laisser un avis sur cette garde →</a></p>
<?php require __DIR__ . '/footer.php'; ?>
