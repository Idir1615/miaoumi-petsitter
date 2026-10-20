<?php $titre = 'Miaoumi — Publier une annonce'; require __DIR__ . '/header.php'; ?>
<section class="publication">
<h1>Publier une annonce</h1>
<p class="intro">Décrivez la garde dont votre animal a besoin.</p>
<?php if ($erreurs): ?><div class="message message-erreur" role="alert"><p>Ton annonce n’a pas été publiée. Vérifie les champs ci-dessous.</p><?php if (isset($erreurs['general'])): ?><p><?= e($erreurs['general']) ?></p><?php endif; ?></div><?php endif; ?>
<form class="formulaire" method="post" action="?page=creer-annonce">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <div class="formulaire-grille">
    <?php foreach (['titre' => ['Titre', 'text', 150, 'Ex. Garde de Milo, week-end'], 'type_animal' => ['Type d’animal', 'text', 100, 'Ex. Chien, chat, lapin...'], 'date_debut' => ['Date de début', 'date', null, ''], 'date_fin' => ['Date de fin', 'date', null, '']] as $champ => $options): ?>
        <div class="champ <?= $champ === 'titre' || ($champ === 'type_animal' && !($villeDisponible ?? false)) ? 'large' : '' ?>">
            <label for="<?= e($champ) ?>"><?= e($options[0]) ?></label>
            <input id="<?= e($champ) ?>" name="<?= e($champ) ?>" type="<?= e($options[1]) ?>" placeholder="<?= e($options[3]) ?>" value="<?= e($donnees[$champ]) ?>" required
                <?= $options[2] ? 'maxlength="' . (int) $options[2] . '"' : '' ?>
                <?= isset($erreurs[$champ]) ? 'aria-invalid="true" aria-describedby="erreur-' . e($champ) . '"' : '' ?>>
            <?php if (isset($erreurs[$champ])): ?><p class="erreur-champ" id="erreur-<?= e($champ) ?>"><?= e($erreurs[$champ]) ?></p><?php endif; ?>
        </div>
        <?php if ($champ === 'type_animal' && ($villeDisponible ?? false)): ?>
            <div class="champ"><label for="ville">Ville</label><input id="ville" name="ville" placeholder="Ex. Paris 11e" maxlength="100" value="<?= e($donnees['ville'] ?? '') ?>" <?= isset($erreurs['ville']) ? 'aria-invalid="true" aria-describedby="erreur-ville"' : '' ?>><?php if (isset($erreurs['ville'])): ?><p class="erreur-champ" id="erreur-ville"><?= e($erreurs['ville']) ?></p><?php endif; ?></div>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="champ large">
        <label for="description">Description des besoins</label>
        <textarea id="description" name="description" placeholder="Habitudes de l’animal, sorties, repas, matériel à prévoir..." rows="6" maxlength="5000" <?= isset($erreurs['description']) ? 'aria-invalid="true" aria-describedby="erreur-description"' : '' ?>><?= e($donnees['description']) ?></textarea>
        <?php if (isset($erreurs['description'])): ?><p id="erreur-description" class="erreur-champ"><?= e($erreurs['description']) ?></p><?php endif; ?>
        <small class="intro">Facultative, 5 000 caractères maximum.</small>
    </div>
    </div>
    <div class="actions-formulaire"><button type="submit">Publier l’annonce</button><a class="bouton bouton-secondaire" href="?page=annonces">Annuler</a></div>
</form>
<p class="note-formulaire">Après publication, l’annonce est au statut « Ouverte ».</p>
</section>
<?php require __DIR__ . '/footer.php'; ?>
