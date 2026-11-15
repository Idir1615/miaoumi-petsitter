# Images du site

## Image d’une annonce

- Si le propriétaire a envoyé une photo, c’est elle qui s’affiche (`public/uploads/annonces/`, voir `PhotoUploader`).
- Sinon, l’**illustration du type d’animal** s’affiche : `animaux/illustrations/chat.svg`, `chien.svg`, `lapin.svg`, `oiseau.svg`, `poisson.svg`, et `autre.svg` (une patte) pour tous les autres types. Aucune photo n’est choisie au hasard.

`photoAnimal()` (`app/core/photos.php`) fait ce choix ; `cleAnimal()` transforme « Chats » ou « Oiseaux » en `chat` ou `oiseau`.

Les photos `animaux/chat1.jpg`, `chat2.jpg`, `lapin1.jpg` et `poisson1.jpg` (première refonte de Maria) ne sont plus affichées ; `chien1.jpg` sert seulement de photo de secours pour la bannière.

## Photo de la bannière d’accueil

La bannière affiche `accueil.jpg` (un chien et un chat, domaine public) si ce fichier existe, sinon `animaux/chien1.jpg`. Pour la changer, remplacer `accueil.jpg` par une autre image libre de droits (paysage, environ 1 400 × 900 pixels) : aucun code à modifier. Crédit dans `animaux/CREDITS.md`.
