# Fiche fonctions — les modèles expliqués simplement

Les fonctions des modèles de Miaoumi, avec des mots simples, puis les questions que le professeur peut poser. Version détaillée : partie E de [FICHE-REVISION.md](FICHE-REVISION.md).

---

## D’abord, 6 mots à comprendre

| Mot | En simple |
| --- | --- |
| **Requête préparée (`?`)** | On écrit la question à la base avec des trous `?`, et on remplit les trous à part. Personne ne peut glisser une commande pirate. |
| **`WHERE`** | Le filtre : « seulement les lignes où… ». |
| **`JOIN`** | Coller deux tables ensemble. Une annonce contient seulement l’**id** du propriétaire ; avec un `JOIN` sur `utilisateurs`, on récupère aussi son **nom**. |
| **Transaction** | « Tout ou rien ». Plusieurs changements faits ensemble : si un seul rate, on annule tout. |
| **`FOR UPDATE`** | Un verrou. Si deux personnes cliquent en même temps, la deuxième attend que la première ait fini. Ça évite les doublons. |
| **`COUNT` / `AVG`** | Compter des lignes / faire une moyenne. |

---

## 1. Annonce (les gardes proposées)

Imagine un **tableau d’affichage** avec des petites annonces.

**Les plus importantes :**
- **`rechercher()`** : c’est la barre de recherche. Elle construit le filtre selon ce qu’on a tapé (ville, animal, mot). Un visiteur ne voit que les annonces encore ouvertes ; dans « Mes annonces », on voit toutes les siennes. Elle ajoute le nom et la note du propriétaire.
- **`valider()`** : le **contrôleur des règles**. Titre rempli ? Dates correctes ? Début pas dans le passé ? Fin après le début ? Elle renvoie la liste des erreurs.
- **`creer()`** : appelle `valider()`, puis **ajoute** l’annonce avec le statut « ouverte ». Si elle renvoie une liste vide `[]`, tout s’est bien passé.
- **`modifier()` / `supprimer()`** : changer ou effacer une annonce, seulement si **c’est la sienne** et qu’elle est **encore ouverte**. Ces conditions sont écrites dans la requête : impossible de tricher.
- **`estModifiable()`** : répond oui ou non à « est-ce que je peux modifier cette annonce ? ».

**La vie d’une annonce :**

```
ouverte  →  pourvue  →  terminée
(créée)    (gardien choisi)  (garde finie)
```

- **`marquerPourvue()`** : ouverte → pourvue, quand un gardien est accepté.
- **`cloturer()`** : le propriétaire clique sur « Terminer la garde » → terminée.

**Les petites, pour l’affichage :**
- **`trouver()`** : va chercher une annonce par son numéro ; si elle n’existe pas, le site affiche « page introuvable » (404).
- **`detail()`** : pareil, pour la page complète, avec le nom du propriétaire et le nombre de candidatures.
- **`statistiques()`** : les chiffres de l’accueil (annonces ouvertes, gardes terminées, nombre de villes).
- **`definirPhoto()` / `retirerPhoto()`** : mettre ou enlever la photo. **`photoUrl()`** : l’image à afficher, la photo envoyée, sinon un dessin de l’animal.
- **`villeDisponible()` / `photoDisponible()`** : vérifient que la base a bien les colonnes ville et photo, pour ne pas planter sur une vieille base.
- **`toutes()`** : toutes les annonces ; sert seulement aux tests.

---

## 2. Candidature (un gardien qui se propose)

Comme **envoyer un CV** pour un job.

- **`postuler()`** : le gardien se propose. Refusé dans 3 cas :
  1. l’annonce n’est plus ouverte ou elle est périmée ;
  2. c’est **sa propre** annonce (on ne garde pas son propre chat) ;
  3. il a **déjà postulé**.

  Elle utilise un **verrou** (`FOR UPDATE`) : un double-clic ne crée pas deux candidatures.
- **`decider()`** : le propriétaire **accepte** ou **refuse**. S’il accepte, tout se fait d’un coup (transaction) :
  - ce gardien est accepté ;
  - l’annonce devient « pourvue » ;
  - les autres candidats sont refusés automatiquement.
- **`pourUtilisateur()`** : la liste des candidatures **envoyées** et **reçues**.
- **`estAccessible()`** : vérification interne : l’annonce est-elle ouverte et pas périmée ?

---

## 3. Avis (les notes après une garde)

Comme les **étoiles sur un site de livraison**.

- **`gardeFinie()`** : la garde est-elle finie ? Oui si elle est terminée ou si la date de fin est passée. On ne peut noter qu’après.
- **`destinataires()`** : les personnes qu’on **a le droit** de noter : garde finie, gardien accepté, pas déjà noté.
- **`creer()`** : enregistre l’avis en **revérifiant tout** : note entre 1 et 5, commentaire pas trop long, droit de noter cette personne.
- **`noteMoyenne()`** : la moyenne des notes reçues (« 4,5 / 5 »). Sans avis, la base répond « rien » (`NULL`) et le site affiche « Pas encore d’avis ».

**Pour l’affichage :**
- **`pourAnnonce()`** : les avis d’une annonce, avec qui a écrit et qui est noté.
- **`recus()`** : les avis reçus par quelqu’un, sur son profil.
- **`derniers()`** : les 3 derniers bons avis (4 étoiles ou plus), sur l’accueil.
- **`statistiques()`** : le nombre total d’avis et la moyenne générale, pour l’accueil.

---

## 4. Utilisateur (les comptes)

- **`creer()`** : l’inscription. Vérifie le nom, l’e-mail (valide et pas déjà pris) et le mot de passe (8 à 72 caractères). Le mot de passe est **brouillé** avec `password_hash()` : même nous, on ne peut pas le lire.
- **`trouverParEmail()`** : pour la connexion, retrouve le compte grâce à l’e-mail.
- **`trouver()`** : les infos publiques pour le profil (nom, photo), **jamais** l’e-mail.
- **`changerPhoto()`** : changer la photo de profil.

**Modération :**
- **`etatCompte()`** : à chaque page, vérifie que le compte n’a pas été suspendu.
- **`tous()`** : la liste de tous les comptes pour l’admin, avec leur nombre d’annonces.
- **`changerActif()`** : suspendre ou réactiver un compte. Un admin ne peut jamais être suspendu.

---

## 5. Favori (les annonces mises de côté)

Comme le **cœur ❤ sur Vinted**.

- **`ajouter()`** : mettre en favori, sauf sa propre annonce, et jamais deux fois la même.
- **`retirer()`** : enlever le favori.
- **`existe()`** : « est-ce déjà en favori ? ».
- **`parGardien()`** : la page « Mes favoris », le dernier ajouté en premier.
- **`idsParGardien()`** : savoir quels cœurs remplir sur les cartes.
- **`compterPourAnnonce()`** : le propriétaire voit « 3 gardiens intéressés ».

---

## 6. PhotoUploader (l’envoi de photos)

Le **vigile à l’entrée** pour les photos.

- **`enregistrer()`** : vérifie la photo avant de la garder :
  - pas plus de 2 Mo ;
  - c’est **vraiment** une image (il regarde le contenu, pas juste le nom) ;
  - il lui donne un **nom au hasard** ;
  - il enlève les infos cachées de la photo (comme la position GPS).
- **`supprimer()`** : efface la photo du disque.
- **`url()`** : l’adresse de la photo pour l’afficher.

---

## Le résumé en 3 phrases

1. **On vérifie d’abord, on écrit ensuite.**
2. **Les `?` protègent contre le piratage de la base.**
3. **Quand plusieurs choses doivent arriver ensemble, on utilise une transaction : tout ou rien.**

---

## Les questions que le professeur peut poser

### ⭐⭐⭐ Très probables

**1. « C’est quoi un modèle ? À quoi il sert ? »**
> C’est la classe qui parle à la base de données. Il fait les requêtes SQL et vérifie les règles. Il n’affiche jamais rien : c’est la vue qui affiche.

**2. « Comment vous protégez-vous des injections SQL ? »**
> Avec des requêtes préparées : on met des `?` dans le SQL et on envoie les valeurs à part. Une valeur ne peut jamais devenir une commande.

**3. « Que se passe-t-il quand le propriétaire accepte une candidature ? »**
> Dans `decider()`, trois choses ensemble : la candidature est acceptée, l’annonce devient « pourvue », les autres candidatures sont refusées. C’est dans une transaction : tout ou rien.

**4. « C’est quoi une transaction ? Pourquoi ici ? »**
> Un groupe de requêtes qui réussissent toutes ou sont toutes annulées. On n’a jamais une candidature acceptée avec une annonce encore ouverte.

**5. « Comment calculez-vous la note moyenne ? Et sans avis ? »**
> `SELECT AVG(note) FROM avis WHERE id_cible = ?`. Sans avis, `AVG` renvoie `NULL` : la fonction renvoie `null` et le profil affiche « Pas encore d’avis ».

### ⭐⭐ Probables

**6. « Qu’est-ce qui empêche de modifier l’annonce d’un autre ? »**
> Deux protections : le contrôleur vérifie que c’est son annonce, et la requête répète la condition `WHERE id = ? AND id_proprietaire = ?`. Même en trafiquant le formulaire, on ne modifie que les siennes.

**7. « Pourquoi `valider()` est séparée de `creer()` ? »**
> Parce que `creer()` et `modifier()` utilisent les mêmes règles : on les écrit une seule fois.

**8. « Pourquoi valider dans le modèle et pas seulement dans le formulaire HTML ? »**
> Le HTML (`required`, `maxlength`) se contourne facilement. Le modèle vérifie toujours avant d’écrire : une donnée fausse n’entre jamais en base.

**9. « Quand peut-on postuler ? Quels sont les refus ? »**
> Refusé si l’annonce n’est plus ouverte ou est périmée, si c’est sa propre annonce, ou si on a déjà postulé.

**10. « Comment est stocké le mot de passe ? »**
> Haché avec `password_hash()`, jamais en clair. À la connexion, on compare avec `password_verify()`.

### ⭐ Possibles

**11. « C’est quoi `FOR UPDATE` ? »**
> Un verrou sur la ligne : si quelqu’un double-clique sur « Postuler », la deuxième requête attend la première, donc pas de doublon.

**12. « Comment empêchez-vous d’ajouter deux fois le même favori ? »**
> La table `favoris` a une clé primaire composée `(id_gardien, id_annonce)` : la base refuse le doublon.

**13. « C’est quoi un `JOIN` ? Un exemple ? »**
> Ça relie deux tables : une annonce contient l’id du propriétaire ; avec un `JOIN` sur `utilisateurs`, on récupère son nom pour l’afficher.

**14. « Quand peut-on laisser un avis ? »**
> Quand la garde est finie (terminée ou date passée), entre le propriétaire et le gardien accepté, une seule fois par personne.

**15. « Que renvoie `creer()` ? »**
> Un tableau d’erreurs. S’il est vide `[]`, l’annonce a bien été créée.

---

## L’astuce si on ne sait pas

Si le professeur montre une fonction qu’on ne connaît plus, la lire à voix haute et chercher :

1. **Qu’est-ce qu’elle vérifie ?** (les `if` au début)
2. **Qu’est-ce qu’elle écrit ?** (`INSERT`, `UPDATE` ou `DELETE` avec des `?`)
3. **Qu’est-ce qu’elle renvoie ?**

Même sans s’en souvenir, on montre qu’on sait lire le code.
