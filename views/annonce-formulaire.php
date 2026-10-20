<?php $titre = 'Miaoumi — Publier une annonce'; require __DIR__ . '/header.php'; ?>
<h1>Publier une annonce de garde</h1>
<p class="intro">Présente ton animal et indique les dates auxquelles tu as besoin d’un gardien.</p>
<?php if ($erreurs): ?>
    <div class="message message-erreur" role="alert">
        <p>Ton annonce n’a pas été publiée. Vérifie les champs ci-dessous.</p>
        <?php if (isset($erreurs['general'])): ?><p><?= e($erreurs['general']) ?></p><?php endif; ?>
    </div>
<?php endif; ?>
<form class="formulaire" method="post" action="?page=creer-annonce">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <?php foreach (['titre' => ['Titre', 'text', 150], 'type_animal' => ['Type d’animal', 'text', 100], 'date_debut' => ['Date de début', 'date', null], 'date_fin' => ['Date de fin', 'date', null]] as $champ => $options): ?>
        <div class="champ">
            <label for="<?= e($champ) ?>"><?= e($options[0]) ?></label>
            <input id="<?= e($champ) ?>" name="<?= e($champ) ?>" type="<?= e($options[1]) ?>" value="<?= e($donnees[$champ]) ?>" required
                <?= $options[2] ? 'maxlength="' . (int) $options[2] . '"' : '' ?>
                <?= isset($erreurs[$champ]) ? 'aria-invalid="true" aria-describedby="erreur-' . e($champ) . '"' : '' ?>>
            <?php if (isset($erreurs[$champ])): ?><p class="erreur-champ" id="erreur-<?= e($champ) ?>"><?= e($erreurs[$champ]) ?></p><?php endif; ?>
        </div>
    <?php endforeach; ?>
    <div class="champ">
        <label for="description">Description (facultative, 5 000 caractères maximum)</label>
        <textarea id="description" name="description" rows="5" maxlength="5000" <?= isset($erreurs['description']) ? 'aria-invalid="true" aria-describedby="erreur-description"' : '' ?>><?= e($donnees['description']) ?></textarea>
        <?php if (isset($erreurs['description'])): ?><p id="erreur-description" class="erreur-champ"><?= e($erreurs['description']) ?></p><?php endif; ?>
    </div>
    <button type="submit">Publier mon annonce</button>
    <a href="?page=annonces">Annuler</a>
</form>
<?php require __DIR__ . '/footer.php'; ?>
