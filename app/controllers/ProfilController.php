<?php

class ProfilController
{
    // Profil public : nom, note moyenne et avis reçus d'un utilisateur.
    public function index(): void
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $utilisateur = $id ? (new Utilisateur())->trouver($id) : null;
        if (!$utilisateur) {
            http_response_code(404);
            require __DIR__ . '/../views/introuvable.php';
            return;
        }
        $modele = new Avis();
        $avis = $modele->recus($id);
        // noteMoyenne() est livrée par Maria ; en attendant, le profil affiche « Pas encore d’avis ».
        $moyenne = method_exists($modele, 'noteMoyenne') ? $modele->noteMoyenne($id) : null;
        require __DIR__ . '/../views/profil.php';
    }
}
