# Journal des modifications

Les changements importants sont expliqués ici en français. « Non publié » correspond au travail qui n’a pas encore fait l’objet d’une version publiée.

Les horaires sont exprimés en heure de Paris (Europe/Paris). Pour les anciennes modifications, les dates et heures indiquées proviennent des commits Git : elles correspondent à leur enregistrement, pas nécessairement à l’heure exacte de modification des fichiers. Pour le travail local non commité, l’horodatage correspond à sa consignation dans ce journal.

## Non publié

### 20/10/2026 à 10:43 — Démarrage XAMPP (travail local consigné)

- Configuration d’exemple adaptée à une installation XAMPP locale standard.
- Message précis lorsque config/config.php manque et explication des copies distinctes du projet dans le guide Windows.
- Configuration locale privée toujours exclue de Git.

### 20/10/2026 à 09:39 — Partage des prompts (travail local consigné)

- Ajout de docs/PROMPTS.md avec les demandes de la conversation dans l’ordre et des notes de contexte pour l’équipe.
- Ajout du lien dans le README ; exclusion des instructions internes et des données privées.

### 20/10/2026 à 09:13 — Première version des avis (travail local consigné)

- Ajout du modèle Avis, du contrôleur AvisController et de la vue de consultation et de dépôt.
- Ajout d’un lien vers les avis depuis chaque annonce.
- Dépôt d’une note de 1 à 5 et d’un commentaire facultatif après la date de fin, entre propriétaire et gardien dont la candidature est acceptée.
- Vérification de la session et du jeton CSRF, des destinataires et des doublons ; échappement des commentaires à l’affichage.
- Tests sur MySQL/MariaDB avec tables temporaires, sans modification des données du projet.

### 20/10/2026 à 09:02 — Horodatage du journal

- Ajout des dates et heures aux entrées du journal à partir des informations disponibles.
- Ajout d’une consigne pour dater les prochaines modifications en heure de Paris.

### 20/10/2026 à 09:02 — Organisation MVC (travail local consigné)

- Séparation des requêtes SQL dans les modèles Utilisateur et Annonce.
- Déplacement des traitements dans AuthController et AnnonceController.
- Simplification de public/index.php en routeur et préparation des sessions dans core/bootstrap.php.
- Conservation des vues, des adresses des pages et des protections des formulaires et mots de passe.

### 20/10/2026 à 08:55 — Simplification des vérifications (commit `1bd65e5`)

- Suppression du script de test Python à la demande du propriétaire.
- Remplacement des instructions associées par la vérification syntaxique PHP et les essais manuels dans le navigateur.

### 19/10/2026 à 23:05 — Logo de l’équipe (commit `25ce723`)

- Remplacement de la proposition de logo par le logo noir et blanc fourni par l’équipe, sans retouche, avec tailles d’affichage adaptées au README et au site.

### 19/10/2026 à 23:00 — Présentation du dépôt (commit `82744ca`)

- Ajout du logo Miaoumi au README et à la navigation du site.
- Ajout du guide de contribution et des modèles GitHub de bugs, propositions et pull requests.
- Mise à jour du rapport avec la confirmation utilisateur du fonctionnement de l’authentification sur MySQL.

### 19/10/2026 à 22:46 — Authentification (commit `24d3c22`)

- Ajout de l’inscription, de la connexion et de la déconnexion, avec validation des champs, requêtes préparées, mots de passe hachés et session renouvelée à la connexion.
- Protection des formulaires par jeton CSRF et déconnexion par POST.
- Navigation indiquant le nom de la personne connectée et message explicite si la base est indisponible.
- Ajout du guide XAMPP pour démarrer MySQL, importer la base et vérifier les comptes.

### 19/10/2026 à 22:07 — Compatibilité des hooks (commit `5dcf59b`)

- Conservation des fins de ligne Unix des hooks pour leur exécution après un clonage sous Windows.

### 19/10/2026 à 22:06 — Documentation et outils (commit `ef95702`)

- Rassemblement du projet et de sa documentation sur `main` à la demande du propriétaire.
- Ajout du rapport de vérification des critères fournis et des limites des tests locaux.

- Ajout d’un guide de démarrage, d’une présentation de la structure et d’un guide Git pour débuter.
- Ajout de consignes pour maintenir la documentation lors des prochaines modifications.
- Ajout d’un générateur d’historique Git local et d’un hook pour le lancer après chaque commit sur Windows.

## Base existante

- 18/10/2026 à 16:06 — Squelette PHP avec connexion MySQL via PDO et liste des annonces avec titre, type d’animal et dates de garde (commit `8216f50`).
- 18/10/2026 à 19:33 — Schéma SQL des utilisateurs, annonces, candidatures et avis (commit `9a2cc4a`).

Les dates et auteurs des commits sont disponibles dans l’historique Git.
