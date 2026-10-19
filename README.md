<p align="center">
  <img src="public/assets/miaoumi-logo.png" alt="Logo MIAOUMI : un chien et un chat en noir et blanc" width="320">
</p>

<h1 align="center">Miaoumi</h1>

<p align="center">Des animaux bien entourés, des propriétaires rassurés.</p>

<p align="center">
  <a href="#démarrer-en-local">Démarrer</a> ·
  <a href="docs/DEMARRAGE-WINDOWS.md">Guide XAMPP</a> ·
  <a href="CHANGELOG.md">Modifications</a> ·
  <a href="docs/GIT.md">Guide Git</a>
</p>

Site de mise en relation entre propriétaires d’animaux et gardiens, développé en PHP et MySQL.

## État du projet

Le site affiche les annonces de garde, de la plus récente à la plus ancienne. Il propose une inscription, une connexion et une déconnexion avec session utilisateur. Les mots de passe sont hachés et les formulaires disposent d’un jeton contre les soumissions intersites.

La création d’annonces, les candidatures et les avis ne sont pas encore implémentés dans le site. Pour le démarrage avec XAMPP et le test des comptes, suivre [le guide Windows](docs/DEMARRAGE-WINDOWS.md).

L’inscription et la connexion sur la base MySQL ont été confirmées fonctionnelles par le propriétaire du projet. Les tests automatisés utilisent une base temporaire isolée.

## Démarrer en local

Prérequis : Git, PHP 7.4 ou supérieur avec les extensions `pdo_mysql` et `mbstring`, et un serveur MySQL compatible avec le fichier SQL fourni. Une version de PHP encore maintenue est préférable.

1. Ouvrir le dossier du projet dans VS Code.
2. Dans le terminal, sélectionner la branche principale :

   ```sh
   git switch main
   ```

3. Copier `config/config.example.php` vers `config/config.php`, puis renseigner les identifiants MySQL locaux. Ce fichier est ignoré par Git pour protéger les mots de passe.
4. Importer `Miaoumi (2).sql` dans MySQL, par exemple avec phpMyAdmin. Il crée la base `miaoumi` et ses tables : à utiliser sur une base neuve.
5. Depuis la racine du projet, lancer :

   ```sh
   php -S localhost:8000 -t public
   ```

6. Ouvrir <http://localhost:8000>. Si aucune annonce n’a été ajoutée à la base, le message « Aucune annonce pour le moment » est normal.

Le serveur intégré PHP sert au développement local.

## Organisation

| Fichier ou dossier | Rôle |
| --- | --- |
| `public/index.php` | Point d’entrée et choix de la page |
| `core/Database.php` | Connexion MySQL avec PDO |
| `config/config.example.php` | Modèle de configuration locale |
| `views/annonces.php` | Affichage des annonces |
| `views/auth.php` | Formulaires d’inscription et de connexion |
| `views/navigation.php` | Navigation et état de connexion |
| `public/assets/` | Logo du projet |
| `tests/auth_smoke.py` | Test HTTP de l’authentification sur base temporaire |
| `Miaoumi (2).sql` | Structure de la base de données |
| `CHANGELOG.md` | Résumé lisible des modifications |
| `docs/GIT.md` | Guide Git pour débuter |
| `scripts/` | Outils pour générer les journaux |
| `AGENTS.md` | Consignes pour les assistants qui modifient le projet |

## Suivre les modifications

Le [journal des modifications](CHANGELOG.md) explique les changements importants. Le [guide Git](docs/GIT.md) explique comment enregistrer et envoyer son travail.

Le [rapport de vérification](docs/VERIFICATION.md) présente les résultats des contrôles et les critères restant à valider.

Pour activer les journaux automatiques sur Windows, exécuter une fois à la racine :

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/install-hooks.ps1
```

Après chaque **commit**, un historique local est généré dans `logs/git-history.md`. Il indique la date, l’auteur, le message et les fichiers concernés. Il ne surveille pas chaque sauvegarde de fichier. Pour le régénérer à la demande :

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/generate-history.ps1
```

Le dossier `logs/` est ignoré par Git : chacun peut générer son historique sans créer de modifications à enregistrer. Le README et le CHANGELOG sont maintenus avec les changements du projet ; Git ne peut pas en rédiger automatiquement les explications.

## Participer

Consulter le [guide de contribution](CONTRIBUTING.md) avant de proposer une modification. Des modèles de signalement de bug et de proposition sont disponibles dans l’onglet **Issues** de GitHub.

## Vérifier le code

Avec Python et PHP disposant de `pdo_sqlite` et `mbstring`, depuis la racine :

```powershell
python tests/auth_smoke.py C:\xampp\php\php.exe
```

Ce test utilise une base SQLite temporaire sans modifier les comptes MySQL. Le [rapport de vérification](docs/VERIFICATION.md) distingue les tests automatisés et les validations manuelles.
