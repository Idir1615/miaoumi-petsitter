<?php

// Modération : rôle administrateur et comptes suspendus.

// Relu en base à chaque page (appelée par public/index.php) : un compte suspendu ou supprimé pendant
// qu'il est connecté est déconnecté dès sa requête suivante, et un changement de rôle s'applique tout de suite.
function verifierCompteConnecte(): void
{
    $id = (int) ($_SESSION['utilisateur']['id'] ?? 0);
    if (!$id) {
        return;
    }
    $compte = (new Utilisateur())->etatCompte($id);
    if ($compte && (int) $compte['actif']) {
        $_SESSION['utilisateur']['nom'] = $compte['nom'];
        $_SESSION['utilisateur']['role'] = $compte['role'];
        return;
    }
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    ajouterFlash('Ce compte est suspendu. Tu as été déconnecté·e.', 'erreur');
    header('Location: ?page=connexion', true, 303);
    exit;
}

function estAdmin(): bool
{
    return ($_SESSION['utilisateur']['role'] ?? '') === 'admin';
}
