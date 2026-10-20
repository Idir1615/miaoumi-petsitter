# Vérification du projet — 19 octobre 2026

## Partie Idir : annonces et socle commun — 20 octobre 2026 à 11:14

Le test `tests/annonces.php`, exécuté avec le PHP de XAMPP, passe sur des tables MySQL temporaires qui masquent les tables applicatives uniquement pour la connexion de test. Les données réelles ne sont pas modifiées.

Contrôles réussis : refus des champs vides ou trop longs, des dates impossibles, des débuts passés et des fins antérieures au début ; refus d’un propriétaire invalide ou inexistant ; aucune insertion après ces erreurs ; création valide avec statut ouverte ; propriétaire issu de la session malgré un identifiant envoyé dans le formulaire ; redirection 303 du visiteur et après publication ; erreur 422 et conservation de la saisie ; liste vide ; échappement du titre, de la description et du flash ; gabarit et CSS présents ; effacement du flash après lecture.

Les fichiers PHP applicatifs ont passé la vérification syntaxique. Les tests du contrôleur sont exécutés en PHP, pas depuis un navigateur. Le rendu responsive et le parcours complet dans le navigateur restent à vérifier manuellement. Auth et avis attendent encore le raccordement au gabarit par Maria ; candidatures attendent le travail d’Ama.

## Avis — 20 octobre 2026

Syntaxe PHP vérifiée pour les quatorze fichiers de l’application. MySQL/MariaDB est accessible lors de ce contrôle. Des tests avec des tables temporaires masquant les tables applicatives uniquement sur la connexion de test ont validé :

- autorisation du propriétaire et du gardien accepté, refus d’un tiers et d’un gardien en attente ;
- refus des gardes futures, de l’autoévaluation, d’une note hors limites et d’un commentaire trop long ;
- création des deux avis réciproques, refus d’un second avis identique et lecture de la liste ;
- échappement HTML du commentaire et des noms dans la vue.

Aucune donnée des tables réelles n’a été ajoutée ou modifiée. Le contrôle des doublons utilise une transaction et un verrou sur l’annonce ; aucun test de charge concurrente n’a été réalisé. Le parcours complet dans le navigateur reste à vérifier avec les comptes de l’équipe. Les règles de cette première version sont décrites dans le README.

## Réorganisation MVC — 20 octobre 2026

Sur la branche `feature`, les requêtes SQL ont été déplacées vers les modèles et les traitements vers les contrôleurs. La syntaxe des onze fichiers PHP concernés a été vérifiée avec PHP 8.2.12.

Un parcours HTTP avec une base SQLite temporaire a validé l’inscription, la validation des champs, le refus d’un mauvais mot de passe, la connexion, la liste vide, l’échappement du nom, le renouvellement du jeton CSRF, le refus d’un jeton invalide, la déconnexion et le statut 404. Cette vérification n’utilise aucun script Python et ne modifie pas la base MySQL. Le parcours sur MySQL après cette réorganisation reste à vérifier dans le navigateur.

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
