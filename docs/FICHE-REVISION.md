# Fiche de révision — Miaoumi (oral)

Pour Idir, Ama et Maria. Le professeur évalue deux choses :

1. **Le projet fonctionne et a une finalité** → parties A et B (pitch, démonstration).
2. **La maîtrise technique du code** → parties C à J (architecture, base, méthodes, sécurité, questions).

N’importe qui peut être interrogé sur n’importe quel module : **chacun révise tout**. Cette fiche décrit le code du 15/11/2026, refonte visuelle et modération comprises. Elle remplace les PDF du 07/11/2026 (`FICHE-REVISION-MIAOUMI.pdf`, `FICHE-EXPRESS-ORAL.pdf`), qui ne connaissent ni la note moyenne, ni le nouveau `decider()`, ni les photos et les favoris.

**Méthode** : lire une partie, cacher la page, la réexpliquer à voix haute, puis s’entraîner sur les questions de la partie J (les réponses sont repliées).

---

## A. Le projet en une minute (à savoir par cœur)

> Miaoumi met en relation des **propriétaires d’animaux** et des **gardiens** entre particuliers. Un propriétaire **publie une annonce** de garde avec une photo, des gardiens **postulent**, le propriétaire **accepte** l’un d’eux (l’annonce devient « pourvue »), puis **clôture** la garde. Ensuite, les deux **se notent** de 1 à 5, et la **note moyenne** s’affiche sur le profil public, ce qui crée la confiance. Un gardien peut aussi **mettre des annonces en favori**, et un **administrateur** peut **suspendre** un compte qui pose problème. C’est un site **PHP sans framework**, en **MVC**, avec **MySQL** via **PDO**, réalisé à trois avec **Git et GitHub**.

**Finalité** (le « pourquoi ») : faire garder son animal par quelqu’un de proche et de confiance, sans passer par une pension. La confiance vient de trois choses : les avis réciproques, la note moyenne sur le profil, et le fait qu’un même compte peut être propriétaire **et** gardien.

**Qui a fait quoi** (le dire honnêtement) :

| Personne | Partie |
| --- | --- |
| **Idir** | Annonces (publier, rechercher, paginer, clôturer, modifier, supprimer), socle commun (gabarit, CSS, messages flash, routeur, CSRF), profil public, photos et RGPD, favoris, refonte visuelle de l’interface (icônes, cœur favori sur les cartes), modération (suspendre un compte, page réservée aux administrateurs), intégration Git. A aussi branché le passage en « pourvue » dans `decider()`. |
| **Ama** | Candidatures : postuler, consulter, accepter ou refuser. |
| **Maria** | Comptes (inscription, connexion, validation dans `Utilisateur::creer()`), avis, note moyenne, page de détail, première refonte visuelle (accueil, cartes avec photos), données de démo. |

---

## B. La démonstration (environ 5 minutes)

**Avant l’oral** : XAMPP lancé (Apache et MySQL), base importée dans cet ordre : `sql/miaoumi.sql`, puis `sql/donnees-demo.sql` (pour remettre la démo à zéro, il suffit de réimporter `donnees-demo.sql`). Mot de passe de tous les comptes de démo : **`demo1234`**.

| Étape | Compte | Ce qu’on montre | Ce qu’on dit (technique) |
| --- | --- | --- | --- |
| 1 | visiteur | Accueil : bannière, chiffres **réels**, recherche (ville, animal, mot-clé), pagination | Chiffres calculés en SQL (`statistiques()`) ; `Annonce::rechercher()` construit le `WHERE` avec des `?` ; 6 annonces par page avec `array_slice()` |
| 2 | visiteur | Clic sur « Mes annonces » : redirection vers la connexion | Chaque contrôleur vérifie la session |
| 3 | `claire@demo.miaoumi.fr` | Publier une annonce **avec photo** ; essayer une date de fin avant le début | Validation dans le modèle **avant** l’INSERT, code 422, saisie conservée ; `PhotoUploader` |
| 4 | Claire | Modifier l’annonce : remplacer la photo, puis cocher « Supprimer la photo actuelle » | L’ancien fichier est effacé du disque, pas de fichier orphelin |
| 5 | `sofia@demo.miaoumi.fr` | Cliquer sur le **cœur** d’une carte, ouvrir « Mes favoris », postuler | Le cœur est un petit formulaire POST + CSRF ; clé primaire composée ; transaction `FOR UPDATE` |
| 6 | Sofia | Tenter d’ouvrir `?page=modifier-annonce&id=…` d’une annonce de Claire | **403** : propriétaire seulement |
| 7 | Claire | « Candidatures reçues » sur « Garde de Moka » : accepter une candidature | `decider()` : annonce « pourvue », autres candidatures refusées, dans la même transaction |
| 8 | Claire | « Terminer la garde » (confirmation), puis laisser un avis à Sofia | `cloturer()` ; avis possible dès la clôture (`gardeFinie()`) |
| 9 | Sofia | Laisser un avis à Claire, voir la note moyenne sur un profil | `AVG(note)` ; « Pas encore d’avis » quand il n’y en a pas |
| 10 | `bastien@demo.miaoumi.fr` | Se connecter : « Ce compte est suspendu » | `actif = 0`, refusé **après** `password_verify()` |
| 11 | `admin@demo.miaoumi.fr` | Menu du compte → « Modération » : suspendre Sofia (connectée dans un autre navigateur), recharger sa page, puis la réactiver | POST + CSRF, `WHERE role <> 'admin'` ; Sofia est déconnectée à sa page suivante (`verifierCompteConnecte()`) |
| 12 | tous | Taper `?page=nimporte` | Page 404 avec le gabarit du site, jamais une page blanche |

**À dire honnêtement si on le demande** : pas de suppression de compte, pas de page pour nommer un administrateur (le rôle se change en base), les annonces d’un compte suspendu restent visibles, pas de clôture automatique à la date de fin, pas de classement des gardiens, pas de messagerie entre utilisateurs.

---

## C. L’architecture MVC et le trajet d’une requête

```
Navigateur ── ?page=creer-annonce ──► public/index.php (routeur, seul point d'entrée)
                                         │ charge app/core/bootstrap.php :
                                         │   session, classes, e(), vérification CSRF des POST
                                         ▼
                                   Contrôleur (app/controllers/) ── appelle ──► Modèle (app/models/) ──► MySQL (PDO)
                                         │                                         ◄── données ou erreurs
                                         ▼
                                   Vue (app/views/) : HTML, chaque valeur passe par e()
```

- **Modèle** : les requêtes SQL et les règles métier (validation avant d’écrire). Ne fait jamais d’affichage.
- **Vue** : seulement l’affichage HTML. Aucune requête SQL.
- **Contrôleur** : lit la requête (`$_GET`, `$_POST`, `$_FILES`, `$_SESSION`), vérifie les droits, appelle le modèle, puis affiche une vue **ou** redirige.
- **Routeur** (`public/index.php`) : un tableau `$routes` associe chaque `?page=…` à `[Contrôleur, méthode]`. Page inconnue : **404**. Base indisponible (`PDOException`) : page `indisponible.php` avec **503**.
- **`bootstrap.php`** : démarre la session (cookie `HttpOnly`, `SameSite=Lax`, sessions stockées dans `storage/sessions/`), charge les classes avec `require_once`, définit `e()` et refuse tout POST sans jeton CSRF valide (**403**).
- **`app/core/moderation.php`** : `verifierCompteConnecte()`, appelée par `public/index.php` **avant chaque contrôleur** : relit en base si le compte connecté est toujours actif (sinon déconnexion) et son rôle ; `estAdmin()` pour les vues et contrôleurs.
- **`app/core/icones.php`** : fonction `icone('coeur')` qui renvoie une petite icône SVG écrite dans la page (aucun fichier ni service externe).
- **`public/assets/style.css`** : une seule feuille de style ; les couleurs sont des **variables CSS** (`--accent` marron doré, `--menthe`, `--texte` vert foncé…) définies une fois dans `:root` et réutilisées partout.
- **`config/database.php`** : classe `Database`, connexion PDO **unique** (patron singleton, propriété `static`), configurée par `config/config.php`, qui contient le mot de passe et n’est donc **jamais versionné**.

**Patron Post/Redirect/Get** : après un POST réussi, le contrôleur ajoute un message flash et **redirige (303)**. Recharger la page ne renvoie donc pas le formulaire, et le message s’affiche une seule fois.

### Les routes (`?page=…`)

| Page | Contrôleur::méthode | Méthode HTTP |
| --- | --- | --- |
| `annonces`, `mes-annonces` | `AnnonceController::index` | GET |
| `annonce` (fiche) | `AnnonceController::voir` | GET |
| `creer-annonce`, `modifier-annonce` | `AnnonceController::creer` / `modifier` | GET + POST |
| `supprimer-annonce`, `cloturer-annonce`, `supprimer-photo-annonce` | `AnnonceController::supprimer` / `cloturer` / `supprimerPhoto` | POST seulement |
| `candidatures`, `mes-candidatures`, `candidatures-recues` | `CandidatureController::index` | GET |
| `postuler` | `CandidatureController::postuler` | GET + POST |
| `decision-candidature` | `CandidatureController::decision` | POST seulement |
| `avis` | `AvisController::index` | GET + POST |
| `profil`, `photo-profil` | `ProfilController::index` / `photo` | GET / POST |
| `basculer_favori`, `mes_favoris` | `FavoriController::basculer` / `mesFavoris` | POST / GET |
| `inscription`, `connexion`, `deconnexion` | `AuthController::…` | GET + POST (déconnexion : POST seulement) |
| `admin-utilisateurs`, `suspendre-utilisateur` | `AdminController::utilisateurs` / `suspendre` | GET / POST (administrateurs seulement) |

---

## D. La base de données

| Table | Contenu | Clés |
| --- | --- | --- |
| `utilisateurs` | nom, e-mail **unique**, mot de passe **haché**, rôle (`user` ou `admin`), `actif` (0 = suspendu), photo | PK `id` |
| `annonces` | titre, type d’animal, ville, dates, description, photo, statut (`ouverte` → `pourvue` → `terminee`) | PK `id`, FK `id_proprietaire` |
| `candidatures` | message, statut (`en_attente`, `acceptee`, `refusee`), date | PK `id`, FK `id_annonce`, `id_gardien` |
| `avis` | note de 1 à 5, commentaire, date | PK `id`, FK `id_annonce`, `id_auteur`, `id_cible` |
| `favoris` | date d’ajout | **PK composée** `(id_gardien, id_annonce)`, qui sont aussi des FK |

- **Pas de rôle fixe propriétaire/gardien** : le même compte peut publier (propriétaire) et postuler (gardien). La colonne `role` sert seulement à distinguer les administrateurs (`admin`) qui modèrent.
- **`ON DELETE CASCADE`** sur toutes les clés étrangères : supprimer une annonce supprime ses candidatures, avis et favoris.
- **Plusieurs-à-plusieurs** : `favoris` est une **table de liaison** entre `utilisateurs` et `annonces`. Sa clé primaire composée empêche d’ajouter deux fois la même annonce.
- **Migrations** (`sql/migrations/`), pour les bases déjà créées : `001` ville, `002` renomme `avis.date` en `dates`, `003` photo, `004` favoris, `005` colonne `actif` (modération). Le code reste compatible sans les migrations 001 et 003 : `villeDisponible()` et `photoDisponible()` testent l’existence de la colonne avec `SHOW COLUMNS`.
- **MCD** : une annonce est publiée par un utilisateur, reçoit 0..n candidatures et 0..n avis ; un avis relie un auteur et une cible.
- **MLD** : chaque association 1–n devient une clé étrangère ; une association n–n (favoris) devient une table.

---

## E. Les méthodes des modèles en une phrase

Version simplifiée, avec les questions probables : [FICHE-FONCTIONS.md](FICHE-FONCTIONS.md).

### Annonce (`app/models/Annonce.php`)
- `rechercher()` : liste filtrée (texte, animal, ville) ; seulement les annonces ouvertes pour tous, ou toutes celles du propriétaire dans « Mes annonces » ; joint le nom et la note moyenne du propriétaire.
- `valider()` : règles communes à la création et à la modification ; renvoie un tableau d’erreurs par champ.
- `creer()` : `valider()`, vérifie que le compte existe, puis fait l’INSERT au statut `ouverte` ; renvoie les erreurs, ou `[]` si l’annonce est créée.
- `estModifiable()` : vrai si l’utilisateur est le propriétaire **et** que l’annonce est ouverte.
- `modifier()` / `supprimer()` : UPDATE / DELETE avec `WHERE id = ? AND id_proprietaire = ? AND statut = 'ouverte'`.
- `definirPhoto()` : enregistre le nom de la photo, ou `NULL`. `photoUrl()` : adresse de l’image (photo envoyée, sinon image du type d’animal).
- `cloturer()` : vérifie l’annonce, le propriétaire et le statut, puis passe à `terminee` (conditions répétées dans le `WHERE`).
- `marquerPourvue()` : `ouverte` → `pourvue` ; appelée quand une candidature est acceptée.
- `detail()` : la fiche d’une annonce, avec le nom du propriétaire (`JOIN`) et le nombre de candidatures (**sous-requête** `SELECT COUNT(*)`) ; `null` si elle n’existe pas (le contrôleur affiche alors 404).
- `trouver()` : une annonce brute (`SELECT * … WHERE id = ?`) ; sert aux contrôleurs pour vérifier qu’elle existe et à qui elle est.
- `statistiques()` : chiffres de la bannière en une requête : `SUM(statut = 'ouverte')` (une comparaison vaut 1 ou 0, donc la somme compte), `SUM(statut = 'terminee')` et `COUNT(DISTINCT NULLIF(ville, ''))` (villes différentes, sans les vides).
- `retirerPhoto()` : vérifie que l’annonce existe, est à lui et a une photo, puis met `photo = NULL` (propriétaire répété dans le `WHERE`) ; renvoie le nom de l’ancien fichier pour que le contrôleur l’efface du disque.
- `toutes()` : toutes les annonces, la plus récente en premier ; utilisée par `tests/annonces.php`, pas par les pages.
- `villeDisponible()` / `photoDisponible()` : `SHOW COLUMNS … LIKE 'ville'` : vrai si la migration a été appliquée ; le code s’adapte aux bases anciennes au lieu de planter.

### Candidature (`app/models/Candidature.php`)
- `postuler()` : **transaction** avec `SELECT … FOR UPDATE` qui verrouille l’annonce ; refuse une annonce non ouverte ou expirée, sa propre annonce et les doublons, puis insère en `en_attente`.
- `decider()` : transaction ; seul le propriétaire, seulement une candidature en attente. **À l’acceptation** : l’annonce passe en `pourvue` et les autres candidatures en attente sont refusées.
- `pourUtilisateur()` : candidatures envoyées **et** reçues. `estAccessible()` (privée) : annonce ouverte et date de fin non passée.

### Avis (`app/models/Avis.php`)
- `gardeFinie()` : garde clôturée **ou** date de fin passée.
- `destinataires()` : personnes que l’on a le droit de noter (garde finie, candidature acceptée, pas déjà notée).
- `creer()` : transaction ; revérifie la note (1 à 5), le commentaire (2 000 caractères maximum) et le droit de noter cette cible, puis insère.
- `noteMoyenne()` : `SELECT AVG(note) … WHERE id_cible = ?` ; `null` sans avis (`AVG` renvoie `NULL`), sinon arrondie à une décimale.
- `pourAnnonce()` : avis d’une annonce, avec le nom de l’auteur **et** de la cible : on joint **deux fois** la table `utilisateurs` avec deux alias (`auteur`, `cible`).
- `recus()` : avis reçus par un utilisateur (`WHERE id_cible = ?`), avec l’auteur et le titre de l’annonce ; affichés sur son profil.
- `derniers($limite)` : les derniers avis positifs (note ≥ 4, avec commentaire) pour l’accueil ; `LIMIT ?` lié avec `bindValue(…, PDO::PARAM_INT)`, car `LIMIT` exige un entier.
- `statistiques()` : `COUNT(*)` et `ROUND(AVG(note), 1)` sur tous les avis, pour la bannière.

### Utilisateur (`app/models/Utilisateur.php`)
- `creer()` : valide le nom (1 à 100), l’e-mail (format, 150 caractères maximum, pas déjà pris) et le mot de passe (8 à 72 octets) **avant** l’INSERT, puis le hache avec `password_hash()`.
- `trouverParEmail()` (connexion, lit aussi `role` et `actif`), `trouver()` (profil public, jamais l’e-mail), `changerPhoto()`, `photoDisponible()`.
- `etatCompte()` : nom, rôle et `actif`, relus à chaque page pour l’utilisateur connecté.
- `tous()` : liste de modération, avec `LEFT JOIN annonces` + `COUNT` + `GROUP BY` pour le nombre d’annonces (un compte sans annonce apparaît avec 0).
- `changerActif()` : `UPDATE utilisateurs SET actif = ? WHERE id = ? AND role <> 'admin'` ; renvoie faux si rien n’a changé (administrateur ou compte inexistant).

### Favori (`app/models/Favori.php`)
- `existe()`, `retirer()`, `compterPourAnnonce()` (`COUNT`, montré au propriétaire seulement).
- `ajouter()` : `INSERT IGNORE INTO favoris … SELECT ?, id FROM annonces WHERE id = ? AND id_proprietaire <> ?`. La ligne n’est insérée que si l’annonce existe et n’est pas la sienne ; un doublon est ignoré grâce à la clé primaire.
- `parGardien()` : annonces + nom du propriétaire, `ORDER BY date_ajout DESC` (la dernière ajoutée en premier).
- `idsParGardien()` : identifiants des annonces en favori, pour remplir le cœur des cartes de la liste.

### PhotoUploader (`app/core/PhotoUploader.php`)
- `enregistrer($_FILES['photo'])` : renvoie le **nom** du fichier enregistré, ou lève une `RuntimeException` dont le message s’affiche dans le formulaire.
- `supprimer($nom)` : efface le fichier, sans erreur s’il n’existe plus ; garde seulement `basename()` et vérifie la forme du nom (pas de `../`).
- `url($nom)` : chemin public `uploads/annonces/…` si le nom est valide, sinon `null`.
- Privées : `nomValide()` (regex `^[a-f0-9]{32}\.(jpg|png|webp)$`, la forme exacte des noms que le serveur génère), `dossier()` (n’accepte que `annonces` ou `profils`, crée le dossier si besoin), `retirerMetadonnees()` (recrée l’image avec GD pour enlever l’EXIF, dont la position GPS).
- `enregistrer()` en détail : erreur d’envoi (`UPLOAD_ERR_*`) → message ; taille ≤ 2 Mo ; type lu dans le contenu avec `finfo` (JPEG, PNG, WebP seulement) ; dimensions ≤ 4 000 px ; nom `bin2hex(random_bytes(16))` + extension ; `move_uploaded_file()`.

### Fonctions utiles hors modèles (`app/core/`)
- `photoAnimal($annonce)` : photo envoyée si elle existe, sinon l’illustration SVG du type d’animal (`cleAnimal()` met en minuscules, enlève accents et pluriel ; `autre.svg` par défaut).
- `ajouterFlash()` / `lireFlash()` : message rangé en session, lu puis effacé (une seule fois).
- `e()` : `htmlspecialchars` pour tout affichage. `icone()` : icône SVG.
- `verifierCompteConnecte()` / `estAdmin()` : modération (voir plus haut).

**Astuce pour réviser** : pour chaque modèle, savoir dire (1) quelle table il interroge, (2) quelle méthode **écrit** (INSERT/UPDATE/DELETE) et ce qu’elle vérifie avant, (3) où il y a une **transaction** (`postuler`, `decider`, `Avis::creer`), un **`JOIN`**, un **`COUNT`/`AVG`**.

---

## F. Les contrôleurs : ce qu’ils vérifient

Règle générale : **connecté ?** (sinon redirection vers la connexion) → **bonne méthode ?** (sinon 405) → **jeton CSRF** (403, dans `bootstrap.php`) → **la ressource existe ?** (sinon 404) → **elle est à lui ?** (sinon 403 ou message d’erreur) → appel du modèle → **flash + redirection 303**, ou réaffichage avec **422**.

- `AnnonceController::creer()` : champs validés **d’abord**, photo traitée **ensuite** (pas de fichier pour une annonce refusée) ; si l’INSERT échoue, la photo est effacée.
- `AnnonceController::modifier()` : 404 si l’annonce n’existe pas, 403 si elle n’est pas modifiable. Nouvelle photo : elle remplace l’ancienne et l’ancien fichier est effacé. Case cochée : photo retirée et fichier effacé. Sinon, la photo est gardée.
- `FavoriController::basculer()` : les cinq règles du sujet, dans l’ordre. Le **retour** n’accepte que `mes_favoris` ou la fiche : jamais une adresse libre (pas de **redirection ouverte**).
- `CandidatureController::decision()`, `AvisController::index()`, `ProfilController::photo()` : identité lue **dans la session**, jamais dans le formulaire.
- `AdminController` : `exigerAdmin()` d’abord (visiteur → connexion, non-admin → **403**), puis POST, CSRF, compte existant (404) ; `suspendre()` bascule entre suspendre et réactiver.
- `AuthController::connexion()` : mot de passe vérifié **puis** compte actif ; sinon « Ce compte est suspendu » (403).

---

## G. La sécurité (questions presque certaines)

| Attaque | Protection | Où |
| --- | --- | --- |
| **Injection SQL** | Requêtes préparées (`prepare` + `execute` avec des `?`). Les seuls `query()` sont du SQL fixe, sans donnée de l’utilisateur. | tous les modèles |
| **XSS** | Toute valeur affichée passe par `e()` = `htmlspecialchars(…, ENT_QUOTES, 'UTF-8')` | vues |
| **CSRF** | Jeton aléatoire (`random_bytes`) en session, caché dans chaque formulaire, comparé avec `hash_equals()` à chaque POST, sinon 403 | `bootstrap.php` |
| **Mots de passe** | `password_hash()` / `password_verify()` (bcrypt), jamais en clair, jamais réaffichés ; 72 octets maximum | `Utilisateur`, `AuthController` |
| **Vol de session** | `session_regenerate_id(true)` à la connexion et à la déconnexion, cookie `HttpOnly` et `SameSite=Lax`, nouveau jeton CSRF à la connexion | `AuthController`, `bootstrap.php` |
| **Accès aux données des autres** | Propriétaire et auteur lus dans la session ; droits revérifiés dans le modèle **et** dans le `WHERE` | contrôleurs et modèles |
| **Action par un simple lien** | Les actions qui modifient sont en **POST seulement** (405 sinon) : déconnexion, clôture, suppression, favoris | contrôleurs |
| **Doublons simultanés** | Transaction + `SELECT … FOR UPDATE` ; clé primaire composée pour les favoris | `postuler()`, `Avis::creer()`, `favoris` |
| **Fichier piégé** | Type lu dans le **contenu** (`finfo`) et liste blanche, nom aléatoire choisi par le serveur, 2 Mo, `move_uploaded_file`, `.htaccess` qui interdit d’exécuter du PHP dans `uploads/` | `PhotoUploader`, `public/uploads/.htaccess` |
| **Traversée de dossier** | `supprimer()` garde seulement le nom (`basename`) et vérifie sa forme | `PhotoUploader` |
| **Redirection ouverte** | Le champ `retour` n’accepte que deux valeurs connues | `FavoriController` |
| **Accès au back-office** | Rôle relu **en base** à chaque page (pas seulement à la connexion), 403 pour un non-admin, administrateur jamais suspendable (`WHERE role <> 'admin'`) | `moderation.php`, `AdminController`, `Utilisateur::changerActif()` |
| **Secrets dans Git** | `config/config.php`, `storage/` et les photos envoyées sont dans `.gitignore` | `.gitignore` |

**RGPD des photos** : consentement (case obligatoire pour la photo de profil), minimisation (on photographie seulement l’animal, la base ne garde que le nom du fichier), métadonnées GPS supprimées (image recréée avec GD), droit à l’effacement (boutons de suppression qui effacent vraiment le fichier).

---

## H. Notions à savoir expliquer

- **Authentification / autorisation** : l’authentification vérifie **qui** tu es (e-mail + mot de passe, `password_verify`) ; l’autorisation vérifie **ce que tu as le droit de faire** (propriétaire de l’annonce, rôle `admin`). Un compte suspendu est authentifié correctement mais n’est plus autorisé à se connecter.
- **`LEFT JOIN`** : garde aussi les lignes de gauche sans correspondance (un compte sans annonce apparaît avec `COUNT = 0`) ; un `JOIN` simple les ferait disparaître. Avec `COUNT`, il faut un **`GROUP BY`**.
- **Codes HTTP** : 200 OK, 303 redirection après un POST, 403 interdit ou jeton invalide, 404 introuvable, 405 mauvaise méthode, 409 conflit (candidature en double), 422 formulaire invalide, 503 base indisponible.
- **Message flash** : `ajouterFlash()` range le message dans `$_SESSION` **avant** la redirection ; le header l’affiche avec `lireFlash()`, qui l’efface aussitôt (une seule fois).
- **Transaction** : un groupe de requêtes validé en entier (`commit`) ou annulé en entier (`rollBack`). Exemple : accepter une candidature, passer l’annonce en « pourvue » et refuser les autres, tout ou rien.
- **`FOR UPDATE`** : verrouille la ligne lue jusqu’à la fin de la transaction ; deux envois simultanés passent l’un après l’autre.
- **Requête préparée** : le SQL est envoyé avec des `?`, puis les valeurs séparément : une valeur ne peut jamais devenir du code SQL.
- **Singleton** : une seule connexion PDO par page, réutilisée par tous les modèles (`Database::getConnection()`).
- **`ON DELETE CASCADE`**, **clé primaire composée**, **table de liaison**, **jointure** (`JOIN`), **sous-requête**, **`AVG`**, **`COUNT`**.
- **Upload** : `$_FILES['photo']` contient `name` (jamais utilisé), `type` (envoyé par le navigateur, jamais cru), `tmp_name`, `error`, `size` ; `move_uploaded_file()` déplace seulement un vrai fichier envoyé.
- **Pagination** : `array_slice($annonces, ($numero - 1) * 6, 6)`, numéro de page ramené entre 1 et le nombre de pages.
- **Variables CSS** : `--accent:#6c4f13` déclarée dans `:root`, utilisée avec `var(--accent)` ; changer la couleur à un seul endroit change tout le site.
- **Responsive** : des `@media (max-width: …)` changent la mise en page : 3 colonnes de cartes sur ordinateur, 2 sur tablette, 1 sur mobile ; la bannière passe en une colonne.
- **Image par annonce** : `photoAnimal()` affiche la photo envoyée par le propriétaire ; sans photo, l’**illustration du type d’animal** (`chat.svg`, `chien.svg`… et une patte pour « Autre »), jamais une photo prise au hasard.

---

## I. Git, tests et organisation

- **Circuit** : `feature/prénom-fonctionnalité` → pull request → `develop` (relu et fusionné par Idir) → `main`. Un hook `pre-push` refuse les envois sur `main` ou `develop` par quelqu’un d’autre qu’Idir, ainsi que les noms de branches non conformes.
- **CHANGELOG.md** : chaque changement avec date, heure de Paris et prénom. **README** : MCD, MLD, diagramme de classes, logique métier.
- **Tests** (tables MySQL **temporaires**, la vraie base n’est pas touchée) :
  `& C:\xampp\php\php.exe tests/annonces.php`, puis `tests/parcours.php`, `tests/modification.php`, `tests/favoris.php` et `tests/moderation.php`. Chacun affiche une ligne « OK : … », ou s’arrête sur la première erreur.
- **Conflit Git** : quand deux branches modifient les mêmes lignes, Git place des marqueurs `<<<<<<<`, `=======` et `>>>>>>>`. On les lit, on garde le bon contenu (souvent les deux), on enlève les marqueurs, puis on commite.

---

## J. Questions possibles et réponses modèles

### Projet et finalité

<details><summary>À quoi sert votre site, pour qui ?</summary>

À faire garder son animal par un particulier de confiance près de chez soi. Il s’adresse aux propriétaires d’animaux et aux gardiens ; le même compte peut jouer les deux rôles. La confiance repose sur les avis réciproques et la note moyenne affichée sur le profil.
</details>

<details><summary>Quel est le cycle de vie d’une annonce ?</summary>

`ouverte` à la publication (modifiable et supprimable par son propriétaire), puis `pourvue` quand le propriétaire accepte un gardien (les autres candidatures sont refusées), puis `terminee` quand il clôture la garde. Les avis sont possibles dès que la garde est finie : clôturée, ou date de fin passée.
</details>

<details><summary>Qu’est-ce qui ne fonctionne pas, ou pas encore ?</summary>

Pas de suppression de compte (on peut seulement le suspendre), pas de clôture automatique à la date de fin, pas de classement des gardiens ni de messagerie. On ne peut pas nommer un administrateur depuis le site : le rôle se change en base. Si on supprimait un compte directement en base, les lignes partiraient en cascade, mais pas les fichiers photo.
</details>

### Architecture

<details><summary>Pourquoi le MVC ?</summary>

Pour séparer les responsabilités : le modèle parle à la base, la vue affiche, le contrôleur décide. On peut changer l’affichage sans toucher au SQL, et l’équipe a pu se répartir le travail par module.
</details>

<details><summary>Que se passe-t-il quand on tape <code>?page=mes_favoris</code> ?</summary>

`public/index.php` charge `bootstrap.php` (session, classes, CSRF), trouve `'mes_favoris' => [FavoriController::class, 'mesFavoris']`, crée le contrôleur et appelle la méthode. Celle-ci vérifie la connexion (sinon redirection), appelle `Favori::parGardien()`, puis charge la vue `favoris.php`, qui affiche chaque valeur avec `e()`.
</details>

<details><summary>Pourquoi PDO plutôt que mysqli ?</summary>

PDO gère simplement les requêtes préparées, lève des exceptions en cas d’erreur (`ERRMODE_EXCEPTION`) et fonctionne avec plusieurs bases de données.
</details>

<details><summary>Où se fait la validation d’un formulaire ?</summary>

Dans le **modèle**, avant l’INSERT ou l’UPDATE (`Annonce::valider()`, `Utilisateur::creer()`, `Avis::creer()`) : une donnée invalide ne crée jamais de ligne. Le contrôleur ne vérifie que la forme (méthode, champ présent, type). Les attributs HTML (`required`, `maxlength`) ne sont qu’un confort : on peut les contourner.
</details>

### Base de données

<details><summary>Pourquoi une table <code>favoris</code> et pas une colonne dans <code>annonces</code> ?</summary>

Parce que c’est une relation plusieurs-à-plusieurs : un gardien a plusieurs favoris et une annonce peut être favorite de plusieurs gardiens. Une colonne ne pourrait contenir qu’un seul gardien ; il faut une table de liaison avec les deux clés étrangères.
</details>

<details><summary>À quoi sert la clé primaire composée de <code>favoris</code> ?</summary>

Le couple `(id_gardien, id_annonce)` est unique : la base elle-même refuse d’ajouter deux fois la même annonce, même si deux clics arrivent en même temps. `INSERT IGNORE` transforme ce refus en « rien n’est inséré », sans erreur.
</details>

<details><summary>Que fait <code>ON DELETE CASCADE</code> ?</summary>

Supprimer une ligne parente supprime automatiquement les lignes qui la référencent. Supprimer une annonce efface donc ses candidatures, ses avis et ses favoris, sans laisser de ligne orpheline.
</details>

<details><summary>Comment est calculée la note moyenne ?</summary>

`SELECT AVG(note) FROM avis WHERE id_cible = ?`. Sans avis, `AVG` renvoie `NULL` : la méthode renvoie `null` et le profil affiche « Pas encore d’avis ». Sinon, elle arrondit à une décimale et affiche par exemple « 4,8 / 5 ».
</details>

### Sécurité

<details><summary>Comment empêchez-vous l’injection SQL ? Montrez un exemple.</summary>

Avec des requêtes préparées : `prepare('SELECT … WHERE id_cible = ?')`, puis `execute([$id])`. La valeur est envoyée séparément du SQL : si quelqu’un tape `' OR 1=1 --`, la base cherche littéralement ce texte. Dans `rechercher()`, même les filtres optionnels ajoutent des `?`, jamais la valeur elle-même.
</details>

<details><summary>Qu’est-ce que le CSRF, et comment vous en protégez-vous ?</summary>

Un autre site fait envoyer un formulaire à votre insu, avec votre session (par exemple « supprimer mon annonce »). Chaque formulaire contient un jeton aléatoire stocké en session ; `bootstrap.php` le compare avec `hash_equals()` à chaque POST et répond 403 s’il manque ou s’il est faux. L’autre site ne connaît pas ce jeton.
</details>

<details><summary>Pourquoi échapper à l’affichage et pas à l’enregistrement ?</summary>

On stocke la donnée telle quelle et on l’échappe selon l’endroit où on l’affiche (HTML ici). L’échapper en base l’abîmerait (`&amp;` dans les exports, double échappement).
</details>

<details><summary>Que se passe-t-il si quelqu’un modifie l’identifiant du propriétaire dans le formulaire ?</summary>

Rien : le propriétaire est lu dans `$_SESSION`, jamais dans le formulaire. Et pour modifier ou supprimer, le `WHERE` contient `id_proprietaire = ?` avec l’identifiant de la session.
</details>

<details><summary>Pourquoi la déconnexion et les favoris sont-ils en POST et pas en simple lien ?</summary>

Une action qui modifie des données ne doit pas pouvoir se déclencher par un lien ou une image piégée. En POST, elle exige aussi le jeton CSRF. Un GET sur ces adresses renvoie 405.
</details>

<details><summary>Que fait votre site si on envoie un fichier PHP renommé en <code>photo.jpg</code> ?</summary>

`PhotoUploader` lit le type réel dans le contenu avec `finfo` : il trouve `text/x-php`, absent de la liste blanche (JPEG, PNG, WebP). L’envoi est refusé avec un message et rien n’est enregistré. Même si un fichier dangereux passait, il aurait un nom aléatoire, et le `.htaccess` du dossier `uploads` interdit d’y exécuter du PHP : c’est une défense en profondeur.
</details>

<details><summary>Pourquoi ne pas garder le nom du fichier envoyé ?</summary>

Il peut contenir `../` (sortir du dossier), écraser un autre fichier, révéler des informations personnelles, ou se terminer par `.php`. Le serveur choisit donc `bin2hex(random_bytes(16))` suivi de l’extension du type **détecté** : un JPEG renommé en `.png` est enregistré en `.jpg`.
</details>

<details><summary>Pourquoi <code>basename()</code> dans <code>supprimer()</code> ?</summary>

Pour ne garder que le nom du fichier : avec `../../config/config.php`, on obtiendrait sinon un chemin vers un fichier hors du dossier des photos, qui serait effacé. En plus, le nom doit avoir la forme « 32 caractères hexadécimaux + extension ».
</details>

<details><summary>Qu’est-ce qu’une redirection ouverte, et comment l’évitez-vous ?</summary>

C’est quand le site redirige vers une adresse fournie par l’utilisateur : un pirate envoie un lien vers votre site qui renvoie vers son faux site. Dans `basculer()`, le champ `retour` n’accepte que `mes_favoris` ; toute autre valeur ramène à la fiche de l’annonce.
</details>

<details><summary>Un utilisateur suspendu ou supprimé reste-t-il connecté ?</summary>

Il n’y a pas de suspension dans Miaoumi. La session garde l’identifiant jusqu’à la déconnexion ; en revanche, `Annonce::creer()` vérifie que le compte existe encore avant d’insérer (« Ce compte n’existe plus »). Pour une vraie suspension, il faudrait revérifier l’état du compte à chaque requête.
</details>

### Candidatures (module d’Ama)

<details><summary>Comment empêchez-vous de postuler deux fois ?</summary>

`postuler()` ouvre une transaction, verrouille l’annonce avec `SELECT … FOR UPDATE`, vérifie qu’aucune candidature n’existe pour ce gardien sur cette annonce, puis insère. Deux envois simultanés sont traités l’un après l’autre ; le second trouve la première candidature et il est refusé (409).
</details>

<details><summary>Que se passe-t-il quand le propriétaire accepte une candidature ?</summary>

Dans une seule transaction, `decider()` passe la candidature en `acceptee`, appelle `Annonce::marquerPourvue()` et refuse les autres candidatures en attente de cette annonce. Si une étape échoue, `rollBack` annule tout.
</details>

### Avis et comptes (module de Maria)

<details><summary>Qui peut laisser un avis ?</summary>

Seulement le propriétaire et un gardien dont la candidature a été acceptée, une fois la garde finie (clôturée ou date de fin passée), une seule fois par personne notée, et jamais à soi-même. `destinataires()` calcule la liste et `creer()` la revérifie dans une transaction.
</details>

<details><summary>Pourquoi limiter le mot de passe à 72 octets ?</summary>

`password_hash()` utilise bcrypt, qui ignore tout ce qui dépasse 72 octets : deux mots de passe différents après le 72e octet seraient acceptés comme identiques. On compte en octets avec `strlen`, pas en caractères.
</details>

<details><summary>Pourquoi <code>session_regenerate_id()</code> à la connexion ?</summary>

Pour changer l’identifiant de session au moment où l’utilisateur devient connecté : si un pirate avait réussi à imposer ou à connaître l’ancien identifiant (fixation de session), celui-ci ne sert plus à rien.
</details>

### Photos et favoris (sujet PetSitter V2)

<details><summary>Pourquoi traiter la photo seulement après la validation des champs ?</summary>

Pour ne pas enregistrer de fichier sur le disque pour une annonce qui ne sera pas créée. Et si l’INSERT échoue quand même, le contrôleur efface la photo qu’il vient d’enregistrer.
</details>

<details><summary>Comment évitez-vous les fichiers orphelins ?</summary>

À chaque opération qui fait disparaître une photo de la base, on efface aussi le fichier avec `PhotoUploader::supprimer()` : remplacement par une nouvelle photo, case « Supprimer la photo actuelle », bouton « Supprimer la photo », suppression de l’annonce, photo de profil remplacée ou supprimée.
</details>

<details><summary>Pourquoi tester le type avec <code>finfo</code> et pas <code>$_FILES['photo']['type']</code> ?</summary>

`$_FILES['photo']['type']` est envoyé par le navigateur : l’utilisateur peut y mettre ce qu’il veut. `finfo` lit les premiers octets du fichier (sa « signature »), qui révèlent le vrai format.
</details>

<details><summary>Pourquoi un <code>.htaccess</code> dans <code>uploads/</code> si on vérifie déjà le type ?</summary>

Parce qu’aucune vérification n’est parfaite : un fichier peut être à la fois une image valide et contenir du code. Si un tel fichier passait, Apache refuserait quand même de l’exécuter. C’est une seconde barrière, indépendante de la première.
</details>

<details><summary>Pourquoi vos noms diffèrent-ils un peu du contrat du sujet ?</summary>

Le sujet est écrit pour le projet PetSitter du professeur (objets hydratés, routes `?action=`). Miaoumi a sa propre structure : routes `?page=`, modèles qui renvoient des tableaux, vues à plat dans `app/views/`. Nous avons gardé les noms du contrat (`PhotoUploader`, `enregistrer`, `supprimer`, `definirPhoto`, `photoUrl`, `Favori`, `existe`, `ajouter`, `retirer`, `parGardien`, `basculer_favori`, `mes_favoris`, `FavoriController`) en les adaptant : par exemple, `photoUrl($annonce)` reçoit l’annonce en paramètre.
</details>

### Modération (suspendre un utilisateur)

<details><summary>Le sujet parle de UserChecker et de security.yaml : où est-ce dans votre projet ?</summary>

Ce sont des outils de Symfony ; Miaoumi est en PHP sans framework, donc nous avons écrit l’équivalent nous-mêmes. Le champ `actif` est ajouté par la migration `005_moderation.sql` (booléen `TINYINT(1)`, 1 par défaut). Le rôle du UserChecker est joué par `AuthController::connexion()`, qui refuse un compte suspendu avec « Ce compte est suspendu ». La route `POST /admin/utilisateurs/{id}/suspendre` devient `POST ?page=suspendre-utilisateur` avec l’`id` et le jeton CSRF dans le formulaire.
</details>

<details><summary>Que se passe-t-il pour un utilisateur déjà connecté au moment de la suspension ? (point 5 du sujet)</summary>

Si on vérifiait seulement à la connexion, il resterait connecté tant que sa session dure : sa session ne contient que son id et son nom, elle ne sait pas qu’il a été suspendu. C’est le cas par défaut de Symfony avec un simple UserChecker. Nous avons donc ajouté `verifierCompteConnecte()`, appelée par `public/index.php` avant chaque page : elle relit `actif` en base, et si le compte est suspendu (ou supprimé), elle vide la session, régénère son identifiant et renvoie vers la connexion avec un message. Il est donc déconnecté **dès sa requête suivante**. Le coût : une petite requête par clé primaire à chaque page.
</details>

<details><summary>Pourquoi vérifier « suspendu » après le mot de passe, et pas avant ?</summary>

Si on affichait « Ce compte est suspendu » avant de vérifier le mot de passe, n’importe qui pourrait taper une adresse et savoir si elle a un compte sur le site, et s’il est suspendu. En vérifiant d’abord le mot de passe, seul le vrai titulaire voit le message ; les autres voient le message habituel « E-mail ou mot de passe incorrect ».
</details>

<details><summary>Comment empêchez-vous un utilisateur normal d’accéder à la modération ?</summary>

`AdminController::exigerAdmin()` est appelé au début des deux méthodes : visiteur → connexion ; connecté mais `role` différent de `admin` → **403** « Accès refusé ». Le rôle est relu en base à chaque page : si on retire le rôle admin à quelqu’un, il perd l’accès tout de suite, sans attendre une reconnexion. Le lien « Modération » n’est affiché qu’aux admins, mais ce n’est pas une sécurité : c’est le contrôleur qui protège.
</details>

<details><summary>Un administrateur peut-il se suspendre lui-même, ou suspendre un autre admin ?</summary>

Non : la requête est `UPDATE utilisateurs SET actif = ? WHERE id = ? AND role <> 'admin'`. La règle est dans le `WHERE`, comme pour les annonces : même un formulaire modifié à la main ne peut pas suspendre un administrateur. `rowCount()` vaut alors 0 et on affiche « Un compte administrateur ne peut pas être suspendu ». Cela évite aussi de se retrouver sans aucun admin.
</details>

<details><summary>Pourquoi suspendre plutôt que supprimer le compte ?</summary>

La suppression est irréversible et, avec `ON DELETE CASCADE`, effacerait ses annonces, candidatures et avis, donc aussi les notes des autres. La suspension est réversible (bouton « Réactiver ») et garde l’historique.
</details>

<details><summary>Comment devient-on administrateur ?</summary>

Il n’y a volontairement pas de page pour cela : un formulaire « devenir admin » serait une faille de plus. Le rôle se met directement en base, par exemple `UPDATE utilisateurs SET role = 'admin' WHERE email = '…';` dans phpMyAdmin. Pour la démo, le compte `admin@demo.miaoumi.fr` est créé par `donnees-demo.sql`. Grâce à `verifierCompteConnecte()`, le changement s’applique dès la page suivante.
</details>

<details><summary>Pourquoi un LEFT JOIN dans la liste de modération ?</summary>

`Utilisateur::tous()` fait `LEFT JOIN annonces … COUNT(a.id) … GROUP BY u.id` pour afficher le nombre d’annonces de chaque compte. Avec un `JOIN` simple, les comptes qui n’ont publié aucune annonce (l’admin, Bastien) disparaîtraient de la liste. `COUNT(a.id)` compte 0 pour eux, car il ignore les `NULL`.
</details>

### Interface et design

<details><summary>Comment avez-vous rendu le site agréable sur mobile ?</summary>

Avec des media queries CSS : sous 1 100 px, la bannière passe en une colonne et les cartes en deux colonnes ; sous 420 px, une seule colonne. La balise `<meta name="viewport">` du gabarit dit au téléphone d’utiliser sa vraie largeur. Nous avons vérifié avec des captures à 390 px de large.
</details>

<details><summary>Comment le cœur d’une carte sait-il s’il doit être rempli ?</summary>

`AnnonceController::index()` demande `Favori::idsParGardien()` pour l’utilisateur connecté ; la vue vérifie si l’identifiant de chaque annonce est dans cette liste (`in_array`) et ajoute la classe `actif`. Le cœur est un vrai formulaire POST avec le jeton CSRF vers `basculer_favori` ; pour un visiteur, c’est un lien vers la connexion, et il n’apparaît pas sur sa propre annonce.
</details>

<details><summary>Pourquoi des icônes SVG écrites dans la page plutôt qu’une bibliothèque ou Google Fonts ?</summary>

Pas de dépendance ni de requête vers un autre site : la page reste rapide, fonctionne hors ligne, et l’adresse IP des visiteurs n’est pas envoyée à un tiers (point RGPD). Les icônes prennent la couleur du texte (`currentColor`) et sont marquées `aria-hidden`, car elles sont décoratives.
</details>

<details><summary>Que s’affiche-t-il quand le propriétaire ne met pas de photo ?</summary>

L’illustration de son type d’animal (un chat, un chien, un oiseau… ou une patte pour les autres), dessinée en SVG. C’est un choix : une photo prise au hasard ferait croire que c’est le vrai animal de l’annonce. `cleAnimal()` transforme « Chats » en `chat` pour trouver le bon fichier. La photo de la bannière d’accueil est dans le domaine public (créditée dans `public/assets/animaux/CREDITS.md`).
</details>

<details><summary>Les statistiques de la bannière sont-elles écrites en dur ?</summary>

Non : `Annonce::statistiques()` compte les annonces ouvertes, terminées et les villes (`SUM`, `COUNT(DISTINCT …)`), et `Avis::statistiques()` calcule le nombre d’avis et la moyenne (`AVG`). Elles changent dès qu’une annonce ou un avis est ajouté.
</details>

### Git et travail d’équipe

<details><summary>Comment avez-vous travaillé à trois ?</summary>

Répartition par module, une branche `feature/prénom-fonctionnalité` par tâche, des pull requests vers `develop` relues et fusionnées par Idir, puis `develop` fusionnée dans `main`. Le CHANGELOG trace chaque changement avec la date et le prénom ; un hook Git protège `main` et `develop`.
</details>

<details><summary>Comment avez-vous testé ?</summary>

Quatre scripts PHP sur des tables MySQL temporaires (annonces, parcours, modification, favoris), les tableaux de tests du sujet rejoués sur le site, et des parcours complets dans le navigateur avec les comptes de démo.
</details>

---

## K. Les 11 phrases à placer

1. « Toutes les requêtes avec une donnée sont **préparées**. »
2. « Tout ce qui est affiché passe par **`e()`**, donc `htmlspecialchars`. »
3. « Chaque POST est protégé par un **jeton CSRF** vérifié avec `hash_equals`. »
4. « L’identité vient de la **session**, jamais du formulaire, et les droits sont **répétés dans le `WHERE`**. »
5. « On **valide dans le modèle** avant d’écrire : une donnée invalide ne crée aucune ligne. »
6. « Après un POST, **message flash et redirection 303** (Post/Redirect/Get). »
7. « Accepter une candidature se fait dans une **transaction** : tout ou rien. »
8. « Le type d’un fichier se lit dans son **contenu** (`finfo`), pas dans son nom, et le serveur choisit le **nom**. »
9. « Les favoris sont une relation **plusieurs-à-plusieurs** avec une **clé primaire composée**. »
10. « Une action qui modifie des données est en **POST seulement** : un lien ne peut rien casser (405). »
11. « Le compte et le rôle sont **relus en base à chaque page** : un compte suspendu est déconnecté tout de suite, pas à la fin de sa session. »
