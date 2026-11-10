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

            // La confirmation concerne le formulaire, pas la donnée : elle reste dans le contrôleur.
            if ($password !== $this->champ('confirmation')) {
                $erreurs['confirmation'] = 'Les mots de passe ne correspondent pas.';
            } else {
                // Le modèle valide nom, e-mail et mot de passe avant l'INSERT.
                $erreurs = (new Utilisateur())->creer($nom, $email, $password);
                if (!$erreurs) {
                    ajouterFlash('Ton compte a été créé. Tu peux te connecter.');
                    $this->rediriger('?page=connexion');
                }
            }
            http_response_code(422);
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
                    $destination = '?page=annonces';
                    $annonceApresConnexion = filter_var(
                        $_SESSION['annonce_apres_connexion'] ?? null,
                        FILTER_VALIDATE_INT,
                        ['options' => ['min_range' => 1]]
                    );
                    unset($_SESSION['annonce_apres_connexion']);
                    if ($annonceApresConnexion) {
                        $destination = '?page=postuler&annonce=' . $annonceApresConnexion;
                    }
                    ajouterFlash('Bonjour ' . $utilisateur['nom'] . ', tu es connecté·e.');
                    $this->rediriger($destination);
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
        // La session vide reste ouverte : le message sera lu sur la page suivante.
        ajouterFlash('Tu es déconnecté·e. À bientôt !');
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
