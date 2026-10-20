# Journal des modifications

Les changements importants sont expliqués ici en français. « Non publié » correspond au travail qui n’a pas encore fait l’objet d’une version publiée.

Les horaires sont exprimés en heure de Paris (Europe/Paris). Pour les anciennes modifications, les dates et heures indiquées proviennent des commits Git : elles correspondent à leur enregistrement, pas nécessairement à l’heure exacte de modification des fichiers. Pour le travail local non commité, l’horodatage correspond à sa consignation dans ce journal.

## Non publié

### 20/10/2026 à 12:35 — Idir — Mise en cohérence de la documentation

- Correction du README et de la checklist pour refléter les actions de candidature désormais disponibles et les vérifications encore à faire.
- Ajout au fichier des prompts du suivi des demandes récentes ; distinction dans le rapport entre le dépôt confirmé manuellement et les scénarios non vérifiés.

### 20/10/2026 à 12:25 — Idir — Logo de la navigation

- Remplacement de l’icône patte en haut à gauche par le logo Miaoumi déjà affiché dans le pied de page.

### 20/10/2026 à 12:13 — Présentation visuelle du dépôt — Idir

- Ajout en tête du README des aperçus de la maquette fournie, avec accès au PDF complet.
- Retranscription du MCD partagé en diagramme Mermaid avec ses quatre entités et six associations.
- Présentation de l’équipe et distinction entre maquette de référence et fonctionnalités disponibles.

### 20/10/2026 à 12:06 — Ama — Accès au formulaire de candidature

- Affichage d’un lien de candidature pour les annonces ouvertes et accessibles ; les visiteurs non connectés sont invités à se connecter.
- Retour vers le formulaire de l’annonce après connexion et documentation du parcours.

### 20/10/2026 à 11:59 — Ama — Mise à jour de feature depuis main

- Intégration des derniers changements de `main` dans `feature` : maquette, recherche et filtres, pagination, champ ville et migration.
- Conservation du parcours complet de candidature et de tes changements locaux ; `config/config.php` reste local.

### 20/10/2026 à 11:52 — Intégration de la version validée sur main — Idir

- Confirmation par Idir du bon fonctionnement de la version adaptée à la maquette et demande explicite d’intégration sur main.
- Vérification syntaxique PHP et tests sur tables MySQL temporaires réussis avant intégration.

### 20/10/2026 à 11:44 — Adaptation à la maquette PDF (travail local consigné) — Idir

- Palette brun doré/menthe, filtres latéraux, cartes d’annonces, navigation et formulaires adaptés à la maquette fournie.
- Connexion/inscription à deux colonnes, passage des vues auth et avis au gabarit partagé et conservation du logo de l’équipe dans le footer.
- Recherche, filtres par animal et ville, pagination et page Mes annonces avec statuts et compteurs calculés.
- Consultation des candidatures personnelles et reçues avec contrôles d’accès ; actions de candidature toujours à terminer par Ama.
- Champ ville dans le schéma neuf et migration dédiée aux bases existantes, avec compatibilité des anciennes bases.
- Tests PHP/MySQL et HTTP sur données temporaires ; contrôle visuel Chrome sur ordinateur et mobile à 390 px.

### 20/10/2026 à 11:24 — Ama — Candidatures (travail local consigné)

- Ajout des vues et du flux MVC pour postuler, consulter les demandes et accepter ou refuser une candidature.
- Réservation des décisions au propriétaire de l’annonce, contrôle POST avec CSRF, refus des doublons et validation côté modèle.
- Autorisation de plusieurs candidatures acceptées sur une annonce, cohérente avec la consultation des avis existante.
- Réutilisation du gabarit, des styles et des messages flash partagés du projet.
- Mise à jour du README pour documenter le parcours et les règles.
- Consignation des contrôles réalisés et des essais restant à faire dans le rapport de vérification.

### 20/10/2026 à 11:14 — Partie Idir : annonces et socle commun (travail local consigné) — Idir

- Création d’annonces en MVC avec validation dans le modèle, dates vérifiées et propriétaire issu de la session.
- Formulaire accessible aux utilisateurs connectés, erreurs par champ et conservation de la saisie ; redirection avec message flash après publication.
- Header/footer, classes CSS partagées et fonctions ajouterFlash/lireFlash disponibles pour l’équipe.
- Raccordement des annonces, du formulaire et de la page indisponible au gabarit commun ; affichage échappé des données des annonces.
- Ajout de tests PHP sur tables MySQL temporaires pour les validations, autorisations, redirections, affichages et messages ; aucun script Python ni changement du schéma.

### 20/10/2026 à 10:55 — Répartition de la séance 2 — Idir

- Revue de l’état du code par rapport aux huit critères de la nouvelle checklist.
- Répartition du travail à venir : annonces et socle commun pour Idir, candidatures pour Ama, avis et finition de l’authentification pour Maria.
- Ajout des fichiers concernés, dépendances, vérifications attendues et explications des méthodes de modèles dans docs/REPARTITION-SEANCE-2.md.
- Signalement du conflit de nommage entre feature et les branches feature/ par module, sans modifier les branches existantes.

### 20/10/2026 à 10:48 — Attribution des changements — Idir

- Ajout des prénoms de l’équipe : Idir, Ama et Maria.
- Attribution à Idir des changements réalisés avec l’assistant dans cette conversation ; les auteurs de la base initiale restent à confirmer.
- Consigne d’indiquer le prénom de la personne concernée avec la date et l’heure pour les prochaines entrées.

### 20/10/2026 à 10:43 — Démarrage XAMPP (travail local consigné) — Idir

- Configuration d’exemple adaptée à une installation XAMPP locale standard.
- Message précis lorsque config/config.php manque et explication des copies distinctes du projet dans le guide Windows.
- Configuration locale privée toujours exclue de Git.

### 20/10/2026 à 09:39 — Partage des prompts (travail local consigné) — Idir

- Ajout de docs/PROMPTS.md avec les demandes de la conversation dans l’ordre et des notes de contexte pour l’équipe.
- Ajout du lien dans le README ; exclusion des instructions internes et des données privées.

### 20/10/2026 à 09:13 — Première version des avis (travail local consigné) — Idir

- Ajout du modèle Avis, du contrôleur AvisController et de la vue de consultation et de dépôt.
- Ajout d’un lien vers les avis depuis chaque annonce.
- Dépôt d’une note de 1 à 5 et d’un commentaire facultatif après la date de fin, entre propriétaire et gardien dont la candidature est acceptée.
- Vérification de la session et du jeton CSRF, des destinataires et des doublons ; échappement des commentaires à l’affichage.
- Tests sur MySQL/MariaDB avec tables temporaires, sans modification des données du projet.

### 20/10/2026 à 09:02 — Horodatage du journal — Idir

- Ajout des dates et heures aux entrées du journal à partir des informations disponibles.
- Ajout d’une consigne pour dater les prochaines modifications en heure de Paris.

### 20/10/2026 à 09:02 — Organisation MVC (travail local consigné) — Idir

- Séparation des requêtes SQL dans les modèles Utilisateur et Annonce.
- Déplacement des traitements dans AuthController et AnnonceController.
- Simplification de public/index.php en routeur et préparation des sessions dans core/bootstrap.php.
- Conservation des vues, des adresses des pages et des protections des formulaires et mots de passe.

### 20/10/2026 à 08:55 — Simplification des vérifications (commit `1bd65e5`) — Idir

- Suppression du script de test Python à la demande du propriétaire.
- Remplacement des instructions associées par la vérification syntaxique PHP et les essais manuels dans le navigateur.

### 19/10/2026 à 23:05 — Logo de l’équipe (commit `25ce723`) — Idir

- Remplacement de la proposition de logo par le logo noir et blanc fourni par l’équipe, sans retouche, avec tailles d’affichage adaptées au README et au site.

### 19/10/2026 à 23:00 — Présentation du dépôt (commit `82744ca`) — Idir

- Ajout du logo Miaoumi au README et à la navigation du site.
- Ajout du guide de contribution et des modèles GitHub de bugs, propositions et pull requests.
- Mise à jour du rapport avec la confirmation utilisateur du fonctionnement de l’authentification sur MySQL.

### 19/10/2026 à 22:46 — Authentification (commit `24d3c22`) — Idir

- Ajout de l’inscription, de la connexion et de la déconnexion, avec validation des champs, requêtes préparées, mots de passe hachés et session renouvelée à la connexion.
- Protection des formulaires par jeton CSRF et déconnexion par POST.
- Navigation indiquant le nom de la personne connectée et message explicite si la base est indisponible.
- Ajout du guide XAMPP pour démarrer MySQL, importer la base et vérifier les comptes.

### 19/10/2026 à 22:07 — Compatibilité des hooks (commit `5dcf59b`) — Idir

- Conservation des fins de ligne Unix des hooks pour leur exécution après un clonage sous Windows.

### 19/10/2026 à 22:06 — Documentation et outils (commit `ef95702`) — Idir

- Rassemblement du projet et de sa documentation sur `main` à la demande du propriétaire.
- Ajout du rapport de vérification des critères fournis et des limites des tests locaux.

- Ajout d’un guide de démarrage, d’une présentation de la structure et d’un guide Git pour débuter.
- Ajout de consignes pour maintenir la documentation lors des prochaines modifications.
- Ajout d’un générateur d’historique Git local et d’un hook pour le lancer après chaque commit sur Windows.

## Base existante

- 18/10/2026 à 16:06 — Squelette PHP avec connexion MySQL via PDO et liste des annonces avec titre, type d’animal et dates de garde (commit `8216f50`).
- 18/10/2026 à 19:33 — Schéma SQL des utilisateurs, annonces, candidatures et avis (commit `9a2cc4a`).

Les dates et auteurs des commits sont disponibles dans l’historique Git.
