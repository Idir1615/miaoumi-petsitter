# Git, pas à pas

Git garde des points de sauvegarde du code appelés **commits**. GitHub héberge les commits envoyés avec **push**. Sauvegarder un fichier dans VS Code ne crée pas de commit.

## Les branches

- `main` : branche principale qui rassemble le projet et sa documentation à la demande du propriétaire.
- `develop` : branche de développement existante.
- `feature` : autre branche existante ; son contenu peut différer.

Une branche est une version de travail du projet. Vérifier la branche en bas à gauche de VS Code ou avec `git branch --show-current`.

## Avant de travailler

```sh
git status
git switch main
git pull --ff-only
```

Si Git indique des modifications locales, les examiner et les enregistrer avant de changer de branche. Si la mise à jour échoue, demander de l’aide en donnant le message exact ; ne pas forcer.

## Enregistrer une modification

1. Modifier les fichiers et vérifier le résultat.
2. Ajouter une explication dans `CHANGELOG.md` et adapter le README si nécessaire.
3. Examiner les changements :

   ```sh
   git status
   git diff
   ```

4. Sélectionner les fichiers à enregistrer. Exemple pour une modification de documentation :

   ```sh
   git add README.md CHANGELOG.md
   git commit -m "Documente le démarrage du projet"
   ```

   Adapter la liste aux fichiers réellement modifiés. Le commit enregistre uniquement les fichiers sélectionnés.

5. Quand le travail est prêt à être envoyé sur GitHub :

   ```sh
   git push origin main
   ```

Dans VS Code, le panneau **Contrôle de code source** permet aussi d’examiner les différences, de sélectionner les fichiers avec `+`, puis de saisir un message et de créer un commit.

## Les journaux

- `CHANGELOG.md` : explications rédigées avec chaque changement significatif.
- `logs/git-history.md` : historique technique généré depuis les commits, après activation décrite dans le README.
- `git log --oneline -10` : les dix derniers commits dans le terminal.

Le hook est local : chaque personne qui clone le projet doit l’installer. L’installation refuse de remplacer une configuration de hooks existante. Pour désactiver le hook installé ici : `git config --local --unset core.hooksPath`.

## Précautions simples

Ne jamais ajouter `config/config.php` à Git. Éviter les commandes `reset --hard`, `clean -fd` et `push --force` sans comprendre leur effet : elles peuvent supprimer du travail ou remplacer l’historique partagé.
