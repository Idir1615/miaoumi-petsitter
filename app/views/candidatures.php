<?php $titre = 'Miaoumi — Candidatures'; require __DIR__ . '/header.php'; ?>
<h1>Mes candidatures</h1>

<?php if ($erreurs): ?>
    <div class="message message-erreur" role="alert">
        <?php foreach ($erreurs as $erreur): ?><p><?= e($erreur) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

    <?php
    $mesCandidatures = array_filter($candidatures, static function (array $candidature) use ($utilisateur): bool {
        return (int) $candidature['id_gardien'] === $utilisateur;
    });
    $candidaturesRecues = array_filter($candidatures, static function (array $candidature) use ($utilisateur): bool {
        return (int) $candidature['id_proprietaire'] === $utilisateur;
    });
    $libellesStatuts = ['en_attente' => 'En attente', 'acceptee' => 'Acceptée', 'refusee' => 'Refusée'];
    ?>

<section>
    <h2>Mes demandes envoyées</h2>
    <?php if (!$mesCandidatures): ?>
        <p class="carte">Tu n’as pas encore postulé à une annonce.</p>
    <?php else: ?>
        <div class="grille">
            <?php foreach ($mesCandidatures as $candidature): ?>
                <article class="carte">
                    <h3><?= e($candidature['titre']) ?></h3>
                    <p>Propriétaire : <a href="?page=profil&amp;id=<?= (int) $candidature['id_proprietaire'] ?>"><?= e($candidature['nom_proprietaire']) ?></a></p>
                    <p>Statut : <?= e($libellesStatuts[$candidature['statut']] ?? $candidature['statut']) ?></p>
                    <p>Envoyée le <?= e($candidature['date_candidature']) ?></p>
                    <?php if (($candidature['message'] ?? '') !== ''): ?>
                        <p><?= nl2br(e($candidature['message'])) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <h2>Candidatures reçues pour mes annonces</h2>
    <?php if (!$candidaturesRecues): ?>
        <p class="carte">Aucune candidature reçue pour le moment.</p>
    <?php else: ?>
        <div class="grille">
            <?php foreach ($candidaturesRecues as $candidature): ?>
                <article class="carte">
                    <h3><?= e($candidature['titre']) ?></h3>
                    <p>Candidat·e : <a href="?page=profil&amp;id=<?= (int) $candidature['id_gardien'] ?>"><?= e($candidature['nom_gardien']) ?></a></p>
                    <p>Statut : <?= e($libellesStatuts[$candidature['statut']] ?? $candidature['statut']) ?></p>
                    <p>Reçue le <?= e($candidature['date_candidature']) ?></p>
                    <?php if (($candidature['message'] ?? '') !== ''): ?>
                        <p><?= nl2br(e($candidature['message'])) ?></p>
                    <?php endif; ?>
                    <?php if ($candidature['statut'] === 'en_attente'): ?>
                        <form method="post" action="?page=decision-candidature">
                            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                            <input type="hidden" name="candidature" value="<?= (int) $candidature['id'] ?>">
                            <button type="submit" name="decision" value="acceptee">Accepter</button>
                            <button type="submit" name="decision" value="refusee">Refuser</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/footer.php'; ?>
