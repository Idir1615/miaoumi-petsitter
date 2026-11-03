-- À exécuter une seule fois si la table avis a une colonne `date` au lieu de `dates`
-- (vérifier avec : SHOW COLUMNS FROM avis;). Le schéma du projet et views/avis.php utilisent `dates`.
-- Ne pas exécuter si la colonne `dates` existe déjà.
USE miaoumi;
ALTER TABLE avis CHANGE `date` `dates` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
