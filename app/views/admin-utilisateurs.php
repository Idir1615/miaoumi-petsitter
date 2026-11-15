<?php $titre = 'Miaoumi — Modération'; require __DIR__ . '/header.php'; ?>
<h1>Modération des comptes</h1>
<p class="intro">Un compte suspendu ne peut plus se connecter ; s’il est connecté, il est déconnecté dès sa page suivante. Ses annonces et avis restent en place. Les administrateurs ne peuvent pas être suspendus.</p>
<div class="carte tableau-defilant">
    <table class="tableau-moderation">
        <thead>
            <tr><th scope="col">Nom</th><th scope="col">E-mail</th><th scope="col">Rôle</th><th scope="col">Annonces</th><th scope="col">État</th><th scope="col"><span class="visuellement-cache">Action</span></th></tr>
        </thead>
        <tbody>
        <?php foreach ($utilisateurs as $u): $actif = (bool) (int) $u['actif']; $admin = $u['role'] === 'admin'; ?>
            <tr>
                <td><a href="?page=profil&amp;id=<?= (int) $u['id'] ?>"><?= e($u['nom']) ?></a></td>
                <td><?= e($u['email']) ?></td>
                <td><?= $admin ? 'Administrateur' : 'Utilisateur' ?></td>
                <td><?= (int) $u['nb_annonces'] ?></td>
                <td><span class="badge <?= $actif ? 'statut-ouverte' : 'statut-refusee' ?>"><?= $actif ? 'Actif' : 'Suspendu' ?></span></td>
                <td>
                    <?php if (!$admin): ?>
                        <form method="post" action="?page=suspendre-utilisateur">
                            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button type="submit" class="<?= $actif ? 'bouton-danger' : 'bouton-secondaire' ?>"><?= $actif ? 'Suspendre' : 'Réactiver' ?></button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/footer.php'; ?>
