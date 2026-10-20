# Vérification du projet — 19 octobre 2026

## Validation manuelle confirmée par le propriétaire

Après démarrage de son environnement et essai du site, le propriétaire a confirmé dans la conversation que la connexion et le parcours d’authentification fonctionnent sur sa base MySQL. Cette confirmation complète les tests automatisés isolés ; elle ne constitue pas un nouveau contrôle MySQL exécuté par l’assistant. Les résultats historiques ci-dessous décrivent l’environnement au moment des contrôles précédents.

## Mise à jour : authentification ajoutée

L’inscription, la connexion et la déconnexion sont maintenant implémentées. Lors de leur ajout, un test HTTP sur base temporaire isolée a vérifié : création du compte, hachage du mot de passe, validation, refus d’un mauvais mot de passe, renouvellement de session, échappement HTML, protections CSRF et déconnexion. La syntaxe de tous les fichiers PHP avait été validée.

Le script de test a depuis été retiré à la demande du propriétaire. Les vérifications actuelles se font avec PHP et dans le navigateur selon `DEMARRAGE-WINDOWS.md` ; les résultats ci-dessus sont historiques.

Ce test ne valide pas MySQL : le contrôle du doublon d’e-mail spécifique à MySQL et le parcours sur la base réelle restent à vérifier avec le guide `DEMARRAGE-WINDOWS.md`. Une configuration locale XAMPP standard a été préparée dans le fichier ignoré `config/config.php` ; adapter ses identifiants si nécessaire. MySQL ne répond toujours pas au contrôle local.

La liste ci-dessous conserve les résultats de l’audit initial, avant l’ajout de l’authentification.

Contrôles effectués sur le code disponible et à partir de la liste et du schéma fournis par le propriétaire. Une définition SQL ne prouve pas que la base a été créée sur un serveur.

| Critère | Résultat | Éléments vérifiés |
| --- | --- | --- |
| Base créée avec quatre tables | Définition conforme ; création réelle non vérifiée | Le fichier SQL définit utilisateurs, annonces, candidatures et avis. MySQL local ne répond pas. |
| Connexion Database sans erreur | Non vérifiée en exécution | PDO et pdo_mysql sont disponibles. Le fichier local config/config.php est absent et MySQL ne répond pas. |
| Routeur et liste des annonces | Vérification partielle réussie | Syntaxe correcte ; vue testée avec une liste vide et une annonce simulée ; route inconnue testée avec statut 404. La lecture réelle des annonces dépend de MySQL. |
| Inscription et connexion | Manquantes | Aucun formulaire ni traitement d’authentification n’est présent. |
| Répartition précise entre étudiants | Non documentée | Aucun nom ni attribution de fichiers n’a été fourni. |
| Dépôt Git et trois branches | Présents | main, develop et feature sont présents dans les références distantes après actualisation. |

## Correspondance avec le schéma fourni

Les quatre entités et leurs attributs figurent dans le SQL. Les six relations sont représentées par des clés étrangères obligatoires :

- annonces.id_proprietaire vers utilisateurs.id ;
- candidatures.id_gardien vers utilisateurs.id ;
- candidatures.id_annonce vers annonces.id ;
- avis.id_auteur et avis.id_cible vers utilisateurs.id ;
- avis.id_annonce vers annonces.id.

Ces clés représentent les cardinalités « un parent obligatoire pour chaque enfant » et « zéro à plusieurs enfants par parent ». Les suppressions en cascade suppriment les annonces, candidatures ou avis liés : ce comportement est prévu dans le SQL.

## Contrôles exécutés

- Analyse syntaxique PHP réussie sur les quatre fichiers PHP du projet avec le PHP de XAMPP.
- Rendu de la vue avec une liste vide et une annonce simulée : réussi.
- Échappement du titre et du type d’animal contenant du HTML : réussi.
- Route inconnue : texte attendu et code HTTP 404 vérifiés en exécution PHP CLI.
- Génération du journal Git : réussie ; dossier logs ignoré par Git et hook local configuré.
- Connexion MySQL locale : échec de connexion, serveur inaccessible (erreur 2002/10061). Aucune base n’a été importée ni modifiée pendant cet audit.

## Pour terminer la validation

1. Démarrer MySQL, créer la configuration locale et importer le SQL dans une base neuve.
2. Tester la connexion PDO et l’affichage des annonces avec cette base depuis le navigateur.
3. Développer puis tester l’inscription, la connexion et la déconnexion, avec mots de passe hachés, sessions et protections des formulaires.
4. Renseigner les noms des étudiants et les fichiers attribués, selon l’organisation réelle de l’équipe.

Le projet constitue un squelette cohérent, mais tous les critères de la liste ne sont pas encore remplis.
