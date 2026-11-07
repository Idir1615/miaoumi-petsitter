CREATE DATABASE IF NOT EXISTS miaoumi CHARACTER SET utf8mb4;
USE miaoumi;

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user'
);

CREATE TABLE annonces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    type_animal VARCHAR(100) NOT NULL,
    ville VARCHAR(100) NOT NULL DEFAULT '',
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    description TEXT,
    statut VARCHAR(50) NOT NULL DEFAULT 'ouverte',
    id_proprietaire INT NOT NULL,
    FOREIGN KEY (id_proprietaire) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

CREATE TABLE candidatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT,
    statut VARCHAR(50) NOT NULL DEFAULT 'en_attente',
    date_candidature DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_annonce INT NOT NULL,
    id_gardien INT NOT NULL,
    FOREIGN KEY (id_annonce) REFERENCES annonces(id) ON DELETE CASCADE,
    FOREIGN KEY (id_gardien) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note INT NOT NULL,
    commentaire TEXT,
    dates DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_annonce INT NOT NULL,
    id_auteur INT NOT NULL,
    id_cible INT NOT NULL,
    FOREIGN KEY (id_annonce) REFERENCES annonces(id) ON DELETE CASCADE,
    FOREIGN KEY (id_auteur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cible) REFERENCES utilisateurs(id) ON DELETE CASCADE
);
