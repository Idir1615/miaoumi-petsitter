<?php
// Test PHP/MySQL isolé : php tests/annonces.php
// Les tables temporaires disparaissent à la fermeture de la connexion.
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/flash.php';
require __DIR__ . '/../models/Annonce.php';
require __DIR__ . '/../models/Candidature.php';
require __DIR__ . '/../controllers/AnnonceController.php';

function verifier(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function e(string $texte): string
{
    return htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
}

$pdo = Database::getConnection();
$pdo->exec('CREATE TEMPORARY TABLE utilisateurs (id INT PRIMARY KEY) ENGINE=InnoDB');
$pdo->exec("CREATE TEMPORARY TABLE annonces (
    id INT AUTO_INCREMENT PRIMARY KEY, titre VARCHAR(150), type_animal VARCHAR(100),
    date_debut DATE, date_fin DATE, description TEXT, statut VARCHAR(50), id_proprietaire INT
) ENGINE=InnoDB");
$pdo->exec('INSERT INTO utilisateurs VALUES (1)');
$modele = new Annonce();
verifier($modele->toutes() === [], 'La liste doit pouvoir être vide.');
$_SESSION = ['csrf' => 'test'];
$annonces = [];
ob_start();
require __DIR__ . '/../views/annonces.php';
$html = ob_get_clean();
verifier(strpos($html, 'Aucune annonce pour le moment.') !== false, 'État vide absent.');

$donnees = ['titre' => '<Garde & chat>', 'type_animal' => 'Chat',
    'date_debut' => '2099-01-01', 'date_fin' => '2099-01-02', 'description' => '<script>test</script>'];
foreach ([['titre', ''], ['titre', str_repeat('a', 151)], ['type_animal', ''],
    ['date_debut', '2099-02-30'], ['date_debut', '2000-01-01'],
    ['date_fin', '2098-01-01'], ['description', str_repeat('a', 5001)]] as [$champ, $valeur]) {
    $invalide = $donnees;
    $invalide[$champ] = $valeur;
    verifier(isset($modele->creer($invalide, 1)[$champ]), 'Validation absente : ' . $champ);
}
verifier(isset($modele->creer($donnees, 0)['general']), 'Auteur invalide accepté.');
verifier(isset($modele->creer($donnees, 999)['general']), 'Compte inexistant accepté.');
verifier($modele->toutes() === [], 'Une entrée invalide a été insérée.');
verifier($modele->creer($donnees, 1) === [], 'Création valide refusée.');
$annonces = $modele->toutes();
verifier(count($annonces) === 1 && (int) $annonces[0]['id_proprietaire'] === 1, 'Auteur incorrect.');
verifier($annonces[0]['statut'] === 'ouverte', 'Statut initial incorrect.');
verifier($modele->trouver((int) $annonces[0]['id']) !== null, 'Lecture impossible.');
ajouterFlash('<Annonce publiée>');
ob_start();
require __DIR__ . '/../views/annonces.php';
$html = ob_get_clean();
verifier(strpos($html, '&lt;Garde &amp; chat&gt;') !== false, 'Titre non échappé.');
verifier(strpos($html, '&lt;script&gt;test&lt;/script&gt;') !== false, 'Description non échappée.');
verifier(strpos($html, '&lt;Annonce publiée&gt;') !== false, 'Flash non échappé.');
verifier(lireFlash() === [], 'Flash non supprimé après lecture.');
verifier(strpos($html, 'assets/style.css') !== false && strpos($html, 'site-footer') !== false, 'Gabarit incomplet.');

$erreurs = ['titre' => 'Erreur de test'];
ob_start();
require __DIR__ . '/../views/annonce-formulaire.php';
$html = ob_get_clean();
verifier(strpos($html, 'value="&lt;Garde &amp; chat&gt;"') !== false, 'Saisie non conservée.');
verifier(strpos($html, 'Erreur de test') !== false, 'Erreur non affichée.');

$controleur = new AnnonceController();
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION = [];
$controleur->creer();
verifier(http_response_code() === 303 && count($modele->toutes()) === 1, 'Visiteur non connecté non redirigé.');
lireFlash();
$_SESSION['utilisateur'] = ['id' => 1, 'nom' => 'Test'];
$_SESSION['csrf'] = 'test';
$_POST = $donnees + ['id_proprietaire' => 999];
$controleur->creer();
verifier(http_response_code() === 303, 'Redirection après création absente.');
$annonces = $modele->toutes();
verifier(count($annonces) === 2 && (int) $annonces[0]['id_proprietaire'] === 1, 'Propriétaire détourné par le formulaire.');
verifier(count(lireFlash()) === 1, 'Flash de création absent.');
$_POST['date_fin'] = '2098-01-01';
ob_start();
$controleur->creer();
$html = ob_get_clean();
verifier(http_response_code() === 422 && count($modele->toutes()) === 2, 'Formulaire invalide accepté.');
verifier(strpos($html, 'value="&lt;Garde &amp; chat&gt;"') !== false, 'Le contrôleur a perdu la saisie.');
// Compatibilité d'une ancienne base puis filtre sur une base avec la ville.
verifier(!$modele->villeDisponible(), 'Ancienne structure mal détectée.');
$pdo->exec("ALTER TABLE annonces ADD COLUMN ville VARCHAR(100) NOT NULL DEFAULT ''");
verifier($modele->villeDisponible(), 'Champ ville non détecté.');
$avecVille = $donnees + ['ville' => 'Montreuil'];
verifier($modele->creer($avecVille, 1) === [], 'Création avec ville refusée.');
verifier(count($modele->rechercher('', '', null, 'Montreuil')) === 1, 'Filtre ville incorrect.');
verifier($modele->rechercher("' OR 1=1 --", '', null) === [], 'Recherche non paramétrée.');
verifier($modele->rechercher('', '', 999) === [], 'Annonces privées non filtrées.');
$avecVille['ville'] = str_repeat('a', 101);
verifier(isset($modele->creer($avecVille, 1)['ville']), 'Longueur de ville non validée.');
$pdo->exec("ALTER TABLE utilisateurs ADD COLUMN nom VARCHAR(100) DEFAULT 'Test'");
$pdo->exec('INSERT INTO utilisateurs (id) VALUES (2), (3)');
$pdo->exec('CREATE TEMPORARY TABLE candidatures (id INT PRIMARY KEY, id_annonce INT, id_gardien INT, statut VARCHAR(50), message TEXT, date_candidature DATETIME) ENGINE=InnoDB');
$pdo->exec("INSERT INTO candidatures VALUES (1,1,2,'en_attente','Test','2026-10-20 12:00:00')");
$candidatureModele = new Candidature();
verifier(count($candidatureModele->pourGardien(2)) === 1, 'Candidature du gardien absente.');
verifier($candidatureModele->pourGardien(3) === [], 'Candidature visible par un autre gardien.');
verifier(count($candidatureModele->pourProprietaire(1, 1)) === 1, 'Candidature reçue absente.');
verifier($candidatureModele->pourProprietaire(3, 1) === [], 'Candidature visible par un autre propriétaire.');
echo "OK : validation, création, propriétaire issu de la session, refus des visiteurs, redirections, liste vide, échappement, formulaire, gabarit et flash à lecture unique. Tables MySQL temporaires uniquement.\n";
echo "OK : ville, compatibilité ancien schéma, filtres paramétrés et confidentialité des candidatures.\n";
