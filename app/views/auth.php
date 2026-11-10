<?php $titre = 'Miaoumi — Connexion et inscription'; require __DIR__ . '/header.php'; ?>
<div class="auth-grille">
    <?php foreach (['connexion', 'inscription'] as $mode): $inscription = $mode === 'inscription'; $actif = $mode === $page; ?>
        <section class="auth-carte" id="<?= e($mode) ?>">
            <h1><?= $inscription ? 'Inscription' : 'Connexion' ?></h1>
            <p class="intro"><?= $inscription ? 'Un même compte sert pour confier et pour garder.' : 'Retrouvez vos annonces et vos candidatures.' ?></p>
            <?php if ($actif && $erreurs): ?><ul class="message message-erreur" role="alert"><?php foreach ($erreurs as $erreur): ?><li><?= e($erreur) ?></li><?php endforeach; ?></ul><?php endif; ?>
            <form method="post" action="?page=<?= e($mode) ?>">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <?php if ($inscription): ?><div class="champ"><label for="inscription-nom">Nom</label><input id="inscription-nom" name="nom" autocomplete="name" maxlength="100" required value="<?= e($actif ? $nom : '') ?>"></div><?php endif; ?>
                <div class="champ"><label for="<?= e($mode) ?>-email">Email</label><input id="<?= e($mode) ?>-email" type="email" name="email" placeholder="nom@exemple.fr" autocomplete="email" maxlength="150" required value="<?= e($actif ? $email : '') ?>"></div>
                <div class="champ"><label for="<?= e($mode) ?>-password">Mot de passe</label><input id="<?= e($mode) ?>-password" type="password" name="password" autocomplete="<?= $inscription ? 'new-password' : 'current-password' ?>" <?= $inscription ? 'minlength="8"' : '' ?> required></div>
                <?php if ($inscription): ?><div class="champ"><label for="inscription-confirmation">Confirmation du mot de passe</label><input id="inscription-confirmation" type="password" name="confirmation" autocomplete="new-password" required></div><?php endif; ?>
                <button class="bouton-large" type="submit"><?= $inscription ? 'Créer mon compte' : 'Se connecter' ?></button>
            </form>
            <p class="auth-renvoi"><?= $inscription ? 'Déjà inscrit ?' : 'Pas encore de compte ?' ?> <a href="#<?= $inscription ? 'connexion' : 'inscription' ?>"><?= $inscription ? 'Connexion' : 'Inscription' ?></a></p>
        </section>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/footer.php'; ?>
