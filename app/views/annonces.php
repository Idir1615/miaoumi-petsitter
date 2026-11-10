<?php
$mesAnnonces = $mesAnnonces ?? false;
$recherche = $recherche ?? '';
$animal = $animal ?? '';
$ville = $ville ?? '';
$villeDisponible = $villeDisponible ?? false;
$total = $total ?? count($annonces);
$numero = $numero ?? 1;
$nombrePages = $nombrePages ?? 1;
$statut = $statut ?? '';
$accueil = $accueil ?? null;
$pageListe = $mesAnnonces ? 'mes-annonces' : 'annonces';
$titre = $mesAnnonces ? 'Miaoumi — Mes annonces' : 'Miaoumi — Annonces de garde';
$libellesStatut = ['ouverte' => 'Ouverte', 'pourvue' => 'Pourvue', 'terminee' => 'Terminée'];
require __DIR__ . '/header.php';
?>
<?php if ($accueil): ?>
    <section class="accueil">
        <div class="accueil-texte">
            <p class="surtitre">Garde d’animaux entre particuliers</p>
            <h1>Des animaux bien entourés, des propriétaires rassurés.</h1>
            <p>Confiez votre compagnon à un gardien près de chez vous, ou proposez vos services pendant les vacances de vos voisins. Un seul compte suffit pour les deux.</p>
            <div class="actions-formulaire">
                <a class="bouton" href="?page=creer-annonce">Déposer une annonce</a>
                <a class="bouton bouton-secondaire" href="#liste-annonces">Voir les gardes</a>
            </div>
        </div>
        <ul class="chiffres" aria-label="Miaoumi en chiffres">
            <li><strong><?= (int) $accueil['annonces']['ouvertes'] ?></strong> gardes ouvertes</li>
            <li><strong><?= (int) $accueil['annonces']['terminees'] ?></strong> gardes réalisées</li>
            <li><strong><?= (int) $accueil['annonces']['villes'] ?></strong> villes</li>
            <li><strong><?= $accueil['avis']['moyenne'] !== null ? e(number_format((float) $accueil['avis']['moyenne'], 1, ',', '')) : '—' ?></strong> note moyenne (<?= (int) $accueil['avis']['total'] ?> avis)</li>
        </ul>
    </section>
    <ol class="etapes">
        <li><span>1</span><div><strong>Publiez</strong><p>Décrivez votre animal, les dates et ses habitudes.</p></div></li>
        <li><span>2</span><div><strong>Choisissez</strong><p>Recevez des candidatures et acceptez le gardien idéal.</p></div></li>
        <li><span>3</span><div><strong>Notez</strong><p>Après la garde, laissez-vous un avis l’un à l’autre.</p></div></li>
    </ol>
<?php endif; ?>

<div class="titre-page" id="liste-annonces">
    <h1><?= $mesAnnonces ? 'Mes annonces' : ($accueil ? 'Gardes près de chez vous' : 'Annonces de garde') ?></h1>
    <?php if ($mesAnnonces): ?><a class="bouton" href="?page=creer-annonce">Publier une annonce</a><?php endif; ?>
</div>
<?php if ($mesAnnonces): ?>
    <div class="statistiques"><div class="carte"><p class="meta">Annonces publiées</p><strong><?= (int) $statistiques['total'] ?></strong></div><div class="carte"><p class="meta">Gardes terminées</p><strong><?= (int) $statistiques['terminees'] ?></strong></div></div>
    <nav class="onglets-statut" aria-label="Statut des annonces"><?php foreach ([''=>'Toutes', 'ouverte'=>'Ouverte', 'pourvue'=>'Pourvue', 'terminee'=>'Terminée'] as $cle=>$libelle): ?><a <?= $statut === $cle ? 'aria-current="page"' : '' ?> href="?<?= e(http_build_query(['page'=>'mes-annonces','statut'=>$cle])) ?>"><?= e($libelle) ?></a><?php endforeach; ?></nav>
<?php endif; ?>
<div class="annonces-layout <?= $mesAnnonces ? 'mes-annonces-layout' : '' ?>">
    <aside class="filtres carte">
        <h2>Filtres</h2>
        <form method="get" action="">
            <input type="hidden" name="page" value="<?= e($pageListe) ?>">
            <?php if ($villeDisponible): ?><div class="champ"><label for="ville">Ville</label><input id="ville" name="ville" placeholder="Ex. Paris" maxlength="100" value="<?= e($ville) ?>"></div><?php endif; ?>
            <div class="champ"><label for="mots-cles">Rechercher</label><input id="mots-cles" name="q" placeholder="Ex. Garde de Milo" value="<?= e($recherche) ?>" maxlength="150"></div>
            <div class="champ"><label for="animal">Type d’animal</label><select id="animal" name="animal">
                <option value="">Tous</option>
                <?php foreach (['Chien', 'Chat', 'Lapin', 'Oiseau', 'Poisson', 'Autre'] as $type): ?><option value="<?= e($type) ?>" <?= $animal === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?>
            </select></div>
            <button type="submit">Appliquer</button>
            <a class="reinitialiser" href="?page=<?= e($pageListe) ?>">Réinitialiser</a>
        </form>
    </aside>
    <section class="resultats" aria-label="Annonces">
        <p class="compteur"><?= $total ?> annonce<?= $total > 1 ? 's' : '' ?> <?= $mesAnnonces ? 'publiée' . ($total > 1 ? 's' : '') : 'ouverte' . ($total > 1 ? 's' : '') ?></p>
        <?php if (empty($annonces)): ?>
            <div class="etat-vide">
                <img src="assets/patte.svg" alt="" width="35" height="35">
                <h2><?= $mesAnnonces && $recherche === '' && $animal === '' ? 'Vous n’avez pas encore publié d’annonce' : 'Aucune annonce pour le moment.' ?></h2>
                <p><?= $recherche !== '' || $animal !== '' ? 'Aucune annonce ne correspond à votre recherche.' : 'Les prochaines gardes apparaîtront ici.' ?></p>
                <a class="bouton bouton-secondaire" href="<?= $mesAnnonces && $recherche === '' && $animal === '' ? '?page=creer-annonce' : '?page=' . e($pageListe) ?>"><?= $mesAnnonces && $recherche === '' && $animal === '' ? 'Publier ma première annonce' : 'Réinitialiser les filtres' ?></a>
            </div>
        <?php else: ?>
            <div class="grille">
                <?php foreach ($annonces as $a): $image = photoAnimal($a); ?>
                    <article class="annonce-carte">
                        <a class="annonce-visuel" href="?page=annonce&amp;id=<?= (int) $a['id'] ?>" tabindex="-1" aria-hidden="true">
                            <img class="<?= $image['photo'] ? 'photo' : 'illustration' ?>" src="<?= e($image['src']) ?>" alt="" loading="lazy">
                            <span class="badge badge-statut statut-<?= e($a['statut']) ?>"><?= e($libellesStatut[$a['statut']] ?? $a['statut']) ?></span>
                        </a>
                        <div class="annonce-contenu">
                            <h2><a href="?page=annonce&amp;id=<?= (int) $a['id'] ?>"><?= e($a['titre']) ?></a></h2>
                            <p class="meta"><?= e($a['type_animal']) ?><?= !empty($a['ville']) ? ' · ' . e($a['ville']) : '' ?></p>
                            <p class="meta">Du <time datetime="<?= e($a['date_debut']) ?>"><?= e(date('d/m', strtotime($a['date_debut']))) ?></time> au <time datetime="<?= e($a['date_fin']) ?>"><?= e(date('d/m', strtotime($a['date_fin']))) ?></time></p>
                            <?php if (!empty($a['description'])): ?><p class="description-annonce"><?= e($a['description']) ?></p><?php endif; ?>
                            <?php if (!$mesAnnonces && isset($a['nom_proprietaire'])): ?>
                                <p class="proprietaire">
                                    <span class="avatar avatar-petit" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($a['nom_proprietaire'], 0, 1))) ?></span>
                                    <a href="?page=profil&amp;id=<?= (int) $a['id_proprietaire'] ?>"><?= e($a['nom_proprietaire']) ?></a>
                                    <?php if (($a['note_proprietaire'] ?? null) !== null): ?><span class="note-courte">★ <?= e(number_format((float) $a['note_proprietaire'], 1, ',', '')) ?></span><?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <a class="lien-avis" href="?page=avis&amp;annonce=<?= (int) $a['id'] ?>">Voir les avis / laisser un avis <span aria-hidden="true">→</span></a>
                            <?php if ($mesAnnonces): ?><a class="lien-avis" href="?page=candidatures-recues&amp;annonce=<?= (int) $a['id'] ?>">Candidatures reçues →</a><?php endif; ?>
                            <?php if ($mesAnnonces && !empty($a['photo'])): ?>
                                <form class="form-photo" method="post" action="?page=supprimer-photo-annonce">
                                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                    <input type="hidden" name="annonce" value="<?= (int) $a['id'] ?>">
                                    <button class="bouton-secondaire" type="submit">Supprimer la photo</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($mesAnnonces && ($a['statut'] ?? '') !== 'terminee'): ?>
                                <details class="cloture">
                                    <summary class="bouton bouton-secondaire">Terminer la garde</summary>
                                    <form class="form-cloture" method="post" action="?page=cloturer-annonce">
                                        <p>Clôturer « <?= e($a['titre']) ?> » ? Cette action est définitive.</p>
                                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                        <input type="hidden" name="annonce" value="<?= (int) $a['id'] ?>">
                                        <button type="submit">Confirmer la clôture</button>
                                    </form>
                                </details>
                            <?php endif; ?>
                            <?php
                            $connecte = isset($_SESSION['utilisateur']['id']);
                            $annonceAccessible = !$mesAnnonces
                                && ($a['statut'] ?? '') === 'ouverte'
                                && $a['date_fin'] >= $aujourdHui;
                            $estSaPropreAnnonce = $connecte
                                && (int) $_SESSION['utilisateur']['id'] === (int) $a['id_proprietaire'];
                            ?>
                            <?php if ($annonceAccessible && !$estSaPropreAnnonce): ?>
                                <a class="bouton bouton-postuler" href="?page=postuler&amp;annonce=<?= (int) $a['id'] ?>"><?= $connecte ? 'Postuler' : 'Se connecter pour postuler' ?></a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($nombrePages > 1): ?><nav class="pagination" aria-label="Pagination des annonces">
                <?php for ($i = 1; $i <= $nombrePages; $i++): ?><a <?= $i === $numero ? 'aria-current="page"' : '' ?> href="?<?= e(http_build_query(['page'=>$pageListe,'q'=>$recherche,'animal'=>$animal,'ville'=>$ville,'statut'=>$statut,'numero'=>$i])) ?>#liste-annonces"><?= $i ?></a><?php endfor; ?>
            </nav><?php endif; ?>
        <?php endif; ?>
    </section>
</div>

<?php if ($accueil && $accueil['derniersAvis']): ?>
    <section class="derniers-avis">
        <h2>Ils ont fait confiance à Miaoumi</h2>
        <div class="grille">
            <?php foreach ($accueil['derniersAvis'] as $evaluation): ?>
                <figure class="carte temoignage">
                    <span class="etoiles" aria-label="<?= (int) $evaluation['note'] ?> sur 5"><?= str_repeat('★', (int) $evaluation['note']) . str_repeat('☆', 5 - (int) $evaluation['note']) ?></span>
                    <blockquote><?= e($evaluation['commentaire']) ?></blockquote>
                    <figcaption><a href="?page=profil&amp;id=<?= (int) $evaluation['id_auteur'] ?>"><?= e($evaluation['auteur']) ?></a> à propos de <a href="?page=profil&amp;id=<?= (int) $evaluation['id_cible'] ?>"><?= e($evaluation['cible']) ?></a> · <a href="?page=avis&amp;annonce=<?= (int) $evaluation['id_annonce'] ?>"><?= e($evaluation['titre']) ?></a></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
