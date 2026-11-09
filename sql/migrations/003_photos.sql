-- À exécuter une seule fois sur une base existante (si les colonnes photo n'existent pas).
-- La colonne contient seulement le nom aléatoire du fichier enregistré dans public/uploads/.
USE miaoumi;
ALTER TABLE utilisateurs ADD COLUMN photo VARCHAR(64) NULL AFTER role;
ALTER TABLE annonces ADD COLUMN photo VARCHAR(64) NULL AFTER description;
