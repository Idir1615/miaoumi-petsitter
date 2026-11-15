<?php
require __DIR__ . '/../app/core/bootstrap.php';

// Le routeur associe chaque page à une méthode de contrôleur.
$routes = [
    'annonces' => [AnnonceController::class, 'index'],
    'mes-annonces' => [AnnonceController::class, 'index'],
    'mes-candidatures' => [CandidatureController::class, 'index'],
    'candidatures-recues' => [CandidatureController::class, 'index'],
    'annonce' => [AnnonceController::class, 'voir'],
    'creer-annonce' => [AnnonceController::class, 'creer'],
    'modifier-annonce' => [AnnonceController::class, 'modifier'],
    'supprimer-annonce' => [AnnonceController::class, 'supprimer'],
    'basculer_favori' => [FavoriController::class, 'basculer'],
    'mes_favoris' => [FavoriController::class, 'mesFavoris'],
    'cloturer-annonce' => [AnnonceController::class, 'cloturer'],
    'avis' => [AvisController::class, 'index'],
    'profil' => [ProfilController::class, 'index'],
    'photo-profil' => [ProfilController::class, 'photo'],
    'supprimer-photo-annonce' => [AnnonceController::class, 'supprimerPhoto'],
    'candidatures' => [CandidatureController::class, 'index'],
    'postuler' => [CandidatureController::class, 'postuler'],
    'decision-candidature' => [CandidatureController::class, 'decision'],
    'inscription' => [AuthController::class, 'inscription'],
    'connexion' => [AuthController::class, 'connexion'],
    'deconnexion' => [AuthController::class, 'deconnexion'],
    'admin-utilisateurs' => [AdminController::class, 'utilisateurs'],
    'suspendre-utilisateur' => [AdminController::class, 'suspendre'],
];

$page = $_GET['page'] ?? 'annonces';
if (!is_string($page) || !isset($routes[$page])) {
    http_response_code(404);
    require __DIR__ . '/../app/views/introuvable.php';
    exit;
}

try {
    verifierCompteConnecte();
    [$classe, $methode] = $routes[$page];
    $controleur = new $classe();
    $controleur->$methode();
} catch (PDOException | RuntimeException $exception) {
    http_response_code(503);
    require __DIR__ . '/../app/views/indisponible.php';
}
