<?php
// Test PHP/MySQL isolé : php tests/favoris.php
// Vérifie le modèle Favori (lot C). Les tables temporaires disparaissent à la fermeture de la connexion.
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/models/Favori.php';

function verifier(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = Database::getConnection();
$pdo->exec("CREATE TEMPORARY TABLE utilisateurs (id INT PRIMARY KEY, nom VARCHAR(100)) ENGINE=InnoDB");
$pdo->exec("CREATE TEMPORARY TABLE annonces (id INT PRIMARY KEY, titre VARCHAR(150), statut VARCHAR(50), id_proprietaire INT) ENGINE=InnoDB");
$pdo->exec("CREATE TEMPORARY TABLE favoris (id_gardien INT, id_annonce INT, date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_gardien, id_annonce)) ENGINE=InnoDB");

// Jeu d'essai du sujet : Alice (1), Bob (2), Chloé (3) ; annonces 1 Mochi (Alice), 3 Poissons (Bob), 5 Hamster (Chloé).
$pdo->exec("INSERT INTO utilisateurs VALUES (1, 'Alice'), (2, 'Bob'), (3, 'Chloé')");
$pdo->exec("INSERT INTO annonces VALUES (1, 'Mochi', 'ouverte', 1), (3, 'Poissons', 'ouverte', 2), (5, 'Hamster', 'ouverte', 3)");
$favoris = new Favori();

verifier(!$favoris->existe(2, 1), 'Aucun favori ne doit exister au départ.');
verifier($favoris->ajouter(2, 1), 'Bob doit pouvoir ajouter l’annonce 1.');
verifier($favoris->existe(2, 1), 'Le favori ajouté doit exister.');
verifier(!$favoris->ajouter(2, 1), 'Le même favori ne doit pas être ajouté deux fois.');
verifier(!$favoris->ajouter(2, 3), 'Bob ne doit pas pouvoir mettre sa propre annonce en favori.');
verifier(!$favoris->existe(2, 3), 'Aucun favori ne doit être créé pour sa propre annonce.');
verifier(!$favoris->ajouter(2, 999), 'Une annonce inexistante ne doit pas être ajoutée.');

// Ordre : le dernier ajouté en premier (dates fixées pour ne pas dépendre de la seconde courante).
$favoris->ajouter(2, 5);
$pdo->exec("UPDATE favoris SET date_ajout = '2026-01-01 10:00:00' WHERE id_annonce = 1");
$pdo->exec("UPDATE favoris SET date_ajout = '2026-01-01 11:00:00' WHERE id_annonce = 5");
$liste = $favoris->parGardien(2);
verifier(array_column($liste, 'id') === [5, 1] || array_column($liste, 'id') === ['5', '1'], 'Mes favoris doit afficher 5 puis 1.');
verifier($liste[0]['nom_proprietaire'] === 'Chloé', 'Le nom du propriétaire doit être joint.');
verifier($favoris->parGardien(3) === [], 'Chloé n’a aucun favori.');

$favoris->ajouter(3, 1);
verifier($favoris->compterPourAnnonce(1) === 2, 'L’annonce 1 intéresse deux gardiens.');
verifier($favoris->retirer(2, 1), 'Bob doit pouvoir retirer un favori.');
verifier(!$favoris->existe(2, 1), 'Le favori retiré ne doit plus exister.');
verifier(!$favoris->retirer(2, 1), 'Retirer deux fois ne doit rien faire.');
verifier($favoris->compterPourAnnonce(1) === 1, 'Le compteur doit baisser après le retrait.');

echo "OK : favoris ajoutés une seule fois, jamais sur sa propre annonce ni sur une annonce inexistante, retrait, ordre (dernier ajouté en premier), compteur. Tables MySQL temporaires uniquement.\n";
