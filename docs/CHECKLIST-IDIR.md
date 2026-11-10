# Checklist Idir — Annonces (fin de séance 2)

Vérifiée le **26/10/2026 à 20:51 (Europe/Paris)** par **Idir avec l’assistant**, par relecture du code et `php -l` sur les fichiers du module (aucune erreur de syntaxe). Pas de nouveau parcours navigateur ni de lancement de `tests/annonces.php` pour cette vérification ; les anciens essais sont décrits dans [VERIFICATION.md](VERIFICATION.md).

| Critère | État | Ce que j'ai vu dans le code |
| --- | --- | --- |
| La méthode de création valide les données avant d'écrire en base | ✅ | Dans `models/Annonce.php`, `creer()` contrôle le titre, le type d'animal, les dates (format, pas dans le passé, fin après début), la description, la ville et le propriétaire. Elle renvoie les erreurs avant tout `INSERT`. |
| La liste s'affiche même quand elle est vide | ✅ | `views/annonces.php` affiche un message pour une liste vide, un autre pour une recherche sans résultat, et un bouton « Publier ma première annonce ». |
| Chaque action redirige avec un message flash | ✅ | Après une création réussie, `AnnonceController.php` fait `ajouterFlash()` puis une redirection 303. Un visiteur non connecté est redirigé vers la connexion avec un message d'erreur. |
| Le formulaire réaffiche les erreurs sans effacer la saisie | ✅ | En cas d'erreur, le contrôleur renvoie un code 422 et la vue réaffiche chaque champ (`value="<?= e($donnees[...]) ?>"`), avec son message d'erreur en dessous. |
| La vue échappe toutes les données affichées | ✅ | Les textes et les attributs passent par `e()`, qui appelle `htmlspecialchars`. Les identifiants et les compteurs sont convertis en nombres entiers, donc sans risque. |
| Le module utilise le header, le footer et les classes CSS communs | ✅ | `annonces.php` et `annonce-formulaire.php` chargent `header.php` et `footer.php`. |
| Le code est poussé sur la branche `feature/` du module | ❌ | Il n'existe pas de branche `feature/annonces`, seulement `feature`, `develop` et `main`. |
| Tu sais expliquer chaque méthode du modèle | ⚠️ À compléter | [REPARTITION-SEANCE-2.md](REPARTITION-SEANCE-2.md) explique `trouver()`, `toutes()` et `creer()`, mais il manque `rechercher()` et `villeDisponible()`. |

## Reste à faire

- [ ] Choisir avec Ama et Maria la convention de branche : Git refuse `feature/annonces` tant que la branche `feature` existe (renommer `feature`, ou utiliser `feature-annonces`).
- [ ] Ajouter l’explication de `rechercher()` et `villeDisponible()`.
