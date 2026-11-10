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
        $moyenne = $modele->noteMoyenne($id);
        // Annonces encore actives du membre (ouvertes ou pourvues), réutilise la recherche d'Idir.
        $annonces = array_values(array_filter((new Annonce())->rechercher('', '', $id), fn($a) => $a['statut'] !== 'terminee'));
        require __DIR__ . '/../views/profil.php';
    }
}
