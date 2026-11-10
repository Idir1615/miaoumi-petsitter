<?php
$titre = 'Miaoumi — Avis';
// Affiche une note sous forme d'étoiles pleines et vides (5 au total).
$etoiles = static fn(int $note): string => str_repeat('★', $note) . str_repeat('☆', 5 - $note);
require __DIR__ . '/header.php';
?>
<p class="fil-ariane"><a href="?page=annonces">← Retour aux annonces</a></p>
<div class="titre-page">
    <div>
        <h1>Avis — <?= e($annonce['titre']) ?></h1>
        <p class="meta"><?= e($annonce['type_animal']) ?><?= !empty($annonce['ville']) ? ' · ' . e($annonce['ville']) : '' ?> · du <?= e(date('d/m/Y', strtotime($annonce['date_debut']))) ?> au <?= e(date('d/m/Y', strtotime($annonce['date_fin']))) ?></p>
    </div>
</div>

<?php if ($erreurs): ?>
    <ul class="message message-erreur" role="alert"><?php foreach ($erreurs as $erreur): ?><li><?= e($erreur) ?></li><?php endforeach; ?></ul>
<?php endif; ?>

<?php if (!$auteur): ?>
    <p class="carte"><a href="?page=connexion">Connecte-toi</a> pour laisser un avis si tu as participé à cette garde.</p>
<?php elseif ($destinataires): ?>
    <form class="formulaire" method="post" action="?page=avis&amp;annonce=<?= (int) $annonce['id'] ?>">
        <h2>Laisser un avis</h2>
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <div class="champ">
            <label for="avis-cible">Personne évaluée</label>
            <select id="avis-cible" name="cible" required>
                <?php // La personne choisie reste sélectionnée si le formulaire est réaffiché après une erreur. ?>
                <?php foreach ($destinataires as $personne): ?>
                    <option value="<?= (int) $personne['id'] ?>" <?= (int) $personne['id'] === $cible ? 'selected' : '' ?>><?= e($personne['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="champ">
            <label for="avis-note">Note</label>
            <select id="avis-note" name="note" required>
                <option value="">Choisir une note</option>
                <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= $note === (string) $i ? 'selected' : '' ?>><?= $etoiles($i) ?> — <?= $i ?> / 5</option><?php endfor; ?>
            </select>
        </div>
        <div class="champ">
            <label for="avis-commentaire">Commentaire <span class="meta">(facultatif, 2 000 caractères maximum)</span></label>
            <textarea id="avis-commentaire" name="commentaire" maxlength="2000" rows="5" placeholder="Comment s’est passée la garde ?"><?= e($commentaire) ?></textarea>
        </div>
        <button type="submit">Publier mon avis</button>
    </form>
<?php elseif (!$gardeFinie): ?>
    <p class="carte">Les avis s’ouvrent à la fin de la garde : après sa date de fin ou sa clôture par le propriétaire.</p>
<?php else: ?>
    <p class="carte">Seuls le propriétaire et les gardiens acceptés peuvent laisser un avis, une seule fois par personne évaluée. Tu n’as plus d’avis à déposer pour cette garde.</p>
<?php endif; ?>

<h2 class="titre-section">Avis publiés (<?= count($avis) ?>)</h2>
<?php if (!$avis): ?><div class="etat-vide"><p>Aucun avis pour cette annonce.</p></div><?php endif; ?>
<?php foreach ($avis as $evaluation): ?>
    <article class="carte avis-carte">
        <div class="avis-entete">
            <span class="avatar avatar-petit" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($evaluation['auteur'], 0, 1))) ?></span>
            <p><a href="?page=profil&amp;id=<?= (int) $evaluation['id_auteur'] ?>"><?= e($evaluation['auteur']) ?></a> → <a href="?page=profil&amp;id=<?= (int) $evaluation['id_cible'] ?>"><?= e($evaluation['cible']) ?></a></p>
            <span class="etoiles" aria-label="<?= (int) $evaluation['note'] ?> sur 5"><?= $etoiles((int) $evaluation['note']) ?></span>
        </div>
        <?php if (($evaluation['commentaire'] ?? '') !== ''): ?><p><?= nl2br(e($evaluation['commentaire'])) ?></p><?php endif; ?>
        <p class="meta">Publié le <?= e(date('d/m/Y à H:i', strtotime($evaluation['dates']))) ?></p>
    </article>
<?php endforeach; ?>
<?php require __DIR__ . '/footer.php'; ?>
