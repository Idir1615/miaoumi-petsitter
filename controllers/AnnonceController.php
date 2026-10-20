<?php

class AnnonceController
{
    public function creer(): void
    {
        if (!isset($_SESSION['utilisateur']['id'])) {
            ajouterFlash('Connecte-toi pour publier une annonce.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }
        $donnees = array_fill_keys(['titre', 'type_animal', 'date_debut', 'date_fin', 'description'], '');
        $erreurs = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($donnees as $champ => $valeur) {
                $donnees[$champ] = is_string($_POST[$champ] ?? null) ? trim($_POST[$champ]) : '';
            }
            $erreurs = (new Annonce())->creer($donnees, (int) $_SESSION['utilisateur']['id']);
            if (!$erreurs) {
                ajouterFlash('Ton annonce a été publiée.');
                header('Location: ?page=annonces', true, 303);
                return;
            }
            http_response_code(422);
        }
        require __DIR__ . '/../views/annonce-formulaire.php';
    }

    public function index(): void
    {
        $modele = new Annonce();
        $annonces = $modele->toutes();
        require __DIR__ . '/../views/annonces.php';
    }
}
