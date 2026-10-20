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

Les annonces donnent accès à leurs avis : consultation publique et dépôt réservé aux participants après la date de fin de la garde. La création d’annonces et la gestion des candidatures ne sont pas encore implémentées dans le site. Pour le démarrage avec XAMPP et le test des comptes, suivre [le guide Windows](docs/DEMARRAGE-WINDOWS.md).

L’inscription et la connexion sur la base MySQL ont été confirmées fonctionnelles par le propriétaire du projet.

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
| `public/index.php` | Point d’entrée : associe une page à un contrôleur |
| `controllers/` | Traitements des annonces et de l’authentification |
| `models/` | Accès aux données des utilisateurs et annonces |
| `core/bootstrap.php` | Chargement des classes, session et protection des formulaires |
| `core/Database.php` | Connexion MySQL avec PDO |
| `config/config.example.php` | Modèle de configuration locale |
| `views/annonces.php` | Affichage des annonces |
| `views/auth.php` | Formulaires d’inscription et de connexion |
| `views/navigation.php` | Navigation et état de connexion |
| `models/Avis.php` | Lecture des avis, participants autorisés et enregistrement |
| `controllers/AvisController.php` | Validation des formulaires d’avis et choix de la vue |
| `views/avis.php` | Liste des avis et formulaire de dépôt |
| `public/assets/` | Logo du projet |
| `Miaoumi (2).sql` | Structure de la base de données |
| `CHANGELOG.md` | Résumé lisible des modifications |
| `docs/GIT.md` | Guide Git pour débuter |
| `scripts/` | Outils pour générer les journaux |
| `AGENTS.md` | Consignes pour les assistants qui modifient le projet |

## Comprendre le MVC

Le projet utilise un MVC simple, sans framework. Les adresses des pages restent les mêmes (`?page=annonces`, `?page=inscription`, etc.).

1. `public/index.php` choisit le contrôleur selon la page demandée.
2. Le **contrôleur** traite la demande, valide les champs et appelle un modèle si nécessaire.
3. Le **modèle** exécute les requêtes SQL avec la connexion fournie par `Database`.
4. Le contrôleur transmet les données à une **vue**, qui produit le HTML.

Exemple : pour afficher les annonces, `index.php` appelle `AnnonceController::index()`, qui récupère les données avec `Annonce::toutes()` puis charge `views/annonces.php`.

Pour les comptes, `AuthController` gère l’inscription, la connexion et la déconnexion ; `Utilisateur` enregistre les comptes et recherche un utilisateur par e-mail. Les formulaires restent dans `views/auth.php`. Les vues ne contiennent aucune requête SQL.

## Les avis

Depuis une annonce, cliquer sur **Voir les avis / laisser un avis** (`?page=avis&annonce=ID`). Les avis affichent l’auteur, la personne évaluée, la note sur 5, le commentaire et la date d’enregistrement.

Pour publier, il faut être connecté et participer à la garde : le propriétaire peut évaluer un gardien dont la candidature porte le statut `acceptee`, et ce gardien peut évaluer le propriétaire. La date de fin doit être antérieure au jour courant en heure de Paris. Une personne ne peut pas se noter elle-même ni évaluer deux fois la même personne pour une annonce. Le commentaire est facultatif, limité à 2 000 caractères.

Cette première version utilise les tables existantes, sans réimportation SQL. La gestion des candidatures n’étant pas encore développée, une candidature acceptée doit déjà exister dans la base pour tester un dépôt. Le contrôle repose actuellement sur la date de fin et la candidature acceptée ; aucune confirmation de réalisation de la garde n’est encore prévue. Les avis publiés ne sont pas modifiables ou supprimables depuis le site à ce stade.

Pour essayer dans le navigateur : utiliser une annonce passée avec un propriétaire et un gardien distincts, et une candidature `acceptee`. Se connecter successivement avec les deux comptes pour publier un avis, vérifier que le formulaire ne permet plus de noter la même personne, puis vérifier qu’un compte extérieur ne peut pas déposer d’avis.

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

Vérifier la syntaxe des fichiers PHP modifiés, par exemple :

```powershell
& C:\xampp\php\php.exe -l public/index.php
```

Tester ensuite l’inscription, la connexion et la déconnexion dans le navigateur en suivant le [guide Windows](docs/DEMARRAGE-WINDOWS.md). Python n’est pas nécessaire.
