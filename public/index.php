<?php
require __DIR__ . '/../core/Database.php';

$page = $_GET['page'] ?? 'annonces';

switch ($page) {
    case 'annonces':
        $pdo = Database::getConnection();
        $annonces = $pdo->query("SELECT * FROM annonces ORDER BY id DESC")->fetchAll();
        require __DIR__ . '/../views/annonces.php';
        break;

    default:
        http_response_code(404);
        echo "Page introuvable";
}
