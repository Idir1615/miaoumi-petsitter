# Feuille de route — Miaoumi

Tâches en cours pour chaque membre. L’assistant identifie la personne (voir AGENTS.md), lui présente **sa** section et l’accompagne étape par étape. Une tâche n’est cochée qu’après vérification réelle, avec le prénom et la date.

Objectif actuel : **checklist de fin de séance 2**, puis l’oral (projet fonctionnel et maîtrise technique du code).

## Idir — Annonces et socle commun

- [x] Checklist de séance 2 complète pour le module Annonces.
- [x] Branche `feature/idir-annonces` publiée (03/11/2026).
- [ ] Réviser **tout** docs/ORAL.md : les trois modules, pas seulement les annonces (voir « Révision commune »).
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
   Corrections éventuelles limitées à `app/models/Candidature.php`, `app/controllers/CandidatureController.php`, `app/views/candidatures.php`, `app/views/candidature-formulaire.php`.
3. [ ] **Réviser tout le projet** (voir « Révision commune ») : commencer par `app/models/Candidature.php`, puis les annonces, les avis et les comptes.

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
   - c) `app/views/avis.php` : en cas d’erreur, la personne choisie dans la liste « cible » reste sélectionnée (valeur conservée par `AvisController`).
   - d) `app/views/avis.php` : erreurs affichées avec les classes communes `message message-erreur`.
   Fichiers autorisés : `app/models/Avis.php`, `app/models/Utilisateur.php`, `app/controllers/AvisController.php`, `app/controllers/AuthController.php`, `app/views/avis.php`, `app/views/auth.php`.
3. [ ] **Vérifier** : `php -l` sur chaque fichier modifié, puis tester dans le navigateur (XAMPP) l’inscription valide et invalide, la connexion, la déconnexion et le dépôt d’un avis. Indiquer honnêtement ce qui a été testé ou non.
4. [ ] **Publier** : entrée dans CHANGELOG.md (date, heure de Paris, prénom), un commit par correction à son nom, push de sa branche et pull request vers `develop`.
5. [ ] **Réviser tout le projet** (voir « Révision commune ») : commencer par `app/models/Avis.php` et `app/models/Utilisateur.php`, puis les annonces et les candidatures.

## Remise finale — répartition

Répartition proposée par Idir le 03/11/2026, à partir de la checklist de remise finale et des « Finitions attendues avant la remise ». Chacun reste dans son module : la clôture touche les annonces (Idir), la note moyenne touche les avis (Maria), le passage en « pourvue » se déclenche à l’acceptation d’une candidature (Ama). **Les noms `cloturer()` et `noteMoyenne()` sont imposés par le professeur.**

Ordre conseillé : Idir livre d’abord `cloturer()` et `marquerPourvue()`, car Ama et Maria en dépendent.

### Idir — Annonces, documentation, intégration

1. [x] **`Annonce::cloturer(int $annonce, int $proprietaire)`** : seul le propriétaire clôt sa garde ; l’annonce passe en `terminee`. Bouton « Terminer la garde » dans Mes annonces, en POST avec CSRF, puis redirection avec le message flash « La garde est clôturée. ». Erreur claire si l’annonce n’est pas à lui ou déjà terminée. Tests dans `tests/annonces.php`.
2. [x] **`Annonce::marquerPourvue(int $annonce)`** : passe une annonce ouverte en `pourvue` ; méthode fournie à Ama.
3. [x] **Page introuvable** : la réponse 404 du routeur (`public/index.php`) s’affiche avec le gabarit commun, pas en texte brut.
4. [x] **Jeu de données SQL** : `sql/donnees-demo.sql` (4 annonces, 6 candidatures, 3 avis), sur `feature/idir-donnees-demo`, à fusionner.
5. [~] **Documentation** dans le README (MLD, diagramme de classes, logique métier et `cloturer()` rédigés le 03/11/2026 ; reste à intégrer les paragraphes d’Ama et de Maria) : MLD à jour, diagramme de classes (modèles et contrôleurs), logique métier, paragraphe expliquant `cloturer()`. Intégrer le paragraphe de Maria sur `noteMoyenne()`.
6. [ ] **Intégration** : relire et fusionner les pull requests dans `develop`, puis `develop` dans `main`, sans conflit résiduel.

### Ama — Candidatures

1. [ ] **Passer l’annonce en « pourvue »** : dans `Candidature::decider()`, quand une candidature est acceptée, appeler `Annonce::marquerPourvue()` (après la livraison d’Idir).
2. [ ] **Autres candidatures** : décider avec Idir si elles sont refusées automatiquement à l’acceptation, puis l’appliquer.
3. [ ] **Messages d’erreur clairs** : candidature en double, annonce déjà pourvue ou terminée, décision par un tiers ; jamais de page blanche.
4. [ ] **Tests** des candidatures (doublon, propre annonce, décision par un tiers), sur le modèle de `tests/annonces.php`.
5. [ ] **Paragraphe** pour la documentation : règles de candidature et d’acceptation, à transmettre à Idir.

### Maria — Avis et comptes

1. [ ] **`Avis::noteMoyenne(int $utilisateur)`** : moyenne des notes reçues (`AVG`), `null` s’il n’y a aucun avis ; affichée avec une décimale, ou « Pas encore d’avis » quand elle est absente.
2. [ ] **Confirmations** : messages flash après inscription, connexion, déconnexion et dépôt d’avis (remplace `inscrit=1` et `ajoute=1`) — voir aussi la section séance 2.
3. [ ] **Messages d’erreur** : « Annonce introuvable » sur la page des avis affiché avec le gabarit commun ; classes `message message-erreur` dans `avis.php`.
4. [ ] **Avis après clôture** : vérifier que le dépôt d’avis fonctionne sur une annonce clôturée par `cloturer()` (après la livraison d’Idir).
5. [ ] **Paragraphe** expliquant `noteMoyenne()` pour la documentation, à transmettre à Idir.
6. [ ] **Historique Git** : commits à son nom sur `feature/maria-avis-comptes`.

### Révision commune — tout le monde sait tout

Le professeur peut interroger n’importe qui sur n’importe quelle partie. **Idir, Ama et Maria doivent chacun savoir expliquer les trois modules.** Pour chaque personne, l’assistant :

- explique chaque méthode des **quatre** modèles en une phrase (`Annonce`, `Candidature`, `Avis`, `Utilisateur`, section 5 de docs/ORAL.md) ;
- fait raconter les parcours de la section 3 (publier une annonce, clôturer une garde) et un parcours de candidature et d’avis ;
- fait expliquer la sécurité (section 6), la base de données (section 4) et l’organisation Git (section 8) ;
- pose les questions de la section 10, en insistant sur les modules que la personne n’a **pas** codés, puis corrige ses réponses.

- [ ] Idir sait expliquer les candidatures, les avis et les comptes.
- [ ] Ama sait expliquer les annonces, les avis et les comptes.
- [ ] Maria sait expliquer les annonces et les candidatures.

### Équipe

- [ ] Parcours complet sans erreur sur XAMPP avec `sql/donnees-demo.sql` : publier → candidater → valider → clôturer → noter.
- [ ] Oral blanc à trois avec les questions de docs/ORAL.md.
