<?php

// Préparation commune à toutes les pages.
$sessionPath = __DIR__ . '/../../storage/sessions';
if (!is_dir($sessionPath) && !mkdir($sessionPath, 0700, true) && !is_dir($sessionPath)) {
    throw new RuntimeException('Cannot create the session directory.');
}
session_save_path($sessionPath);

session_start([
    'use_strict_mode' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/icones.php';
require_once __DIR__ . '/PhotoUploader.php';
require_once __DIR__ . '/photos.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/Annonce.php';
require_once __DIR__ . '/../models/Avis.php';
require_once __DIR__ . '/../models/Candidature.php';
require_once __DIR__ . '/../models/Favori.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AnnonceController.php';
require_once __DIR__ . '/../controllers/AvisController.php';
require_once __DIR__ . '/../controllers/CandidatureController.php';
require_once __DIR__ . '/../controllers/ProfilController.php';
require_once __DIR__ . '/../controllers/FavoriController.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(32));

// Toute soumission de formulaire doit provenir de la session courante.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jeton = $_POST['csrf'] ?? null;
    if (!is_string($jeton) || !hash_equals($_SESSION['csrf'], $jeton)) {
        http_response_code(403);
        exit('Formulaire expiré. Recharge la page et réessaie.');
    }
}
