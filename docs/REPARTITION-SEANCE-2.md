# Séance 2 — Répartition et checklist

Préparé le **20/10/2026 à 10:55 (Europe/Paris)** par **Idir avec l’assistant**, à partir de la checklist fournie et de la lecture du code sur `feature`.

Cette répartition attribue le travail à venir ; elle ne signifie pas qu’Ama ou Maria ont déjà réalisé les tâches. Les cases ne doivent être cochées qu’après vérification. Le code applicatif n’a pas été modifié pour cette répartition.

## Qui fait quoi ?

**Mise à jour du 20/10/2026 à 11:44 — Idir :** adaptation visuelle à la maquette, raccordement des vues auth/avis au header/footer et ajout de la consultation des candidatures. Ama peut reprendre les modèles/contrôleurs/vues Candidature désormais présents et développer les actions sans repartir de zéro. Maria conserve la validation du modèle Utilisateur, les vrais flash des actions auth/avis et la conservation du destinataire dans le formulaire d’avis. La création des listes et le raccordement visuel ne doivent plus être refaits.

**Mise à jour du 20/10/2026 à 12:34 — Ama :** les actions de candidature sont implémentées et intégrées à `main` (`766f561`). L’utilisateur a confirmé dans XAMPP qu’une candidature apparaît avec le statut `en_attente`. Les tests des doublons, des refus d’accès et des décisions restent à effectuer.

| Personne | Responsabilité | Fichiers principaux |
| --- | --- | --- |
| **Idir** | Annonces et socle commun de présentation / messages flash | `models/Annonce.php`, `controllers/AnnonceController.php`, `views/annonces.php` ; à créer : `views/annonce-formulaire.php`, `views/header.php`, `views/footer.php`, `public/assets/style.css`, `core/flash.php` |
| **Ama** | Candidatures : postuler, consulter et accepter/refuser | `models/Candidature.php`, `controllers/CandidatureController.php`, `views/candidatures.php`, `views/candidature-formulaire.php` |
| **Maria** | Finaliser les avis et les formulaires d’authentification | `models/Avis.php`, `controllers/AvisController.php`, `views/avis.php`, `models/Utilisateur.php`, `controllers/AuthController.php`, `views/auth.php` |

Les chemins Ama listés ci-dessus sont maintenant présents ; les lignes de checklist ci-dessous indiquent l’implémentation et les vérifications qui restent à faire.

## État réel à la lecture du code

| Élément | Déjà présent | À terminer |
| --- | --- | --- |
| Architecture | Modèles, contrôleurs, vues et routeur séparés | Conserver cette séparation pour les nouveaux modules |
| Comptes | Inscription, connexion, déconnexion, mots de passe hachés, CSRF et gabarit partagé | Validation dans la méthode de création du modèle ; Maria doit encore remplacer les indicateurs URL par les flash communs |
| Annonces | Lecture, recherche, publication validée, autorisations, flash et dates échappées | Aucun point de la checklist Idir ne reste indiqué comme manquant ; parcours à valider sur la base locale de chaque membre |
| Candidatures | Modèle, contrôleur, formulaire, listes, doublons, autorisations propriétaire, POST/CSRF et statuts | Scénarios doublon, annonce personnelle et décisions par un tiers à tester ; coordination avec Maria sur les règles à confirmer explicitement |
| Avis | Liste, état vide, création, note/commentaire contrôlés, autorisations, doublons et gabarit partagé | Remplacer les indicateurs URL par les flash communs et conserver explicitement le destinataire après une erreur |
| Affichage commun | Header, footer, CSS, navigation, logo et classes partagés dans les vues | Contrôler le rendu complet sur les écrans utilisés par l’équipe |
| Git | Version des candidatures et du logo publiée sur `main` ; branche commune `feature` disponible | Créer des branches par module seulement si la checklist les exige strictement et après accord de l’équipe |

Les anciens essais et confirmations sont décrits dans [VERIFICATION.md](VERIFICATION.md). Ce tableau résulte d’une revue du code, pas d’un nouveau parcours navigateur.

## Checklist fournie : avancement et responsables

| Critère de la séance | Situation actuelle | Responsable et résultat attendu |
| --- | --- | --- |
| La méthode de création valide les données avant d’écrire en base | `Annonce::creer()` et `Candidature::postuler()` vérifient leurs règles avant insertion ; `Avis::creer()` contrôle participants, note et doublons. `Utilisateur::creer()` dépend encore des contrôles du contrôleur. | Maria complète la validation du modèle Utilisateur ; une entrée invalide ne doit créer aucune ligne. |
| La liste fonctionne même vide | Les vues annonces, avis et candidatures affichent un état vide. L’utilisateur a montré une candidature envoyée et la liste reçue vide. | Vérifier aussi les autres combinaisons de comptes et de données. |
| Chaque action redirige avec un message flash | Annonces et candidatures utilisent le flash commun. Authentification et avis utilisent encore des indicateurs dans l’URL. | Maria remplace les indicateurs d’authentification et d’avis par les flash communs ; vérifier le message après première lecture. |
| Les erreurs sont réaffichées sans effacer la saisie | Auth conserve nom/e-mail, candidature conserve le message, avis conserve note/commentaire ; les mots de passe ne sont pas réaffichés. | Maria conserve le destinataire des avis après erreur. |
| Toutes les données affichées passent par htmlspecialchars() | Les vues utilisent `e()` pour le texte et les attributs dynamiques ; identifiants et notes sont convertis en entiers. | Tester des valeurs contenant `<`, `>` et `&` dans chaque module. |
| Header/footer et CSS partagés | Les vues auth, annonces, avis et candidatures utilisent le gabarit et les styles communs. | Contrôler le rendu complet sur ordinateur et téléphone. |
| Code poussé sur feature/ du module | Le travail de candidatures a été publié sur `main` à la demande de l’utilisateur ; les branches par module ne sont pas créées. | La convention avec le formateur et l’équipe reste à confirmer. |
| Expliquer chaque méthode du modèle en une phrase | Les méthodes des modèles de candidature sont documentées dans le tableau ci-dessous. | Chaque membre présente les méthodes de son périmètre. |

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

- [x] Créer le modèle et le contrôleur de candidature, sans SQL dans les vues.
- [x] Permettre à un utilisateur connecté de postuler à une annonce ouverte et accessible ; refuser de postuler à sa propre annonce.
- [x] Valider l’annonce, l’identité issue de la session, le message et les doublons avant insertion.
- [x] Afficher les candidatures utiles au gardien et au propriétaire, y compris un message quand la liste est vide.
- [x] Réserver l’acceptation et le refus au propriétaire de l’annonce ; effectuer les changements par POST avec CSRF.
- [x] Utiliser les statuts `en_attente`, `acceptee`, `refusee` ; le modèle d’avis reconnaît `acceptee`.
- [x] Autoriser plusieurs gardiens acceptés pour une même annonce dans le modèle.
- [x] Réutiliser les flash, le header/footer et le CSS ; conserver le message lors d’une erreur.
- [ ] Confirmer explicitement avec Maria l’accord sur la règle des candidatures acceptées.
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

Contrat disponible depuis le 20/10/2026 à 11:14 : chaque vue définit `$titre`, charge `views/header.php`, affiche son contenu, puis charge `views/footer.php`. Le header charge le CSS, la navigation et les flash. Le contrôleur appelle `ajouterFlash('Message')` ou `ajouterFlash('Message', 'erreur')` avant une redirection 303 ; le header appelle `lireFlash()` qui lit et efface les messages. Les vues auth, annonces, avis et candidatures utilisent le gabarit commun. Les classes CSS sont documentées dans le README.

Mise à jour initiale d’Idir : annonces, formulaire et page indisponible raccordés ; tests PHP/MySQL temporaires réussis (voir VERIFICATION.md). À cette date, la validation visuelle et la publication restaient à faire ; les mises à jour ci-dessus décrivent leur avancement ultérieur. Les tableaux d’état initiaux restent le point de départ de la répartition ; la liste d’Idir ci-dessous indique le travail effectué.

## Ordre de travail

1. Idir définit rapidement le contrat des éléments communs et des flash ; les autres peuvent développer leur modèle sans attendre le CSS.
2. Idir réalise les annonces ; Ama développe les candidatures à partir d’annonces de test convenues ; Maria finalise avis et comptes.
3. Ama et Maria alignent les candidatures acceptées et les destinataires des avis.
4. Chaque personne vérifie son module et documente les résultats réels ; l’équipe teste ensuite le parcours complet.

## Git : publication et branches de module

Ce document a d’abord été publié sur **`feature`**. À la demande explicite de l’utilisateur, les changements de candidatures et du logo ont ensuite été intégrés et poussés sur **`main`** dans le commit `766f561`. Toute future intégration dans `main` requiert toujours une demande explicite. `develop` est disponible pour regrouper le travail quand l’équipe le décide.

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

### Méthodes Candidature implémentées

| Personne | Méthode proposée | Objectif |
| --- | --- | --- |
| Ama | `Candidature::pourUtilisateur()` | Récupérer les candidatures envoyées par le compte ou reçues sur ses annonces. |
| Ama | `Candidature::postuler()` | Vérifier l’annonce, le gardien et les doublons puis créer une candidature en attente dans une transaction. |
| Ama | `Candidature::decider()` | Autoriser le propriétaire à accepter ou refuser une candidature encore en attente. |
| Ama | `Candidature::estAccessible()` | Vérifier qu’une annonce est ouverte et que sa date de fin n’est pas passée. |

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
