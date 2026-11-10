<?php
// Test PHP/MySQL isolé : php tests/parcours.php
// Vérifie l'acceptation d'une candidature (annonce pourvue, autres refusées) et la note moyenne.
// Les tables temporaires disparaissent à la fermeture de la connexion.
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/models/Annonce.php';
require __DIR__ . '/../app/models/Candidature.php';
require __DIR__ . '/../app/models/Avis.php';

function verifier(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = Database::getConnection();
$pdo->exec("CREATE TEMPORARY TABLE annonces (id INT PRIMARY KEY, statut VARCHAR(50), id_proprietaire INT) ENGINE=InnoDB");
$pdo->exec("CREATE TEMPORARY TABLE candidatures (id INT PRIMARY KEY, id_annonce INT, id_gardien INT, statut VARCHAR(50)) ENGINE=InnoDB");
$pdo->exec("CREATE TEMPORARY TABLE avis (id INT AUTO_INCREMENT PRIMARY KEY, note INT, id_cible INT) ENGINE=InnoDB");

// Annonce 1 du propriétaire 1, trois gardiens ; annonce 2 avec une candidature qui ne doit pas bouger.
$pdo->exec("INSERT INTO annonces VALUES (1, 'ouverte', 1), (2, 'ouverte', 1)");
$pdo->exec("INSERT INTO candidatures VALUES (10, 1, 2, 'en_attente'), (11, 1, 3, 'en_attente'), (12, 1, 4, 'en_attente'), (20, 2, 2, 'en_attente')");
$statut = fn(string $table, int $id) => $pdo->query("SELECT statut FROM $table WHERE id = $id")->fetchColumn();
$candidatures = new Candidature();

verifier(!$candidatures->decider(10, 2, 'acceptee'), 'Un tiers a pu accepter une candidature.');
verifier(!$candidatures->decider(10, 1, 'peut-etre'), 'Décision inconnue acceptée.');
verifier($candidatures->decider(12, 1, 'refusee'), 'Refus par le propriétaire impossible.');
verifier($statut('annonces', 1) === 'ouverte', 'Un refus ne doit pas rendre l’annonce pourvue.');
verifier($candidatures->decider(10, 1, 'acceptee'), 'Acceptation par le propriétaire impossible.');
verifier($statut('candidatures', 10) === 'acceptee', 'Candidature acceptée non enregistrée.');
verifier($statut('annonces', 1) === 'pourvue', 'L’annonce acceptée doit passer en pourvue.');
verifier($statut('candidatures', 11) === 'refusee', 'L’autre candidature en attente doit être refusée.');
verifier($statut('candidatures', 12) === 'refusee', 'Une candidature déjà refusée doit le rester.');
verifier($statut('candidatures', 20) === 'en_attente', 'Une candidature d’une autre annonce a été modifiée.');
verifier($statut('annonces', 2) === 'ouverte', 'Une autre annonce a été modifiée.');
verifier(!$candidatures->decider(11, 1, 'acceptee'), 'Une candidature déjà refusée a été acceptée.');
echo "OK : acceptation réservée au propriétaire, annonce pourvue, autres candidatures refusées, autres annonces intactes.\n";

$avis = new Avis();
verifier($avis->noteMoyenne(5) === null, 'Sans avis, la moyenne doit être null.');
$pdo->exec('INSERT INTO avis (note, id_cible) VALUES (4, 5), (5, 5), (3, 5), (1, 6)');
verifier($avis->noteMoyenne(5) === 4.0, 'Moyenne de 4, 5 et 3 incorrecte.');
$pdo->exec('INSERT INTO avis (note, id_cible) VALUES (5, 5)');
verifier($avis->noteMoyenne(5) === 4.3, 'La moyenne doit être arrondie à une décimale (4,25 -> 4,3).');
verifier($avis->noteMoyenne(6) === 1.0, 'Les avis d’une autre personne sont mélangés.');
echo "OK : note moyenne (null sans avis, arrondie à une décimale, par personne). Tables MySQL temporaires uniquement.\n";
