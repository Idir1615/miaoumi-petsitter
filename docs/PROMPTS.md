# Historique des demandes — Miaoumi

Document préparé le 20/10/2026 à 09:39, heure de Paris, pour partager le contexte du travail avec l’équipe.

Les demandes textuelles de l’utilisateur sont reprises ci-dessous dans leur ordre d’apparition, avec leur formulation d’origine. Les notes « Contexte » sont des résumés ajoutés pour comprendre les échanges ; elles ne reproduisent pas toutes les réponses de l’assistant. Les captures sont décrites, sans recopier les chemins personnels, les données de configuration ou le contexte technique fourni automatiquement par l’éditeur. Les heures individuelles des messages ne sont pas disponibles.

Ce document est un historique, pas une liste de commandes à exécuter : certaines demandes anciennes ont été remplacées par des consignes plus récentes. Il ne se met pas à jour automatiquement.

## 1. Découverte du projet

1. « salut j'ai un projet miaoumi tu peux y acceder ? »
2. « j'ai des chose dans la branche feature »
3. « dans develop pardon »
4. « apprend de tout les fichiers stp »

Contexte : le projet initial a été trouvé sur `develop`. Lecture des sept fichiers disponibles : README, configuration exemple, connexion PDO, routeur, vue des annonces, SQL et gitignore.

## 2. Documentation et historique

5. « tu peux me faire des fichiers logs je m'y connais pas trop en git fait moi des ficihers propres comme sa : avec un readme etc tu vois ce que je veux dire des qu'on modifie qlq chose sa genere des logs un readme du projet etc »

Contexte : capture d’un README de référence jointe. Ajout du README détaillé, du CHANGELOG, du guide Git, des consignes de travail et d’un générateur d’historique après chaque commit. L’historique automatique suit les commits, pas chaque sauvegarde.

6. « met tout sur le main et verifie que tout est bien fait : »

Contexte : captures d’une liste de critères et du schéma utilisateurs / annonces / candidatures / avis. Vérification du code et des relations SQL. L’authentification manquait alors.

## 3. Inscription, connexion et base de données

7. « fait moi l'inscription et la connnexion de facon simple pour cocher la case , j'ai pas compris le probleme avec la connexion reele a la base non valide mysql je dois faire quoi »
8. « si les tabls sont crée etc la bdd est deja la »
9. « ta rien mis dans le main »
10. « bien sur ta tout le controle »

Contexte : ajout de l’inscription, connexion et déconnexion, avec hachage des mots de passe, sessions, requêtes préparées et jetons CSRF. Guide XAMPP ajouté. Publication sur `main` après autorisation. La configuration locale privée n’est pas versionnée.

11. « ecoute c vrm pas mal sa »
12. « plutot pas mal merci mon pote ta fais du tres bon travail on a tous la nn ? »
13. « TOUT FONCTIONNE LA CONNEXION ETC MERCI MON POTE »

Contexte : confirmation par l’utilisateur du fonctionnement de l’authentification sur sa base MySQL.

## 4. Présentation du dépôt et logo

14. « Mon git est nickel si tu peux le rendre meilleur rend le meilleur on peut mettre un logo ? »
15. « fait le par rapport a notre logo »

Contexte : l’équipe a fourni son logo noir et blanc avec un chien, un chat et le nom MIAOUMI. Il remplace la proposition de logo générée précédemment. Le README et la navigation utilisent le fichier fourni sans retouche. Ajout également d’un guide de contribution et de modèles GitHub pour les bugs, propositions et pull requests.

16. « je vois rien »

Contexte : capture de l’ancien README sur GitHub. Les modifications étaient encore locales ; elles ont ensuite été publiées sur `main`.

## 5. Nouvelle règle sur les branches

17. « quand je travail avec toi sur miaoumi ne met jamais les modificiations sur le main »
18. « y a deja 2 branches features et develop »

Contexte : utiliser la branche existante `feature` pour les modifications et `develop` pour les regrouper. Ne pas modifier `main` sans demande explicite. Cette règle remplace les autorisations générales précédentes concernant `main`.

## 6. Questions sur Codex et l’abonnement

19. « question es ce que j'ai la version pro la ? »
20. « ah merde comment e sais que c'est bientot fini ? »
21. « j'ia la version plus cbon ? »
22. « sa reset tout les combien de temps ? »
23. « la version standard sufiit aussi ? »
24. « gratuit »

Contexte : échanges sur l’abonnement, les limites et l’accès à Codex. Aucun changement du projet. Les offres pouvant évoluer, se référer à la documentation officielle actuelle plutôt qu’à cet historique.

## 7. Simplification de l’index et retrait du test Python

25. « bon passant sur mon index.php il y a pas plus simple a faire repond moi juste ne fais pas de modif »
26. « tu pense cbien a faire ? »
27. « je suis obliger d'utiliser py ? »
28. « enleve moi ce fichier du git etc »
29. « enleve le de main »

Contexte : Python servait uniquement à un test automatisé, pas au fonctionnement du site. Suppression du test Python sur `feature`, puis sur `main` à la demande explicite de l’utilisateur. Documentation remplacée par les vérifications PHP et les essais dans le navigateur. Retour sur `feature` ensuite.

## 8. Passage au MVC

30. « qst es ce que on suit la methode mvc ? »
31. « je veux un truc clair en mvc stp »
32. « sur le changelog ta oublier de mettre les dates et heures de modifications stp »
33. « ta mis sur feature ce qu'on a fait la ? »
34. « et les changements qu'on a fait la ? »
35. Message contenant uniquement une capture de la branche `feature` sur GitHub, encore à l’ancien commit supprimant Python.
36. « oui »

Contexte : le « oui » autorisait l’enregistrement et l’envoi des changements sur `feature`. Séparation en modèles, contrôleurs et vues ; `index.php` devient le routeur et `core/bootstrap.php` prépare les sessions et les protections communes. Ajout des dates et heures de Paris au CHANGELOG et de la règle correspondante dans AGENTS.md. Publication du MVC sur `feature`.

37. « si mes amis modifies des truc sur mes features tu pourras directement faire le changelog ? »

Contexte : l’assistant peut lire les commits et différences puis compléter le CHANGELOG sur demande. Il n’agit pas automatiquement en arrière-plan.

## 9. Développement des avis

38. « et qst on a oublier avis »
39. « go on commence mtn »
40. « met le sur feature »

Contexte : la table avis existait déjà. Ajout de `models/Avis.php`, `controllers/AvisController.php` et `views/avis.php`, puis publication sur `feature`. Première règle retenue : avis réciproques entre propriétaire et gardien dont la candidature est `acceptee`, après la date de fin, avec note de 1 à 5, commentaire facultatif et refus des doublons. La gestion des candidatures et la confirmation réelle de fin de garde restent à développer. Tests réalisés avec des tables MySQL temporaires sans modifier les données applicatives.

## 10. Installation de Codex chez un collègue

41. « je telecharge codex sur l'autre pc du collegue sa fait installing a l'infini c normal ? »
42. « extension vs »

Contexte : demande d’aide pour une extension VS Code bloquée sur « Installing ». Conseils de relance et de consultation du journal d’erreurs ; résolution non confirmée dans cette conversation.

## 11. Partage des prompts

43. « tu peux mettre tout un fichier pour que collegeues puisse voir nos promptes met tout dedans »

Contexte : création du présent fichier pour que l’équipe puisse comprendre les demandes, les décisions et leur ordre. Pour les détails des changements de code, consulter [le CHANGELOG](../CHANGELOG.md), [le rapport de vérification](VERIFICATION.md) et l’historique Git.

## 12. Candidatures et intégration des changements récents

44. « je dois faire ceci maintenant creee moi les fichiers adaptee et les codes » (captures de la répartition et de la checklist Candidatures)
45. « ok mon collaborateur a mis a jour le code pour ajouter les annonces et tout mets moi aussi le code mis a jour depuis git pour que je puisse voir mes modifs »
46. « config.exemple pour lui c est la meme chose que comfig.php pour moi , il y a eu d autres changement dans le main met les moi a jour ici »
47. « est ce que tu les a fait si oui pourquoi je ne vois pas comment faire poure candidater »
48. « est ce que j ai une autre solution pour creer une annonce a partir d un autre compte si ca ne marche pas »
49. « ok top met tout ca dans le main maintenant »
50. « met le sur git sur le main »
51. « ta oublier de faire ce que Idir a dit de faire de modifer les changelogs etc »

Contexte : les fonctions de candidature ont été intégrées sur `main` : dépôt d’une candidature, consultation des demandes envoyées et reçues, puis acceptation ou refus par le propriétaire. Un lien visible mène au formulaire ; une personne déconnectée retrouve l’annonce après connexion. L’utilisateur a aussi testé son environnement avec deux comptes et une annonce d’essai. L’intégration a été publiée après sa demande. Le logo fourni par l’équipe a remplacé l’icône de navigation. Le CHANGELOG, le README, ce fichier de suivi et le rapport de vérification décrivent le travail ; les scénarios MySQL automatisés n’ont pas été rejoués pendant cette reprise.

## 13. Restructuration, MCD et ménage des branches (Idir, 26/10 → 07/11/2026)

52. « nickel je suis idir maintenat je veux que tu check ce qui est deja fais dans cette image » (capture de la checklist de fin de séance 2)
53. « ce tableau la tu peux stp le mettre dans git dans la checklist stp »
54. « fait moi un pdf pr l'instant de ce uqej e dos connaitre »
55. « on a besoin de reprendre le travail en main, j'aime bien tout mon site exiterais t'il pas une facon plus simple pr le code pr qu'on puisse le comprendre si non le fait pas, apres revoit la strucures des ficheirs » (arborescence montrée par le professeur : config/, app/models, app/controllers, app/views, public/index.php, sql/)
56. « fait moi un rendu visuel en local comment je fais sur xampp »
57. « c'est la version la plus a jour du git ? »
58. « faut que tu me dit si le mcd est bon ? » (capture du MCD Looping et du logo)
59. « recuperer sa et modifie le mcd fait par l'ia de git par celui la » (nouvelle image du MCD avec `ville`)
60. « j'ai vu il reste beaucoup branche »
61. « je veux que tu verifie bien pour pas qu'il y 'est d'erreur, commit et pousse feature/idir-structure fusionne/idir-logo et feature/idirstructure dans develop oui je donne mon accord supprime les branches inutiles et journalise tout ce qu'on a fait »

Contexte : la checklist d’Idir (docs/CHECKLIST-IDIR.md) a été rédigée sur la branche de Maria, toujours présente sur `feature/maria-avis-comptes`. Le code n’a pas été simplifié : l’assistant a jugé qu’il ne pouvait pas l’être sans perdre de fonctionnalités. Une première restructuration, faite le 27/10 sur une ancienne version, a été abandonnée et refaite le 07/11 sur `develop` à jour (`feature/idir-structure`). Le MCD de l’équipe a été validé par rapport au schéma SQL ; seul l’attribut `ville` manquait, et Idir a fourni une nouvelle image. Après vérification (`php -l`, pages dans XAMPP, `tests/annonces.php`), `feature/idir-logo` puis `feature/idir-structure` ont été fusionnées dans `develop`, et les branches déjà fusionnées ont été supprimées. Une fiche PDF de révision a été préparée mais n’est pas versionnée : elle cite l’ancienne structure.

## Consignes actuelles pour reprendre le travail

- Suivre le circuit `feature/prénom-fonctionnalité` → `develop` → `main` décrit dans AGENTS.md ; seul Idir fusionne dans `develop` et `main`.
- Garder la structure `app/` (core, models, controllers, views), `config/`, `public/`, `sql/` et l’organisation MVC et les protections de l’authentification et des formulaires.
- Ajouter une entrée datée et horodatée en heure de Paris dans le CHANGELOG pour chaque changement significatif.
- Mettre à jour le README lorsque le fonctionnement ou la structure évolue.
- Ne pas créer de commit ni publier sans demande de l’utilisateur.
- Ne pas réintroduire le test Python supprimé.
- Utiliser le logo noir et blanc fourni par l’équipe.
- Ne pas versionner de configuration privée, de mots de passe ou de données de comptes réels.
