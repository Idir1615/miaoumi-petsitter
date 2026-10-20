<?php
require __DIR__ . '/../core/bootstrap.php';

// Le routeur associe chaque page à une méthode de contrôleur.
$routes = [
    'annonces' => [AnnonceController::class, 'index'],
    'creer-annonce' => [AnnonceController::class, 'creer'],
    'avis' => [AvisController::class, 'index'],
    'inscription' => [AuthController::class, 'inscription'],
    'connexion' => [AuthController::class, 'connexion'],
    'deconnexion' => [AuthController::class, 'deconnexion'],
];

$page = $_GET['page'] ?? 'annonces';
if (!is_string($page) || !isset($routes[$page])) {
    http_response_code(404);
    exit('Page introuvable');
}

try {
    [$classe, $methode] = $routes[$page];
    $controleur = new $classe();
    $controleur->$methode();
} catch (PDOException | RuntimeException $exception) {
    http_response_code(503);
    require __DIR__ . '/../views/indisponible.php';
}
