# Séance 2 — Répartition et checklist

Préparé le **20/10/2026 à 10:55 (Europe/Paris)** par **Idir avec l’assistant**, à partir de la checklist fournie et de la lecture du code sur `feature`.

Cette répartition attribue le travail à venir ; elle ne signifie pas qu’Ama ou Maria ont déjà réalisé les tâches. Les cases ne doivent être cochées qu’après vérification. Le code applicatif n’a pas été modifié pour cette répartition.

## Qui fait quoi ?

| Personne | Responsabilité | Fichiers principaux |
| --- | --- | --- |
| **Idir** | Annonces et socle commun de présentation / messages flash | `models/Annonce.php`, `controllers/AnnonceController.php`, `views/annonces.php` ; à créer : `views/annonce-formulaire.php`, `views/header.php`, `views/footer.php`, `public/assets/style.css`, `core/flash.php` |
| **Ama** | Candidatures : postuler, consulter et accepter/refuser | À créer : `models/Candidature.php`, `controllers/CandidatureController.php`, `views/candidatures.php`, `views/candidature-formulaire.php` |
| **Maria** | Finaliser les avis et les formulaires d’authentification | `models/Avis.php`, `controllers/AvisController.php`, `views/avis.php`, `models/Utilisateur.php`, `controllers/AuthController.php`, `views/auth.php` |

Les nouveaux chemins sont des propositions de fichiers à créer, pas des fichiers déjà présents.

## État réel à la lecture du code

| Élément | Déjà présent | À terminer |
| --- | --- | --- |
| Architecture | Modèles, contrôleurs, vues et routeur séparés | Conserver cette séparation pour les nouveaux modules |
| Comptes | Inscription, connexion, déconnexion, validation dans le contrôleur, mots de passe hachés et CSRF | Validation dans la méthode de création du modèle, vrais messages flash et gabarit commun |
| Annonces | Lecture de la liste et d’une annonce ; message si liste vide | Formulaire et méthode de création, validation, autorisations, flash ; échapper aussi les dates affichées |
| Candidatures | Table SQL uniquement | Modèle, contrôleur, formulaires, listes et actions |
| Avis | Liste, état vide, création, note/commentaire contrôlés, autorisations et refus des doublons | Vrais flash, conservation de la personne sélectionnée lors d’une erreur et présentation commune |
| Affichage commun | Navigation partagée et logo | Header, footer, feuille CSS commune et classes utilisées dans toutes les vues |
| Git | Code existant publié sur `feature` | Futurs modules à développer et publier ; branches par module à prévoir si la checklist les exige strictement |

Les anciens essais et confirmations sont décrits dans [VERIFICATION.md](VERIFICATION.md). Ce tableau résulte d’une revue du code, pas d’un nouveau parcours navigateur.

## Checklist fournie : avancement et responsables

| Critère de la séance | Situation actuelle | Responsable et résultat attendu |
| --- | --- | --- |
| La méthode de création valide les données avant d’écrire en base | Partiel : `Avis::creer()` contrôle note, longueur, participants et doublons avant l’insertion. `Utilisateur::creer()` dépend de la validation du contrôleur. Aucune création d’annonce ou de candidature. | Chacun valide dans son modèle ; Maria complète aussi Utilisateur. Une entrée invalide ne doit créer aucune ligne. |
| La liste fonctionne même vide | Messages présents pour annonces et avis ; candidatures absentes. | Idir : annonces ; Ama : candidatures ; Maria : avis. Tester avec une liste vide puis remplie. |
| Chaque action redirige avec un message flash | Redirections présentes pour comptes et avis, mais messages de succès fondés sur des paramètres URL ; aucun vrai stockage flash en session. | Idir fournit le mécanisme commun ; chacun l’utilise après ses actions. Le message disparaît après sa première lecture. |
| Les erreurs sont réaffichées sans effacer la saisie | Auth : nom/e-mail conservés, mots de passe volontairement vidés. Avis : note/commentaire conservés, destinataire non conservé explicitement. Nouveaux formulaires absents. | Chacun conserve les champs de son formulaire et échappe les valeurs ; Maria conserve le destinataire des avis. Ne jamais réafficher le mot de passe. |
| Toutes les données affichées passent par htmlspecialchars() | `e()` appelle htmlspecialchars et est utilisée dans auth/avis/navigation. Les dates d’annonces sont affichées directement. Les identifiants et notes convertis en entiers ne sont pas du texte libre. | Idir corrige les dates ; chacun vérifie tous ses textes et attributs dynamiques. Tester des valeurs contenant `<`, `>` et `&`. |
| Header/footer et CSS partagés | Seule la navigation est partagée ; chaque page répète son HTML, aucune feuille CSS commune. | Idir crée les éléments communs et les applique aux annonces / page indisponible ; Ama et Maria raccordent leurs vues au même contrat. |
| Code poussé sur feature/ du module | Une branche commune `feature` existe et contient le code actuel. Les branches `feature/annonces`, etc. n’existent pas à ce stade. | Chacun publie son travail autorisé. Pour une exigence stricte par module, suivre le point Git ci-dessous. |
| Expliquer chaque méthode du modèle en une phrase | Possible pour les modèles existants ; Candidature reste à créer. | Chacun prépare les explications de son module, à partir du tableau ci-dessous. |

## Idir — Annonces et éléments communs

- [x] Ajouter `Annonce::creer()` avec validation du titre, du type d’animal, des dates et du propriétaire connecté avant insertion.
- [x] Refuser une fin antérieure au début et un auteur choisi arbitrairement dans le formulaire ; fixer les règles de dates avec l’équipe.
- [x] Créer le formulaire et l’action de création, conserver la saisie et afficher les erreurs par champ ou en liste.
- [x] Échapper les dates et les autres données affichées dans les annonces.
- [x] Créer le header, le footer et le CSS commun ; inclure la navigation existante dans le header.
- [x] Créer les fonctions flash partagées, avec lecture unique depuis la session.
- [x] Adapter annonces et page indisponible aux éléments communs.
- [x] Tester liste vide, création valide, dates invalides, visiteur non connecté et textes contenant du HTML.

## Ama — Candidatures

- [ ] Créer le modèle et le contrôleur de candidature, sans SQL dans les vues.
- [ ] Permettre à un utilisateur connecté de postuler à une annonce ouverte et accessible ; refuser de postuler à sa propre annonce.
- [ ] Valider l’annonce, l’identité issue de la session, le message et les doublons avant insertion.
- [ ] Afficher les candidatures utiles au gardien et au propriétaire, y compris un message quand la liste est vide.
- [ ] Réserver l’acceptation et le refus au propriétaire de l’annonce ; effectuer les changements par POST avec CSRF.
- [ ] Utiliser les statuts `en_attente`, `acceptee`, `refusee` et coordonner les règles d’acceptation avec Maria. Le code des avis attend exactement `acceptee`.
- [ ] Décider avec l’équipe si une annonce peut avoir plusieurs gardiens acceptés ; appliquer cette règle dans le modèle, pas seulement dans l’interface.
- [ ] Réutiliser les flash, le header/footer et le CSS fournis par Idir ; conserver la saisie lors d’une erreur.
- [ ] Tester doublon, propre annonce, candidature non autorisée, acceptation/refus par un tiers, liste vide et contenu HTML.

## Maria — Avis et finition des comptes

- [ ] Conserver les règles des avis : note 1–5, commentaire facultatif de 2 000 caractères maximum, garde passée, candidature acceptée, auteur/cible distincts, aucun doublon.
- [ ] Conserver la personne évaluée sélectionnée en cas d’erreur, en ne proposant que des personnes encore autorisées.
- [ ] Remplacer les indicateurs de succès `ajoute=1` / `inscrit=1` par les flash communs après les actions ; prévoir aussi un retour de connexion et de déconnexion.
- [ ] Ajouter la validation des données dans `Utilisateur::creer()` pour que l’appel direct au modèle ne contourne pas les règles ; conserver des erreurs compréhensibles dans le contrôleur.
- [ ] Adapter auth et avis aux header/footer et classes CSS communs.
- [ ] Vérifier les textes échappés, les listes d’avis vides, les mauvais mots de passe et la conservation des champs non sensibles.
- [ ] Tester avec Ama le parcours candidature acceptée → garde passée → avis des deux participants.

## Fichiers partagés : coordination obligatoire

| Fichier / contrat | Organisation proposée |
| --- | --- |
| `public/index.php` et `core/bootstrap.php` | Idir coordonne les ajouts de routes et de chargements ; Ama et Maria donnent les noms des classes et actions nécessaires. |
| `views/navigation.php`, header/footer, CSS et flash | Idir prépare le socle ; Ama et Maria l’utilisent sans créer trois variantes concurrentes. |
| `Miaoumi (2).sql` | Schéma existant conservé par défaut. Toute évolution décidée ensemble doit prévoir une migration pour les bases déjà remplies, sans demander une réimportation destructive. |
| README, CHANGELOG et ce tableau | Chaque personne décrit ses changements avec prénom et heure de Paris. Modifier seulement les lignes concernées et récupérer les changements des collègues avant publication. |

Contrat disponible depuis le 20/10/2026 à 11:14 : chaque vue définit `$titre`, charge `views/header.php`, affiche son contenu, puis charge `views/footer.php`. Le header charge le CSS, la navigation et les flash. Le contrôleur appelle `ajouterFlash('Message')` ou `ajouterFlash('Message', 'erreur')` avant une redirection 303 ; le header appelle `lireFlash()` qui lit et efface les messages. Les vues sans header doivent encore être raccordées. Les classes CSS sont documentées dans le README.

Mise à jour Idir : annonces, formulaire et page indisponible raccordés ; tests PHP/MySQL temporaires réussis (voir VERIFICATION.md). La validation visuelle au navigateur et la publication de ce nouveau travail restent à faire. Les tableaux d’état initiaux ci-dessus restent le point de départ de la répartition ; la liste d’Idir ci-dessous indique le travail effectué.

## Ordre de travail

1. Idir définit rapidement le contrat des éléments communs et des flash ; les autres peuvent développer leur modèle sans attendre le CSS.
2. Idir réalise les annonces ; Ama développe les candidatures à partir d’annonces de test convenues ; Maria finalise avis et comptes.
3. Ama et Maria alignent les candidatures acceptées et les destinataires des avis.
4. Chaque personne vérifie son module et documente les résultats réels ; l’équipe teste ensuite le parcours complet.

## Git : publication et branches de module

Ce document est publié sur **`feature`**, conformément à la demande d’Idir. **Aucune intégration dans `main` sans demande explicite.** `develop` est disponible pour regrouper le travail quand l’équipe le décide.

La capture demande une branche `feature/` par module. Attention : Git ne permet pas de faire coexister la branche exacte `feature` avec des branches `feature/annonces`, `feature/candidatures` et `feature/avis` dans le même dépôt, leurs noms entrent en conflit.

Pour le moment, conserver `feature`. Si le formateur exige strictement cette convention, convenir d’abord du renommage de la branche commune, puis créer les branches de module depuis le même code à jour. Aucun renommage ni nouvelle branche n’est effectué par cette répartition. Autre convention possible à faire accepter : `feature-annonces`, `feature-candidatures` et `feature-avis`.

## Une phrase par méthode de modèle

### Méthodes existantes

| Personne chargée de l’expliquer | Méthode | Explication |
| --- | --- | --- |
| Idir | `Annonce::trouver()` | Recherche une annonce par son identifiant et renvoie null si elle n’existe pas. |
| Idir | `Annonce::toutes()` | Récupère les annonces de la plus récente à la plus ancienne. |
| Idir | `Annonce::creer()` | Valide les champs, les dates et le propriétaire avant d’enregistrer une annonce, puis renvoie les erreurs éventuelles. |
| Maria | `Utilisateur::creer()` | Enregistre un utilisateur avec un mot de passe haché, en s’appuyant actuellement sur la validation du contrôleur. |
| Maria | `Utilisateur::trouverParEmail()` | Recherche les informations nécessaires à la connexion d’un utilisateur à partir de son e-mail. |
| Maria | `Avis::pourAnnonce()` | Récupère les avis d’une annonce avec les noms de leurs auteurs et destinataires. |
| Maria | `Avis::destinataires()` | Renvoie les participants qu’un utilisateur peut encore évaluer après la date de fin d’une garde. |
| Maria | `Avis::creer()` | Contrôle les droits et les données puis enregistre un avis dans une transaction en refusant les doublons. |

### Méthodes à créer : noms proposés

| Personne | Méthode proposée | Objectif |
| --- | --- | --- |
| Ama | `Candidature::creer()` | Valider puis enregistrer la candidature d’un gardien à une annonce autorisée. |
| Ama | `Candidature::pourGardien()` | Lister les candidatures déposées par le gardien connecté. |
| Ama | `Candidature::pourProprietaire()` | Lister les candidatures reçues sur les annonces du propriétaire connecté. |
| Ama | `Candidature::changerStatut()` | Accepter ou refuser une candidature après avoir vérifié les droits du propriétaire et les règles de la garde. |

## Validation finale de la séance

- [ ] Chaque modèle refuse les données invalides avant l’insertion.
- [ ] Chaque liste est testée vide et remplie.
- [ ] Chaque action aboutit à une redirection et à un vrai message flash.
- [ ] Les erreurs conservent les champs non sensibles.
- [ ] Les données dynamiques sont échappées à l’affichage.
- [ ] Toutes les pages utilisent les éléments et classes CSS communs.
- [ ] Les commits sont publiés selon la convention de branche convenue avec le formateur.
- [ ] Idir, Ama et Maria peuvent expliquer chacune des méthodes de leur périmètre.

Pour cocher une case, ajouter le prénom, la date et la vérification réalisée ; une attribution de tâche ne constitue pas une preuve de réalisation.
