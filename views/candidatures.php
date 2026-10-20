<?php $titre = $reception ? 'Miaoumi — Candidatures reçues' : 'Miaoumi — Mes candidatures'; require __DIR__ . '/header.php'; ?>
<?php if ($reception): ?><p class="intro"><a href="?page=mes-annonces">Mes annonces</a> : <?= e($annonce['titre']) ?></p><?php endif; ?>
<h1><?= $reception ? 'Candidatures reçues' : 'Mes candidatures' ?></h1>
<div class="liste-candidatures">
    <?php if (!$candidatures): ?><div class="etat-vide"><img src="assets/patte.svg" alt="" width="35" height="35"><h2><?= $reception ? 'Aucune candidature reçue pour cette annonce.' : 'Vous n’avez pas encore de candidature.' ?></h2><a class="bouton bouton-secondaire" href="?page=annonces">Voir les annonces</a></div><?php endif; ?>
    <?php foreach ($candidatures as $candidature): ?>
        <article class="candidature-ligne carte">
            <div class="<?= $reception ? 'avatar' : 'candidature-visuel' ?>">
                <?php if ($reception): ?><?= e(mb_strtoupper(mb_substr($candidature['nom'],0,1))) ?><?php else: ?><img src="assets/patte.svg" alt="" width="25" height="25"><?php endif; ?>
            </div>
            <div class="candidature-texte">
                <h2><?= e($reception ? $candidature['nom'] : $candidature['titre']) ?></h2>
                <?php if (!$reception): ?><p class="meta"><?= e($candidature['type_animal']) ?> · <?= e($candidature['date_debut']) ?> → <?= e($candidature['date_fin']) ?></p><?php endif; ?>
                <p class="meta">Candidature envoyée le <?= e($candidature['date_candidature']) ?></p>
                <?php if ($reception && !empty($candidature['message'])): ?><p class="message-candidature"><?= nl2br(e($candidature['message'])) ?></p><?php endif; ?>
            </div>
            <span class="badge statut-<?= e(in_array($candidature['statut'],['en_attente','acceptee','refusee'],true) ? $candidature['statut'] : 'autre') ?>"><?= e(['en_attente'=>'En attente','acceptee'=>'Acceptée','refusee'=>'Refusée'][$candidature['statut']] ?? $candidature['statut']) ?></span>
            <a class="bouton bouton-secondaire" href="?page=avis&amp;annonce=<?= (int) $candidature['id_annonce'] ?>">Voir les avis</a>
        </article>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/footer.php'; ?>
