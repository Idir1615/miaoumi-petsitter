<?php

// Back-office de modération : réservé aux comptes dont le rôle est 'admin'.
class AdminController
{
    // GET ?page=admin-utilisateurs : liste des comptes avec un bouton Suspendre / Réactiver.
    public function utilisateurs(): void
    {
        if (!$this->exigerAdmin()) {
            return;
        }
        $utilisateurs = (new Utilisateur())->tous();
        require __DIR__ . '/../views/admin-utilisateurs.php';
    }

    // POST ?page=suspendre-utilisateur : suspend le compte, ou le réactive s'il était suspendu.
    // Le jeton CSRF est vérifié par bootstrap.php pour tout POST (403 sinon).
    public function suspendre(): void
    {
        if (!$this->exigerAdmin()) {
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Utilise le bouton de la page de modération.');
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $modele = new Utilisateur();
        $compte = $id ? $modele->etatCompte($id) : null;
        if (!$compte) {
            http_response_code(404);
            require __DIR__ . '/../views/introuvable.php';
            return;
        }

        $reactiver = !(int) $compte['actif'];
        if ($modele->changerActif($id, $reactiver)) {
            ajouterFlash($reactiver ? 'Le compte de ' . $compte['nom'] . ' est réactivé.' : 'Le compte de ' . $compte['nom'] . ' est suspendu : il ne peut plus se connecter.');
        } else {
            // Le WHERE role <> 'admin' a refusé la modification.
            ajouterFlash('Un compte administrateur ne peut pas être suspendu.', 'erreur');
        }
        header('Location: ?page=admin-utilisateurs', true, 303);
    }

    // Visiteur : vers la connexion. Connecté sans être admin : 403 « Accès refusé ».
    private function exigerAdmin(): bool
    {
        if (!isset($_SESSION['utilisateur'])) {
            ajouterFlash('Connecte-toi pour accéder à la modération.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return false;
        }
        if (!estAdmin()) {
            http_response_code(403);
            $messageInterdit = 'La modération est réservée aux administrateurs.';
            require __DIR__ . '/../views/interdit.php';
            return false;
        }
        return true;
    }
}
