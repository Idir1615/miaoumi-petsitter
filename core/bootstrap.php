<?php

// Préparation commune à toutes les pages.
session_start([
    'use_strict_mode' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/Annonce.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AnnonceController.php';

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
