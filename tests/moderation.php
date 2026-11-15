<?php
// Test PHP/MySQL isolé : php tests/moderation.php
// Vérifie la modération dans le modèle Utilisateur. Les tables temporaires disparaissent à la fermeture de la connexion.
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/models/Utilisateur.php';

function verifier(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = Database::getConnection();
$pdo->exec("CREATE TEMPORARY TABLE utilisateurs (id INT PRIMARY KEY, nom VARCHAR(100), email VARCHAR(150) UNIQUE, password VARCHAR(255),
    role VARCHAR(50) NOT NULL DEFAULT 'user', actif TINYINT(1) NOT NULL DEFAULT 1) ENGINE=InnoDB");
$pdo->exec("CREATE TEMPORARY TABLE annonces (id INT PRIMARY KEY, id_proprietaire INT) ENGINE=InnoDB");
$pdo->exec("INSERT INTO utilisateurs (id, nom, email, password, role) VALUES (1, 'Admin', 'admin@test.fr', 'x', 'admin'), (2, 'Bob', 'bob@test.fr', 'x', 'user')");
$pdo->exec("INSERT INTO annonces VALUES (10, 2), (11, 2)");
$modele = new Utilisateur();

verifier((int) $modele->etatCompte(2)['actif'] === 1, 'Un compte est actif par défaut.');
verifier($modele->changerActif(2, false), 'L’admin doit pouvoir suspendre Bob.');
verifier((int) $modele->etatCompte(2)['actif'] === 0, 'Bob doit être suspendu.');
verifier((int) $modele->trouverParEmail('bob@test.fr')['actif'] === 0, 'La connexion doit lire actif = 0.');
verifier(!$modele->changerActif(1, false), 'Un administrateur ne doit pas pouvoir être suspendu.');
verifier((int) $modele->etatCompte(1)['actif'] === 1, 'L’administrateur doit rester actif.');
verifier(!$modele->changerActif(999, false), 'Un compte inexistant ne change rien.');
verifier($modele->changerActif(2, true), 'Bob doit pouvoir être réactivé.');
verifier((int) $modele->etatCompte(2)['actif'] === 1, 'Bob doit être actif de nouveau.');
verifier($modele->etatCompte(999) === null, 'Un compte supprimé renvoie null (déconnexion).');

$liste = $modele->tous();
verifier($liste[0]['nom'] === 'Admin', 'Les administrateurs sont listés en premier.');
verifier((int) $liste[1]['nb_annonces'] === 2 && (int) $liste[0]['nb_annonces'] === 0, 'Le nombre d’annonces doit être compté.');

echo "OK : compte actif par défaut, suspension et réactivation, administrateur jamais suspendu, compte inexistant, liste avec nombre d’annonces. Tables MySQL temporaires uniquement.\n";
