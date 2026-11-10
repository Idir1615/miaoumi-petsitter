<?php
$titre = 'Miaoumi — Profil de ' . $utilisateur['nom'];
$etoiles = static fn(int $note): string => str_repeat('★', $note) . str_repeat('☆', 5 - $note);
require __DIR__ . '/header.php';
?>
<section class="profil-entete carte">
    <?php if (!empty($utilisateur['photo'])): ?>
        <img class="photo-profil" src="uploads/profils/<?= e($utilisateur['photo']) ?>" alt="Photo de profil de <?= e($utilisateur['nom']) ?>">
    <?php else: ?>
        <span class="avatar avatar-grand" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($utilisateur['nom'], 0, 1))) ?></span>
    <?php endif; ?>
    <div>
        <h1><?= e($utilisateur['nom']) ?></h1>
        <p class="meta">Membre Miaoumi · propriétaire et gardien</p>
    </div>
    <div class="profil-note">
        <?php if ($moyenne === null): ?>
            <strong>Pas encore d’avis</strong>
        <?php else: ?>
            <span class="etoiles" aria-hidden="true"><?= $etoiles((int) round($moyenne)) ?></span>
            <strong><?= e(number_format((float) $moyenne, 1, ',', '')) ?> / 5</strong>
        <?php endif; ?>
        <span class="meta"><?= count($avis) ?> avis reçu<?= count($avis) > 1 ? 's' : '' ?></span>
    </div>
</section>
<?php if ($estMoi && $photoDisponible): ?>
    <details class="carte carte-photo">
        <summary><?= empty($utilisateur['photo']) ? 'Ajouter une photo de profil' : 'Changer ou supprimer ma photo' ?></summary>
        <form method="post" action="?page=photo-profil" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <p><label for="photo">Photo (JPEG ou PNG, 5 Mo maximum)</label><br><input id="photo" name="photo" type="file" accept="image/jpeg,image/png" required></p>
            <p><label><input type="checkbox" name="consentement" value="oui" required> J’accepte que cette photo soit visible par tous sur mon profil public.</label></p>
            <p class="meta">Facultative. La position GPS et les autres métadonnées sont supprimées, l’image est redimensionnée et renommée. Tu peux la supprimer à tout moment : elle est alors effacée du serveur.</p>
            <button type="submit">Enregistrer la photo</button>
        </form>
        <?php if (!empty($utilisateur['photo'])): ?>
            <form method="post" action="?page=photo-profil">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <input type="hidden" name="action" value="supprimer">
                <button class="bouton-secondaire" type="submit">Supprimer ma photo</button>
            </form>
        <?php endif; ?>
    </details>
<?php endif; ?>

<?php if ($annonces): ?>
    <h2 class="titre-section">Annonces en cours (<?= count($annonces) ?>)</h2>
    <div class="grille grille-compacte">
        <?php foreach ($annonces as $a): $image = photoAnimal($a); ?>
            <a class="mini-annonce carte" href="?page=annonce&amp;id=<?= (int) $a['id'] ?>">
                <img class="<?= $image['photo'] ? 'photo' : 'illustration' ?>" src="<?= e($image['src']) ?>" alt="">
                <span><strong><?= e($a['titre']) ?></strong><br><span class="meta"><?= e($a['type_animal']) ?><?= !empty($a['ville']) ? ' · ' . e($a['ville']) : '' ?> · du <?= e(date('d/m', strtotime($a['date_debut']))) ?> au <?= e(date('d/m', strtotime($a['date_fin']))) ?></span></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2 class="titre-section">Avis reçus</h2>
<?php if (!$avis): ?>
    <div class="etat-vide"><p>Aucun avis reçu pour le moment.</p></div>
<?php endif; ?>
<?php foreach ($avis as $evaluation): ?>
    <article class="carte avis-carte">
        <div class="avis-entete">
            <span class="avatar avatar-petit" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($evaluation['auteur'], 0, 1))) ?></span>
            <p>par <a href="?page=profil&amp;id=<?= (int) $evaluation['id_auteur'] ?>"><?= e($evaluation['auteur']) ?></a></p>
            <span class="etoiles" aria-label="<?= (int) $evaluation['note'] ?> sur 5"><?= $etoiles((int) $evaluation['note']) ?></span>
        </div>
        <?php if (($evaluation['commentaire'] ?? '') !== ''): ?><p><?= nl2br(e($evaluation['commentaire'])) ?></p><?php endif; ?>
        <p class="meta">Garde « <a href="?page=avis&amp;annonce=<?= (int) $evaluation['id_annonce'] ?>"><?= e($evaluation['titre']) ?></a> » · <?= e(date('d/m/Y', strtotime($evaluation['dates']))) ?></p>
    </article>
<?php endforeach; ?>
<?php require __DIR__ . '/footer.php'; ?>
