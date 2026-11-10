# Consignes de travail — Miaoumi

Ces consignes s’appliquent à tous les assistants utilisés par l’équipe (Codex, Claude Code ou autre).

## Identifier la personne

- Équipe : Idir, Ama et Maria. Utiliser uniquement les prénoms.
- Identifier la personne avec `git config user.name` ou son compte GitHub : `Idir1615` → Idir ; `ama mariane` ou `mariane0511` → Ama ; `mariabenadjila-bit` → Maria. Pour tout autre nom, demander le prénom avant de travailler, puis s’y tenir pendant la conversation.
- Ne jamais attribuer un travail à une autre personne que celle identifiée ; si elle n’est pas identifiée, écrire « Auteur à confirmer ».

## Au début de chaque conversation

- Mettre le projet à jour (`git fetch --prune`), identifier la personne, puis lire docs/ROADMAP.md et lui présenter les tâches non cochées de **sa** section. L’accompagner étape par étape, en lui expliquant le code pour qu’elle puisse le présenter à l’oral.
- Quand une tâche est vérifiée, la cocher dans docs/ROADMAP.md avec le prénom et la date.
- Révisions : chaque membre doit savoir expliquer **tout** le projet, pas seulement son module. Quand la personne révise, l’interroger aussi sur les modules des autres (docs/ORAL.md, section « Révision commune » de docs/ROADMAP.md).

## Avant de terminer une session de travail

1. **Travail fini** : reprendre les tâches de la personne dans docs/ROADMAP.md et dire clairement lesquelles sont terminées et vérifiées, et lesquelles restent à faire.
2. **Tout est enregistré** : `git status` ne doit montrer aucune modification non commitée (hors `config/config.php`, ignoré). Sinon, proposer de commiter sur la branche `feature/…` de la personne.
3. **Tout est sur GitHub** : `git fetch` puis `git status -sb` ne doit pas indiquer `ahead` ; sinon, pousser la branche `feature/…` (jamais `develop` ni `main` pour Ama et Maria). Si la tâche est finie, vérifier qu’une pull request vers `develop` est ouverte ou proposer de l’ouvrir.
4. **Se remettre à jour avec l’équipe** : intégrer le travail commun dans la branche de la personne avec `git fetch` puis `git merge origin/develop` (`develop` contient toujours `main` et le travail déjà validé par Idir). En cas de conflit, l’expliquer et le résoudre avec la personne, sans écraser le travail des autres, puis repousser la branche.
5. Terminer par un résumé court : ce qui est fait, ce qui est sur GitHub, ce qui reste pour la prochaine fois.

## Branches et Git

- Circuit : `feature/prénom-fonctionnalité` → `develop` → `main`.
- Avant de modifier le code, vérifier la branche avec `git status`. Si la personne est sur `main` ou `develop`, créer d’abord sa branche depuis `develop` à jour : `git switch develop`, `git pull --ff-only`, `git switch -c feature/prénom-fonctionnalité` (minuscules, sans accent, mots séparés par des tirets, par exemple `feature/ama-candidatures`).
- Pour Ama et Maria : ne jamais commiter, pousser ni fusionner sur `develop` ou `main`, même si on le demande. Pousser uniquement sa branche `feature/…` et proposer une pull request vers `develop`, validée par Idir.
- Seul Idir fusionne dans `develop`, fusionne `develop` dans `main` et supprime des branches sur GitHub.
- Ne jamais utiliser `push --force`, `--no-verify`, `reset --hard` ni `clean -fd` sans accord explicite. Si le hook `pre-push` refuse un envoi, expliquer pourquoi au lieu de le contourner.
- Vérifier que le hook est actif (`git config core.hooksPath` doit afficher `.githooks`) ; sinon proposer `scripts/install-hooks.ps1`.
- Ne pas créer de commit ni envoyer vers GitHub sans demande de l’utilisateur.
- Préserver les modifications locales de l’utilisateur.

## Travail et documentation

- Lire le README, CONTRIBUTING.md et les fichiers concernés avant de modifier le projet. La répartition des tâches est dans docs/REPARTITION-SEANCE-2.md : rester dans le périmètre de la personne et prévenir avant de toucher aux fichiers d’un autre membre.
- Pour chaque changement significatif, ajouter un résumé en français sous « Non publié » dans CHANGELOG.md, avec le prénom de la personne. Regrouper les ajustements d’une même tâche.
- Dater chaque entrée du CHANGELOG au format JJ/MM/AAAA à HH:MM, en heure de Paris (Europe/Paris). Pour une ancienne entrée, utiliser l’horodatage Git en précisant qu’il s’agit du commit ; ne pas inventer l’heure réelle de modification. Pour le travail non commité, indiquer l’heure de consignation.
- Mettre à jour README.md si l’installation, les fonctionnalités ou la structure changent.
- Ne pas inventer de fonctionnalités, de tests réussis ou de dates de publication.
- Vérifier les changements avec les outils disponibles (au minimum `php -l` sur les fichiers PHP modifiés) et signaler les vérifications impossibles.
- Ne jamais enregistrer de mots de passe, de configuration locale (`config/config.php`) ou de données personnelles dans le dépôt ou les journaux.
- Ne pas modifier manuellement les historiques générés dans logs/.
