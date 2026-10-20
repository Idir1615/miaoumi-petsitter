<?php

class AuthController
{
    public function inscription(): void
    {
        $page = 'inscription';
        $erreurs = [];
        $nom = $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($this->champ('nom'));
            $email = trim($this->champ('email'));
            $password = $this->champ('password');

            if ($nom === '' || mb_strlen($nom) > 100) {
                $erreurs[] = 'Le nom est obligatoire (100 caractères maximum).';
            }
            if (!$this->emailValide($email)) {
                $erreurs[] = 'Saisis une adresse e-mail valide (150 caractères maximum).';
            }
            if (strlen($password) < 8 || strlen($password) > 72) {
                $erreurs[] = 'Le mot de passe doit contenir entre 8 et 72 octets.';
            }
            if ($password !== $this->champ('confirmation')) {
                $erreurs[] = 'Les mots de passe ne correspondent pas.';
            }

            if (!$erreurs) {
                try {
                    $modele = new Utilisateur();
                    $modele->creer($nom, $email, $password);
                    $this->rediriger('?page=connexion&inscrit=1');
                } catch (PDOException $exception) {
                    if (($exception->errorInfo[1] ?? null) === 1062) {
                        $erreurs[] = 'Cette adresse e-mail est déjà utilisée.';
                    } else {
                        throw $exception;
                    }
                }
            }
        }

        require __DIR__ . '/../views/auth.php';
    }

    public function connexion(): void
    {
        $page = 'connexion';
        $erreurs = [];
        $nom = $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($this->champ('email'));
            $password = $this->champ('password');

            if (!$this->emailValide($email)) {
                $erreurs[] = 'Saisis une adresse e-mail valide (150 caractères maximum).';
            }
            if ($password === '' || strlen($password) > 72) {
                $erreurs[] = 'E-mail ou mot de passe incorrect.';
            }

            if (!$erreurs) {
                $modele = new Utilisateur();
                $utilisateur = $modele->trouverParEmail($email);
                if ($utilisateur && password_verify($password, $utilisateur['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['utilisateur'] = ['id' => $utilisateur['id'], 'nom' => $utilisateur['nom']];
                    $_SESSION['csrf'] = bin2hex(random_bytes(32));
                    $this->rediriger('?page=annonces');
                }
                $erreurs[] = 'E-mail ou mot de passe incorrect.';
            }
        }

        require __DIR__ . '/../views/auth.php';
    }

    public function deconnexion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Utilise le bouton de déconnexion.');
        }

        $_SESSION = [];
        session_regenerate_id(true);
        $this->rediriger('?page=connexion');
    }

    private function champ(string $nom): string
    {
        return is_string($_POST[$nom] ?? null) ? $_POST[$nom] : '';
    }

    private function emailValide(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($email) <= 150;
    }

    private function rediriger(string $adresse): void
    {
        header('Location: ' . $adresse, true, 303);
        exit;
    }
}
