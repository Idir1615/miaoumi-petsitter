
# Contribuer à Miaoumi

## Équipe et attribution

L’équipe est composée d’**Idir**, **Ama** et **Maria**. Les documents utilisent uniquement les prénoms, sans nom de famille.

Pour chaque changement significatif, ajouter dans le CHANGELOG un titre sous la forme `JJ/MM/AAAA à HH:MM — Description — Prénom`, en heure de Paris. Si plusieurs personnes ont participé, indiquer leurs prénoms. Ne pas déduire les contributions à partir de la simple présence d’une personne dans l’équipe ; si l’auteur n’est pas identifié, écrire « Auteur à confirmer ».

## Préparer son travail

1. Suivre le [README](README.md) pour démarrer le projet.
2. Vérifier la branche et les changements locaux avec `git status`.
3. Partir d’un `main` à jour, puis créer une branche pour la tâche, par exemple `git switch -c feature/creation-annonces`.
4. Convenir avec l’équipe des fichiers à modifier pour éviter de travailler en même temps sur les mêmes parties.

## Avant de proposer une modification

- Garder une modification centrée sur un objectif.
- Vérifier le parcours concerné dans le navigateur et la syntaxe des fichiers PHP modifiés avec `php -l chemin/du/fichier.php`.
- Si l’authentification est touchée, vérifier manuellement l’inscription, la connexion, le refus d’un mauvais mot de passe et la déconnexion en suivant le guide Windows.
- Expliquer le changement dans `CHANGELOG.md` et adapter le README si nécessaire.
- Vérifier `git diff` avant d’enregistrer : aucun mot de passe, compte réel ou fichier `config/config.php` ne doit être envoyé.

Envoyer sa branche et ouvrir une pull request vers `main`, en indiquant le résultat attendu et les vérifications réellement effectuées. Le [guide Git](docs/GIT.md) explique les commandes de base.

## Signaler un problème

Utiliser le modèle de bug dans GitHub et décrire les étapes pour le reproduire. Retirer les mots de passe et données personnelles des captures. Pour une idée, utiliser le modèle de proposition.
