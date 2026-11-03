
# Contribuer à Miaoumi

## Équipe et attribution

L’équipe est composée d’**Idir**, **Ama** et **Maria**. Les documents utilisent uniquement les prénoms, sans nom de famille.

Pour chaque changement significatif, ajouter dans le CHANGELOG un titre sous la forme `JJ/MM/AAAA à HH:MM — Description — Prénom`, en heure de Paris. Si plusieurs personnes ont participé, indiquer leurs prénoms. Ne pas déduire les contributions à partir de la simple présence d’une personne dans l’équipe ; si l’auteur n’est pas identifié, écrire « Auteur à confirmer ».

## Préparer son travail

1. Suivre le [README](README.md) pour démarrer le projet.
2. Vérifier la branche et les changements locaux avec `git status`.
3. Partir de `develop` à jour, puis créer une branche nommée `feature/prénom-fonctionnalité`, en minuscules, sans accent, avec des tirets : `git switch -c feature/ama-candidatures`.
4. Convenir avec l’équipe des fichiers à modifier pour éviter de travailler en même temps sur les mêmes parties.

## Avant de proposer une modification

- Garder une modification centrée sur un objectif.
- Vérifier le parcours concerné dans le navigateur et la syntaxe des fichiers PHP modifiés avec `php -l chemin/du/fichier.php`.
- Si l’authentification est touchée, vérifier manuellement l’inscription, la connexion, le refus d’un mauvais mot de passe et la déconnexion en suivant le guide Windows.
- Expliquer le changement dans `CHANGELOG.md` et adapter le README si nécessaire.
- Vérifier `git diff` avant d’enregistrer : aucun mot de passe, compte réel ou fichier `config/config.php` ne doit être envoyé.

Envoyer sa branche et ouvrir une pull request vers `develop`, en indiquant le résultat attendu et les vérifications réellement effectuées. Le [guide Git](docs/GIT.md) explique les commandes de base.

## Règles de branches

Circuit : `feature/prénom-fonctionnalité` → `develop` → `main`.

- `main` est réservée à **Idir** : elle ne reçoit que `develop`, fusionnée par Idir.
- `develop` rassemble le travail de l’équipe ; on n’y pousse pas directement, tout arrive par pull request validée par Idir.
- Chacun crée ses branches `feature/idir-…`, `feature/ama-…` ou `feature/maria-…` depuis `develop` à jour.
- Après une fusion, repartir de `develop` à jour pour la tâche suivante.
- Le hook `pre-push` (installé avec `scripts/install-hooks.ps1`) refuse localement un push sur `main` ou `develop` par une autre personne qu’Idir, et les noms de branches non conformes. Un contrôle GitHub vérifie aussi chaque pull request.

## Signaler un problème

Utiliser le modèle de bug dans GitHub et décrire les étapes pour le reproduire. Retirer les mots de passe et données personnelles des captures. Pour une idée, utiliser le modèle de proposition.
