<?php
session_start(['use_strict_mode' => true, 'cookie_httponly' => true,
    'cookie_samesite' => 'Lax', 'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
require_once __DIR__ . '/../core/Database.php';
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(32));
$page = $_GET['page'] ?? 'annonces';
if (!is_string($page) || !in_array($page, ['annonces', 'inscription', 'connexion', 'deconnexion'], true)) {
    http_response_code(404);
    exit('Page introuvable');
}
$erreurs = [];
$nom = $email = '';
$post = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($post && (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf']))) {
    http_response_code(403);
    exit('Formulaire expiré. Recharge la page et réessaie.');
}
try {
    if ($page === 'deconnexion') {
        if (!$post) {
            http_response_code(405);
            header('Allow: POST');
            exit('Utilise le bouton de déconnexion.');
        }
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: ?page=connexion', true, 303);
        exit;
    }
    if ($page === 'connexion' || $page === 'inscription') {
        if ($post) {
            $nom = is_string($_POST['nom'] ?? null) ? trim($_POST['nom']) : '';
            $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
                $erreurs[] = 'Saisis une adresse e-mail valide (150 caractères maximum).';
            }
            if ($page === 'inscription') {
                if ($nom === '' || mb_strlen($nom) > 100) { $erreurs[] = 'Le nom est obligatoire (100 caractères maximum).'; }
                if (strlen($password) < 8 || strlen($password) > 72) { $erreurs[] = 'Le mot de passe doit contenir entre 8 et 72 octets.'; }
                if ($password !== ($_POST['confirmation'] ?? null)) { $erreurs[] = 'Les mots de passe ne correspondent pas.'; }
                if (!$erreurs) {
                    $requete = Database::getConnection()->prepare('INSERT INTO utilisateurs (nom, email, password) VALUES (?, ?, ?)');
                    try {
                        $requete->execute([$nom, $email, password_hash($password, PASSWORD_DEFAULT)]);
                        header('Location: ?page=connexion&inscrit=1', true, 303);
                        exit;
                    } catch (PDOException $exception) {
                        if (($exception->errorInfo[1] ?? null) === 1062) { $erreurs[] = 'Cette adresse e-mail est déjà utilisée.'; }
                        else { throw $exception; }
                    }
                }
            } else {
                if ($password === '' || strlen($password) > 72) { $erreurs[] = 'E-mail ou mot de passe incorrect.'; }
                if (!$erreurs) {
                    $requete = Database::getConnection()->prepare('SELECT id, nom, password FROM utilisateurs WHERE email = ?');
                    $requete->execute([$email]);
                    $utilisateur = $requete->fetch();
                    if ($utilisateur && password_verify($password, $utilisateur['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['utilisateur'] = ['id' => $utilisateur['id'], 'nom' => $utilisateur['nom']];
                        $_SESSION['csrf'] = bin2hex(random_bytes(32));
                        header('Location: ?page=annonces', true, 303);
                        exit;
                    }
                    $erreurs[] = 'E-mail ou mot de passe incorrect.';
                }
            }
        }
        require __DIR__ . '/../views/auth.php';
    } else {
        $annonces = Database::getConnection()->query('SELECT * FROM annonces ORDER BY id DESC')->fetchAll();
        require __DIR__ . '/../views/annonces.php';
    }
} catch (PDOException | RuntimeException $exception) {
    http_response_code(503);
    require __DIR__ . '/../views/indisponible.php';
}
