-- À exécuter une seule fois sur une base existante (si la colonne actif n'existe pas).
-- Modération : un compte suspendu (actif = 0) ne peut plus se connecter. Tous les comptes existants restent actifs.
-- La colonne role existe déjà depuis le schéma initial : 'user' par défaut, 'admin' pour la modération.
USE miaoumi;
ALTER TABLE utilisateurs ADD COLUMN actif TINYINT(1) NOT NULL DEFAULT 1;
