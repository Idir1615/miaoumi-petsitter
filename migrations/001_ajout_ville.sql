-- A executer une seule fois sur une base existante (si ville n'existe pas).
USE miaoumi;
ALTER TABLE annonces ADD COLUMN ville VARCHAR(100) NOT NULL DEFAULT '' AFTER type_animal;
