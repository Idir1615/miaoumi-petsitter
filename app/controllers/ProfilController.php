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
        $photoDisponible = (new Utilisateur())->photoDisponible();
        $estMoi = (int) ($_SESSION['utilisateur']['id'] ?? 0) === $id;
        require __DIR__ . '/../views/profil.php';
    }

    // Ajout, remplacement ou suppression de sa propre photo de profil.
    public function photo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Utilise le formulaire de ton profil.');
        }
        $id = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        if (!$id) {
            ajouterFlash('Connecte-toi pour modifier ta photo.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }
        $modele = new Utilisateur();
        if (($_POST['action'] ?? '') === 'supprimer') {
            supprimerPhoto($modele->changerPhoto($id, null), 'profils');   // droit à l'effacement
            ajouterFlash('Ta photo de profil a été supprimée.');
        } elseif (($_POST['consentement'] ?? '') !== 'oui') {
            ajouterFlash('Coche la case pour accepter que ta photo soit visible sur ton profil public.', 'erreur');
        } else {
            $photo = enregistrerPhoto($_FILES['photo'] ?? null, 'profils');
            if (isset($photo['nom'])) {
                supprimerPhoto($modele->changerPhoto($id, $photo['nom']), 'profils');
                ajouterFlash('Ta photo de profil a été enregistrée.');
            } else {
                ajouterFlash($photo['erreur'] ?? 'Choisis une photo.', 'erreur');
            }
        }
        header('Location: ?page=profil&id=' . $id, true, 303);
    }
}

