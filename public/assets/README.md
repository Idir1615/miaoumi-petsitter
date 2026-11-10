# Photos des animaux

Dépose ici les photos des annonces, nommées **type + numéro** :

| Type d'animal de l'annonce | Noms de fichiers |
| --- | --- |
| Chat | `chat1.jpg`, `chat2.jpg`, `chat3.jpg`… |
| Chien | `chien1.jpg`, `chien2.jpg`… |
| Lapin | `lapin1.jpg`… |
| Oiseau | `oiseau1.jpg`… |
| Poisson | `poisson1.jpg`… |
| Autre (hamster, tortue…) | `autre1.jpg`… |

- Formats acceptés : `.jpg`, `.jpeg`, `.png`, `.webp` (majuscules ou minuscules).
- Les numéros n'ont pas besoin de se suivre ; il en faut au moins un par type.
- Format conseillé : paysage, environ 800 × 500 pixels, moins de 300 Ko.
- Sous Windows, vérifier que le fichier ne s'appelle pas `chat1.jpg.jpg` (afficher les extensions dans l'Explorateur).

`photoAnimal()` (`app/core/photos.php`) choisit la photo d'après le type d'animal et le numéro de l'annonce : une annonce garde toujours la même image. Sans photo pour un type, l'illustration de `illustrations/` est affichée.

Utiliser des photos libres de droits (par exemple Unsplash ou Pexels) ou ses propres photos.
