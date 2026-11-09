<?php $titre = 'Miaoumi — Profil de ' . $utilisateur['nom']; require __DIR__ . '/header.php'; ?>
<div class="profil-entete">
    <?php if (!empty($utilisateur['photo'])): ?>
        <img class="photo-profil" src="uploads/profils/<?= e($utilisateur['photo']) ?>" alt="Photo de profil de <?= e($utilisateur['nom']) ?>">
    <?php else: ?>
        <span class="photo-profil photo-vide" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($utilisateur['nom'], 0, 1))) ?></span>
    <?php endif; ?>
    <h1>Profil de <?= e($utilisateur['nom']) ?></h1>
</div>
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
<div class="carte">
    <p class="meta">Note moyenne</p>
    <?php if ($moyenne === null): ?>
        <p><strong>Pas encore d’avis</strong></p>
    <?php else: ?>
        <p><strong><?= e(number_format((float) $moyenne, 1, ',', '')) ?> / 5</strong></p>
    <?php endif; ?>
    <p class="meta"><?= count($avis) ?> avis reçu<?= count($avis) > 1 ? 's' : '' ?></p>
</div>

<h2>Avis reçus</h2>
<?php if (!$avis): ?>
    <div class="etat-vide"><p>Aucun avis reçu pour le moment.</p></div>
<?php endif; ?>
<?php foreach ($avis as $evaluation): ?>
    <article class="carte avis-carte">
        <h3><?= (int) $evaluation['note'] ?> / 5 — par <a href="?page=profil&amp;id=<?= (int) $evaluation['id_auteur'] ?>"><?= e($evaluation['auteur']) ?></a></h3>
        <?php if (($evaluation['commentaire'] ?? '') !== ''): ?><p><?= nl2br(e($evaluation['commentaire'])) ?></p><?php endif; ?>
        <p class="meta">Garde « <a href="?page=avis&amp;annonce=<?= (int) $evaluation['id_annonce'] ?>"><?= e($evaluation['titre']) ?></a> » · <?= e(date('d/m/Y', strtotime($evaluation['dates']))) ?></p>
    </article>
<?php endforeach; ?>
<?php require __DIR__ . '/footer.php'; ?>
