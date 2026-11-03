# Préparer l’oral — Miaoumi

Support de révision pour Idir, Ama et Maria. Chacun doit pouvoir expliquer **tout le projet**, pas seulement sa partie. Ce document décrit le code tel qu’il est sur `develop` ; il ne présente pas comme faites des fonctionnalités qui n’existent pas.

## 1. Le projet en 30 secondes

Miaoumi met en relation des **propriétaires d’animaux** et des **gardiens**.

1. Un propriétaire publie une **annonce** de garde (animal, ville, dates).
2. Un gardien **postule** avec un message.
3. Le propriétaire **accepte ou refuse** la candidature.
4. Une fois la garde terminée, le propriétaire et le gardien accepté peuvent se laisser un **avis** (note de 1 à 5).

Technologies : **PHP** sans framework, **MySQL** avec **PDO**, HTML/CSS, architecture **MVC**, travail en équipe avec **Git/GitHub**.

## 2. Qui a fait quoi

| Personne | Partie | Fichiers principaux |
| --- | --- | --- |
| **Idir** | Annonces, socle commun (header/footer, CSS, messages flash), adaptation à la maquette, organisation Git | `models/Annonce.php`, `controllers/AnnonceController.php`, `views/annonces.php`, `views/annonce-formulaire.php`, `views/header.php`, `views/footer.php`, `core/flash.php`, `public/assets/style.css` |
| **Ama** | Candidatures : postuler, consulter, accepter/refuser | `models/Candidature.php`, `controllers/CandidatureController.php`, `views/candidatures.php`, `views/candidature-formulaire.php` |
| **Maria** | Avis et comptes (inscription, connexion) | `models/Avis.php`, `controllers/AvisController.php`, `views/avis.php`, `models/Utilisateur.php`, `controllers/AuthController.php`, `views/auth.php` |

## 3. L’architecture MVC

```
Navigateur ──► public/index.php (routeur)
                  │  ?page=creer-annonce
                  ▼
             Contrôleur  ──► Modèle ──► MySQL (PDO)
                  │             ◄── données / erreurs
                  ▼
                 Vue (HTML, données échappées avec e())
```

- **Modèle** (`models/`) : uniquement les requêtes SQL et les règles métier (validation avant d’écrire en base).
- **Vue** (`views/`) : uniquement l’affichage HTML. Aucune requête SQL dans les vues.
- **Contrôleur** (`controllers/`) : lit la requête (`$_GET`, `$_POST`, session), appelle le modèle, puis affiche une vue ou redirige.
- **`public/index.php`** est le **seul point d’entrée**. Un tableau `$routes` associe chaque `?page=…` à une méthode de contrôleur. Une page inconnue renvoie **404** ; une base indisponible affiche la page `indisponible.php` avec un code **503**.
- **`core/bootstrap.php`** est chargé par chaque page : il démarre la session, charge les classes, définit `e()` et vérifie le jeton CSRF.
- **`core/Database.php`** fournit une **connexion PDO unique** (patron singleton), configurée à partir de `config/config.php` (fichier local, ignoré par Git car il contient le mot de passe).

### Exemple à savoir raconter : publier une annonce

1. Le formulaire envoie un **POST** vers `?page=creer-annonce` avec le jeton `csrf`.
2. `bootstrap.php` vérifie le jeton ; s’il est faux → **403**.
3. `AnnonceController::creer()` vérifie que l’utilisateur est connecté, sinon message flash + redirection vers la connexion.
4. Il appelle `Annonce::creer()`, qui **valide** titre, animal, dates, description et ville, puis fait l’`INSERT` avec une **requête préparée**. Le propriétaire vient de la **session**, jamais du formulaire.
5. S’il y a des erreurs : code **422**, le formulaire est réaffiché **avec la saisie conservée** et les erreurs par champ.
6. Sinon : message flash « Ton annonce a été publiée. » puis **redirection 303** vers la liste (patron **Post/Redirect/Get** : recharger la page ne republie pas l’annonce).

### Exemple à savoir raconter : clôturer une garde

1. Dans Mes annonces, le propriétaire clique sur « Terminer la garde » : un encadré demande de confirmer (pas de clôture sur un simple clic).
2. « Confirmer la clôture » envoie un **POST** vers `?page=cloturer-annonce` avec l’identifiant de l’annonce et le jeton `csrf`.
3. `AnnonceController::cloturer()` refuse un GET (**405**) et un visiteur non connecté (redirection vers la connexion).
4. `Annonce::cloturer()` vérifie l’annonce, le propriétaire et le statut, puis fait `UPDATE annonces SET statut = 'terminee' WHERE id = ? AND id_proprietaire = ? AND statut <> 'terminee'`. Les conditions sont **répétées dans le WHERE** : même si deux envois arrivent en même temps, un seul peut modifier la ligne.
5. Redirection **303** vers Mes annonces avec « La garde est clôturée. » ou le message d’erreur (« Seul le propriétaire peut clôturer cette garde. »).

## 4. La base de données

Quatre tables (fichier `Miaoumi (2).sql`) :

| Table | Rôle | Clés étrangères |
| --- | --- | --- |
| `utilisateurs` | Comptes : nom, e-mail **unique**, mot de passe **haché**, rôle | — |
| `annonces` | Gardes proposées : titre, animal, ville, dates, description, statut (`ouverte`, `pourvue`, `terminee`) | `id_proprietaire` → utilisateurs |
| `candidatures` | Un gardien postule à une annonce : message, statut (`en_attente`, `acceptee`, `refusee`), date | `id_annonce` → annonces, `id_gardien` → utilisateurs |
| `avis` | Note 1–5 et commentaire laissés après une garde | `id_annonce`, `id_auteur`, `id_cible` |

- Un utilisateur n’a pas de rôle fixe : **le même compte peut être propriétaire et gardien**.
- `ON DELETE CASCADE` : supprimer un utilisateur supprime ses annonces, candidatures et avis.
- `migrations/001_ajout_ville.sql` ajoute la colonne `ville` aux bases créées avant son ajout. Le code vérifie si la colonne existe (`villeDisponible()`) pour rester compatible.

## 5. Les méthodes des modèles en une phrase

### Annonce (Idir)
- `rechercher()` : liste les annonces filtrées par texte, animal et ville ; les annonces ouvertes pour tout le monde, ou toutes celles du propriétaire connecté pour « Mes annonces ».
- `creer()` : valide les champs et les dates (début aujourd’hui ou plus tard, fin après le début), vérifie que le compte existe, puis enregistre l’annonce ; renvoie la liste des erreurs, vide si tout s’est bien passé.
- `trouver()` : renvoie une annonce par son identifiant, ou `null`.
- `toutes()` : renvoie toutes les annonces, de la plus récente à la plus ancienne.
- `cloturer()` : termine une garde après avoir vérifié qu’elle existe, que l’utilisateur connecté en est le propriétaire et qu’elle n’est pas déjà terminée ; renvoie les erreurs, vide si la garde est clôturée.
- `marquerPourvue()` : passe une annonce `ouverte` en `pourvue` ; elle est appelée quand une candidature est acceptée.
- `villeDisponible()` : indique si la colonne `ville` existe dans la base.

### Candidature (Ama)
- `pourUtilisateur()` : liste les candidatures envoyées par l’utilisateur **et** celles reçues sur ses annonces.
- `postuler()` : dans une **transaction**, vérifie que l’annonce est ouverte et non expirée, que ce n’est pas sa propre annonce et qu’il n’a pas déjà postulé, puis crée la candidature `en_attente`.
- `decider()` : dans une transaction, vérifie que l’utilisateur est bien le propriétaire et que la candidature est encore en attente, puis la passe à `acceptee` ou `refusee`.

### Avis (Maria)
- `pourAnnonce()` : liste les avis d’une annonce avec le nom de l’auteur et de la personne notée.
- `destinataires()` : renvoie les personnes que l’utilisateur a le droit de noter (garde terminée, candidature acceptée, pas d’avis déjà déposé).
- `creer()` : dans une transaction, revérifie la note, le commentaire et le droit de noter cette personne, puis enregistre l’avis.

### Utilisateur (Maria)
- `creer()` : enregistre un compte avec le mot de passe haché par `password_hash()`.
- `trouverParEmail()` : renvoie le compte correspondant à un e-mail pour la connexion.

## 6. La sécurité (questions très probables)

| Risque | Protection dans Miaoumi | Où |
| --- | --- | --- |
| **Injection SQL** | Toutes les requêtes avec des données utilisent des **requêtes préparées** (`prepare` + `execute` avec `?`) | tous les modèles |
| **XSS** (script injecté dans une page) | Tout texte affiché passe par `e()` = `htmlspecialchars()` | `core/bootstrap.php`, vues |
| **CSRF** (formulaire envoyé depuis un autre site) | Un jeton aléatoire en session, caché dans chaque formulaire, comparé avec `hash_equals()` à chaque POST | `core/bootstrap.php` |
| **Mots de passe volés** | Hachage avec `password_hash()` / `password_verify()`, jamais stockés en clair, jamais réaffichés | `Utilisateur`, `AuthController` |
| **Vol de session** | `session_regenerate_id()` à la connexion et à la déconnexion, cookie `HttpOnly` et `SameSite=Lax` | `AuthController`, `bootstrap.php` |
| **Accès non autorisé** | Le propriétaire et l’auteur viennent de la **session**, pas du formulaire ; chaque action revérifie les droits dans le modèle | contrôleurs et modèles |
| **Doublons simultanés** | Transaction + `SELECT … FOR UPDATE` qui verrouille l’annonce pendant la vérification | `Candidature::postuler()`, `Avis::creer()` |
| **Déconnexion forcée par un lien** | La déconnexion n’accepte que **POST** (sinon 405) | `AuthController::deconnexion()` |
| **Mot de passe dans Git** | `config/config.php` est dans `.gitignore` ; seul `config.example.php` est versionné | `.gitignore` |

## 7. Notions à savoir expliquer

- **Message flash** : message stocké en session avant une redirection, affiché une seule fois par le header puis effacé (`ajouterFlash()` / `lireFlash()`).
- **Codes HTTP utilisés** : 303 (redirection après POST), 403 (interdit / jeton invalide), 404 (introuvable), 405 (mauvaise méthode), 409 (conflit, candidature en double), 422 (formulaire invalide), 503 (base indisponible).
- **Pagination** : 6 annonces par page, calculée dans le contrôleur avec `array_slice()`.
- **Transaction** : groupe de requêtes validé en entier (`commit`) ou annulé en entier (`rollBack`).
- **Conserver la saisie** : en cas d’erreur, les champs sont réaffichés (échappés) ; le mot de passe ne l’est jamais.

## 8. Git et travail d’équipe

- Circuit : `feature/prénom-fonctionnalité` → `develop` → `main`.
- Chacun crée sa branche depuis `develop`, puis ouvre une **pull request** vers `develop` ; Idir relit et fusionne, puis fusionne `develop` dans `main`.
- Un hook `pre-push` refuse localement les envois sur `main`/`develop` par une autre personne qu’Idir et les noms de branches non conformes ; une GitHub Action vérifie les pull requests.
- Le dépôt est privé sur un compte gratuit : GitHub n’y applique pas les règles de protection de branche, le respect du circuit repose donc sur l’équipe.
- `CHANGELOG.md` explique chaque changement avec la date, l’heure et le prénom ; `logs/git-history.md` est généré automatiquement après chaque commit.

## 9. Ce qui n’est pas fait (à dire honnêtement si on le demande)

- **Passage en « pourvue »** : `Annonce::marquerPourvue()` existe, mais son appel à l’acceptation d’une candidature dans `decider()` est à brancher par Ama.
- **Avis après clôture** : clôturer une garde avant sa date de fin ne permet pas encore de laisser un avis, car la règle des avis exige que la date de fin soit passée (choix à faire par Maria).
- Une annonce n’est pas clôturée automatiquement à sa date de fin : c’est le propriétaire qui la clôture.
- **Plusieurs candidatures peuvent être acceptées** pour une même annonce (choix assumé dans le code).
- **Validation des comptes** : elle se fait dans `AuthController`, pas encore dans `Utilisateur::creer()` (tâche de Maria).
- **Messages de succès** après inscription et dépôt d’avis : affichés via un paramètre d’URL (`inscrit=1`, `ajoute=1`) et non par le mécanisme flash commun (tâche de Maria).
- Pas de modification ni de suppression d’annonce, pas de profil gardien ni de classement des gardiens (présents dans la maquette seulement).
- **Tests** : un script de test PHP existe pour les annonces (`tests/annonces.php`) ; candidatures, avis et comptes ont été vérifiés manuellement ou pas encore (voir `docs/VERIFICATION.md`).

## 10. Questions possibles du jury

**Pourquoi MVC ?** Pour séparer les responsabilités : on peut modifier l’affichage sans toucher au SQL, et chacun a pu travailler sur son module sans gêner les autres.

**Pourquoi PDO et pas `mysqli` ?** PDO gère les requêtes préparées simplement, lève des exceptions en cas d’erreur (`ERRMODE_EXCEPTION`) et fonctionne avec plusieurs bases.

**Où est validé un formulaire ?** Dans le contrôleur pour la forme (types, champs présents) et surtout dans le **modèle** avant l’insertion : une donnée invalide ne crée jamais de ligne.

**Que se passe-t-il si quelqu’un modifie l’identifiant du propriétaire dans le formulaire ?** Rien : le propriétaire est lu dans la session, pas dans le formulaire.

**Comment empêcher quelqu’un de postuler deux fois ?** `postuler()` verrouille l’annonce dans une transaction, vérifie qu’aucune candidature n’existe déjà pour ce gardien, puis insère.

**Qui peut laisser un avis ?** Seulement le propriétaire et un gardien dont la candidature a été acceptée, après la date de fin, une seule fois par personne notée.

**Pourquoi rediriger après un POST ?** Pour éviter qu’un rafraîchissement de la page renvoie le formulaire (patron Post/Redirect/Get) et pour afficher le message flash.

**Pourquoi `htmlspecialchars` à l’affichage et pas à l’enregistrement ?** On stocke la donnée telle quelle et on l’échappe selon l’endroit où on l’affiche ; cela évite de l’abîmer en base.

**Comment avez-vous travaillé en équipe ?** Répartition par module (`docs/REPARTITION-SEANCE-2.md`), une branche par fonctionnalité, des pull requests validées par Idir, un CHANGELOG commun.

**Pourquoi demander une confirmation avant de clôturer ?** La clôture est définitive : l’encadré de confirmation évite une erreur de clic. Il est fait en HTML (`<details>`), donc il fonctionne sans JavaScript ; la sécurité reste vérifiée côté serveur.

**Que voit-on si on tape une page qui n’existe pas ?** Le routeur renvoie un code 404 et affiche `views/introuvable.php` avec le gabarit du site, jamais une page blanche.

**Comment montrer le site avec des données ?** `donnees-demo.sql` crée 4 comptes fictifs, 4 annonces, 6 candidatures et 3 avis, avec des dates calculées depuis le jour de l’import ; il peut être réimporté pour remettre la démonstration à zéro.

## 11. Avant mardi

- [ ] Chacun lit ce document et le code de **toutes** les parties, pas seulement la sienne.
- [ ] Chacun sait raconter le parcours « publier une annonce » (section 3) et un parcours de sa partie de la même façon.
- [ ] Faire une démonstration complète au moins une fois sur XAMPP : inscription, connexion, annonce, candidature, acceptation, avis.
- [x] Comptes et données de démonstration : `donnees-demo.sql`, comptes dans la section « Comptes de démonstration » du README (mot de passe `demo1234`).
- [ ] S’interroger mutuellement avec les questions de la section 10.
