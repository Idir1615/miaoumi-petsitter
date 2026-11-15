-- À exécuter une seule fois sur une base existante (si la table favoris n'existe pas).
-- Favoris : un gardien met des annonces de côté. Relation plusieurs-à-plusieurs entre utilisateurs et annonces.
-- Clé primaire composée : on ne peut pas ajouter deux fois la même annonce. Suppression en cascade.
USE miaoumi;
CREATE TABLE favoris (
    id_gardien INT NOT NULL,
    id_annonce INT NOT NULL,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_gardien, id_annonce),
    FOREIGN KEY (id_gardien) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_annonce) REFERENCES annonces(id) ON DELETE CASCADE
);
