# Journal des modifications

Les changements importants sont expliqués ici en français. « Non publié » correspond au travail qui n’a pas encore fait l’objet d’une version publiée.

## Non publié

### Authentification

- Ajout de l’inscription, de la connexion et de la déconnexion, avec validation des champs, requêtes préparées, mots de passe hachés et session renouvelée à la connexion.
- Protection des formulaires par jeton CSRF et déconnexion par POST.
- Navigation indiquant le nom de la personne connectée et message explicite si la base est indisponible.
- Ajout du guide XAMPP pour démarrer MySQL, importer la base et vérifier les comptes.

### Documentation et outils

- Rassemblement du projet et de sa documentation sur `main` à la demande du propriétaire.
- Ajout du rapport de vérification des critères fournis et des limites des tests locaux.

- Ajout d’un guide de démarrage, d’une présentation de la structure et d’un guide Git pour débuter.
- Ajout de consignes pour maintenir la documentation lors des prochaines modifications.
- Ajout d’un générateur d’historique Git local et d’un hook pour le lancer après chaque commit sur Windows.
- Conservation des fins de ligne Unix des hooks pour leur exécution après un clonage sous Windows.

## Base existante

- Squelette PHP avec connexion MySQL via PDO.
- Liste des annonces avec titre, type d’animal et dates de garde.
- Schéma SQL des utilisateurs, annonces, candidatures et avis.

Les dates et auteurs des commits sont disponibles dans l’historique Git.
