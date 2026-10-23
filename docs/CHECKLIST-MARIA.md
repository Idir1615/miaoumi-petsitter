# Checklist Maria — Avis et finition des comptes

Préparée le **23/10/2026 à 15:51 (Europe/Paris)** par **Idir avec l’assistant**, à partir de [REPARTITION-SEANCE-2.md](REPARTITION-SEANCE-2.md) et d’une relecture du code.

Cette liste indique le travail **à faire** ; elle ne signifie pas que Maria l’a réalisé. Pour cocher une case, ajouter le prénom, la date et la vérification faite.

## Déjà fait (ne pas refaire)

- Les vues `views/auth.php` et `views/avis.php` utilisent déjà `header.php` / `footer.php` et le CSS commun (Idir, 20/10/2026).
- `Avis::creer()` contrôle déjà la note, les participants et les doublons.

## À faire

### 1. Messages flash à la place des paramètres d’URL

- [ ] `controllers/AuthController.php` ligne 33 : remplacer la redirection `?page=connexion&inscrit=1` par `ajouterFlash('Compte créé, vous pouvez vous connecter.')` puis redirection 303 vers `?page=connexion`.
- [ ] `controllers/AvisController.php` ligne 40 : remplacer `&ajoute=1` par `ajouterFlash('Avis publié.')` puis redirection 303 vers la page des avis de l’annonce.
- [ ] Ajouter un message flash après la connexion et après la déconnexion.
- [ ] Supprimer dans les vues l’affichage basé sur `inscrit` / `ajoute`.
- [ ] Vérifier que le message n’apparaît qu’une fois (il disparaît au rafraîchissement).

### 2. Validation dans `Utilisateur::creer()`

- [ ] `models/Utilisateur.php` : valider nom, e-mail (format + non déjà utilisé) et mot de passe **dans le modèle**, avant l’insertion.
- [ ] Renvoyer les erreurs (par exemple un tableau) au lieu de `void`, comme `Annonce::creer()`.
- [ ] Adapter `AuthController` pour afficher ces erreurs de façon compréhensible.
- [ ] Vérifier qu’une donnée invalide ne crée **aucune ligne** en base.

### 3. Formulaire d’avis

- [ ] Conserver la personne évaluée sélectionnée après une erreur.
- [ ] Ne proposer que les personnes encore autorisées (`Avis::destinataires()`).
- [ ] Garder les règles : note de 1 à 5, commentaire facultatif de 2 000 caractères maximum, garde terminée, candidature `acceptee`, auteur différent de la cible, pas de doublon.

### 4. Tests à faire

- [ ] Liste d’avis vide, puis remplie.
- [ ] Mauvais mot de passe, e-mail déjà utilisé, champs vides : nom et e-mail restent remplis, le mot de passe non.
- [ ] Texte contenant `<b>`, `<script>`, `&` : affiché tel quel, sans être interprété.
- [ ] Avec Ama : candidature acceptée → garde passée → avis laissé par les deux participants.

### 5. Avant la pull request

- [ ] Vérifier la syntaxe : `php -l` sur chaque fichier modifié.
- [ ] Ajouter une entrée « Maria » datée (heure de Paris) dans le CHANGELOG.
- [ ] Préparer l’explication en une phrase de chaque méthode : `Utilisateur::creer()`, `Utilisateur::trouverParEmail()`, `Avis::pourAnnonce()`, `Avis::destinataires()`, `Avis::creer()`.
- [ ] Ouvrir la pull request vers `feature` (jamais directement vers `main`).
