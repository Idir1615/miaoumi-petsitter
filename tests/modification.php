<?php
// Test PHP/MySQL isolé : php tests/modification.php
// Vérifie la modification, la suppression et la photo d'une annonce (lot B) dans le modèle Annonce.
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/core/PhotoUploader.php';
require __DIR__ . '/../app/core/photos.php';
require __DIR__ . '/../app/models/Annonce.php';

function verifier(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = Database::getConnection();
$pdo->exec("CREATE TEMPORARY TABLE annonces (
    id INT PRIMARY KEY, titre VARCHAR(150), type_animal VARCHAR(100), ville VARCHAR(100) NOT NULL DEFAULT '',
    date_debut DATE, date_fin DATE, description TEXT, photo VARCHAR(64) NULL, statut VARCHAR(50), id_proprietaire INT
) ENGINE=InnoDB");
$demain = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
$semaine = (new DateTimeImmutable('+7 days'))->format('Y-m-d');
$hier = (new DateTimeImmutable('yesterday'))->format('Y-m-d');
// 1 Mochi (Alice = 1, ouverte), 2 Rex (Alice, pourvue), 3 annonce d'Alice commencée hier mais encore ouverte.
$pdo->exec("INSERT INTO annonces VALUES
    (1, 'Mochi', 'Chat', 'Paris', '$demain', '$semaine', 'Chatte calme', NULL, 'ouverte', 1),
    (2, 'Rex', 'Chien', 'Lyon', '$demain', '$semaine', '', NULL, 'pourvue', 1),
    (3, 'Plume', 'Lapin', 'Paris', '$hier', '$semaine', '', NULL, 'ouverte', 1)");
$modele = new Annonce();
$donnees = ['titre' => 'Mochi et Moka', 'type_animal' => 'Chat', 'ville' => 'Paris 11e',
    'date_debut' => $demain, 'date_fin' => $semaine, 'description' => 'Deux chattes'];

verifier($modele->modifier(1, 2, $donnees) !== [], 'Bob ne doit pas pouvoir modifier l’annonce d’Alice.');
verifier($modele->trouver(1)['titre'] === 'Mochi', 'Une modification refusée ne doit rien changer.');
verifier($modele->modifier(2, 1, $donnees) !== [], 'Une annonce pourvue ne doit pas être modifiable.');
verifier(isset($modele->modifier(1, 1, ['titre' => ''] + $donnees)['titre']), 'Le titre vide doit être refusé.');
verifier(isset($modele->modifier(1, 1, ['date_fin' => $hier] + $donnees)['date_fin']), 'Une fin avant le début doit être refusée.');
verifier($modele->modifier(1, 1, $donnees) === [], 'Alice doit pouvoir modifier son annonce ouverte.');
$mochi = $modele->trouver(1);
verifier($mochi['titre'] === 'Mochi et Moka' && $mochi['ville'] === 'Paris 11e', 'La modification doit être enregistrée.');

// Une date de début déjà passée peut être gardée telle quelle, mais pas choisie.
verifier($modele->modifier(3, 1, ['date_debut' => $hier] + $donnees) === [], 'Garder une date de début passée doit être accepté.');
verifier(isset($modele->modifier(1, 1, ['date_debut' => $hier] + $donnees)['date_debut']), 'Choisir une date passée doit être refusé.');

// Photo : nom enregistré, puis retiré ; l'adresse revient à une image par défaut.
$nom = str_repeat('a', 32) . '.png';
$modele->definirPhoto(1, $nom);
verifier($modele->trouver(1)['photo'] === $nom, 'definirPhoto doit enregistrer le nom.');
verifier($modele->photoUrl($modele->trouver(1)) === 'uploads/annonces/' . $nom, 'photoUrl doit donner l’adresse de la photo.');
$modele->definirPhoto(1, null);
verifier($modele->trouver(1)['photo'] === null, 'definirPhoto(null) doit retirer la photo.');
verifier(strpos($modele->photoUrl($modele->trouver(1)), 'uploads/') === false, 'Sans photo, photoUrl doit donner l’image par défaut.');
verifier(PhotoUploader::url('../../config/config.php') === null, 'Un nom de fichier dangereux ne doit pas donner d’adresse.');

// Suppression : propriétaire seulement, annonce ouverte seulement.
verifier(!$modele->supprimer(1, 2), 'Bob ne doit pas pouvoir supprimer l’annonce d’Alice.');
verifier(!$modele->supprimer(2, 1), 'Une annonce pourvue ne doit pas être supprimée.');
verifier($modele->supprimer(1, 1) && $modele->trouver(1) === null, 'Alice doit pouvoir supprimer son annonce ouverte.');

echo "OK : modification et suppression réservées au propriétaire d’une annonce ouverte, validation commune, date de début conservée, photo enregistrée et retirée, nom dangereux refusé. Tables MySQL temporaires uniquement.\n";
