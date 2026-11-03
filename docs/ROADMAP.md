# Feuille de route — Miaoumi

Tâches en cours pour chaque membre. L’assistant identifie la personne (voir AGENTS.md), lui présente **sa** section et l’accompagne étape par étape. Une tâche n’est cochée qu’après vérification réelle, avec le prénom et la date.

Objectif actuel : **checklist de fin de séance 2**, puis l’oral (projet fonctionnel et maîtrise technique du code).

## Idir — Annonces et socle commun

- [x] Checklist de séance 2 complète pour le module Annonces.
- [x] Branche `feature/idir-annonces` publiée (03/11/2026).
- [ ] Réviser les sections 3 et 5 de docs/ORAL.md.
- [ ] Relire et fusionner les pull requests d’Ama et de Maria dans `develop`, puis `develop` dans `main`.

## Ama — Candidatures

1. [ ] **Créer la branche du module** depuis `develop` à jour, puis la publier :
   ```sh
   git fetch --prune
   git switch develop
   git pull --ff-only
   git switch -c feature/ama-candidatures
   git push -u origin feature/ama-candidatures
   ```
   Si Git signale que le nom `feature` est pris, supprimer l’ancienne branche locale `feature` après avoir vérifié qu’elle ne contient rien d’absent de GitHub. Activer le hook si `git config core.hooksPath` n’affiche pas `.githooks` : `powershell -NoProfile -ExecutionPolicy Bypass -File scripts/install-hooks.ps1`.
2. [ ] **Vérifier la checklist** sur le module, sans rien modifier si tout est conforme :
   - `Candidature::postuler()` et `decider()` valident avant d’écrire en base ;
   - la liste des candidatures s’affiche vide et remplie ;
   - postuler et décider redirigent avec un message flash ;
   - le message de candidature est conservé en cas d’erreur ;
   - toutes les données affichées passent par `e()` ;
   - header/footer et classes CSS communs.
   Corrections éventuelles limitées à `models/Candidature.php`, `controllers/CandidatureController.php`, `views/candidatures.php`, `views/candidature-formulaire.php`.
3. [ ] **Réviser** : expliquer à Ama chaque méthode de `models/Candidature.php` en une phrase (section 5 de docs/ORAL.md), puis l’interroger sur son code comme à l’oral et corriger ses réponses.

## Maria — Avis et comptes

1. [ ] **Mettre à jour sa branche** :
   ```sh
   git fetch --prune
   git switch feature/maria-avis-comptes
   git merge develop
   ```
   Même remarque qu’Ama pour l’ancienne branche locale `feature` et le hook. Vérifier que `git config user.name` est bien son nom, pour que ses commits lui soient attribués.
2. [ ] **Quatre corrections**, une par une, en expliquant chaque changement à Maria :
   - a) `Utilisateur::creer()` valide les données **avant** l’INSERT (nom 1 à 100 caractères, e-mail valide de 150 caractères maximum, mot de passe de 8 à 72 octets) et renvoie les erreurs, comme `Annonce::creer()` ; adapter `AuthController::inscription()`.
   - b) Messages flash : remplacer `?page=connexion&inscrit=1` et `&ajoute=1` par `ajouterFlash()` avant la redirection ; ajouter un message après connexion et après déconnexion ; retirer les tests `$_GET['inscrit']` et `$_GET['ajoute']` des vues.
   - c) `views/avis.php` : en cas d’erreur, la personne choisie dans la liste « cible » reste sélectionnée (valeur conservée par `AvisController`).
   - d) `views/avis.php` : erreurs affichées avec les classes communes `message message-erreur`.
   Fichiers autorisés : `models/Avis.php`, `models/Utilisateur.php`, `controllers/AvisController.php`, `controllers/AuthController.php`, `views/avis.php`, `views/auth.php`.
3. [ ] **Vérifier** : `php -l` sur chaque fichier modifié, puis tester dans le navigateur (XAMPP) l’inscription valide et invalide, la connexion, la déconnexion et le dépôt d’un avis. Indiquer honnêtement ce qui a été testé ou non.
4. [ ] **Publier** : entrée dans CHANGELOG.md (date, heure de Paris, prénom), un commit par correction à son nom, push de sa branche et pull request vers `develop`.
5. [ ] **Réviser** : expliquer à Maria chaque méthode de `models/Avis.php` et `models/Utilisateur.php` en une phrase (section 5 de docs/ORAL.md), puis l’interroger sur son code comme à l’oral et corriger ses réponses.

## Plus tard (checklist de remise finale)

Statut et clôture des annonces, note moyenne, jeu de données SQL, MLD et diagramme de classes : à répartir après la séance 2.
