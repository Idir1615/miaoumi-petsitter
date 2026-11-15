# Rapport — PetSitter : photos et favoris (projet Miaoumi)

Équipe : Idir, Ama, Maria — 15/11/2026

**Ce qui fonctionne.** Les trois lots sont intégrés et testés.
- **Lot A** : la classe `PhotoUploader` contrôle le code d’erreur de l’envoi, la taille (2 Mo), le type réel lu par `finfo` (liste blanche JPEG, PNG, WebP) et les dimensions (4 000 px). Elle enregistre le fichier sous un nom aléatoire de 32 caractères hexadécimaux avec l’extension du type détecté, et refuse sinon avec un message clair.
- **Lot B** : photo facultative à la publication, modification et suppression d’une annonce ouverte par son seul propriétaire (403 sinon). Le remplacement, la case « Supprimer la photo actuelle » et la suppression de l’annonce effacent l’ancien fichier.
- **Lot C** : table `favoris` à clé primaire composée, page « Mes favoris » (dernier ajouté en premier), bouton sur la fiche, règles de `basculer()` dans l’ordre (connexion, 405, 403, 404, pas sa propre annonce, retour limité à deux valeurs). En plus : « n gardien(s) intéressé(s) » pour le propriétaire et un `.htaccess` qui interdit d’exécuter des scripts dans `uploads/`.

Les tableaux de tests A, B et C ont été rejoués sur le site (31 contrôles réussis), ainsi que deux scripts de test PHP (`tests/modification.php`, `tests/favoris.php`). Le schéma s’importe dans une base neuve.

**Ce qui ne fonctionne pas, ou diffère du sujet.** L’archive `PetSitter_Fichiers_Test_Photos.zip` n’était pas à notre disposition : nous avons fabriqué des fichiers équivalents (images valides, JPEG renommé, script PHP renommé, GIF renommé, fichier trop lourd, image trop large). Le contrat est adapté à la structure de Miaoumi : routes `?page=` au lieu de `?action=`, modèles qui renvoient des tableaux (d’où `photoUrl($annonce)`), migration `004_favoris.sql`. Sans photo, l’image affichée dépend du type d’animal au lieu de `pas-de-photo.svg`. Le retrait des métadonnées (GPS) nécessite l’extension GD ; sans elle, la photo contrôlée est gardée telle quelle.

**Difficulté rencontrée.** Sur un poste, l’extension GD était désactivée : l’ancien code photo, qui recréait toujours l’image avec GD, plantait à chaque envoi. Nous l’avons découvert en testant sur une autre machine que celle du développement.

**Décision prise.** `PhotoUploader` suit le contrat (`move_uploaded_file`, extension du type détecté) et ne dépend plus de GD. Si GD est présente, l’image est ensuite recréée **dans le même format** pour supprimer ses métadonnées : nous gardons ainsi la protection RGPD déjà présente dans Miaoumi sans contredire les tests du lot A. Nous avons aussi décidé de n’autoriser la suppression d’une annonce que tant qu’elle est ouverte, pour ne pas effacer les avis d’une garde passée.

**Répartition.** Les lots ont été réalisés par Idir à l’approche de l’oral ; Ama et Maria révisent leur fonctionnement avec la fiche `docs/FICHE-REVISION.md`.
