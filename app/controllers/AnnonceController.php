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
        $villeDisponible = (new Annonce())->villeDisponible();
        $photoDisponible = (new Annonce())->photoDisponible();
        $donnees = array_fill_keys(['titre', 'type_animal', 'ville', 'date_debut', 'date_fin', 'description'], '');
        $erreurs = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($donnees as $champ => $valeur) {
                $donnees[$champ] = is_string($_POST[$champ] ?? null) ? trim($_POST[$champ]) : '';
            }
            $photo = $photoDisponible ? enregistrerPhoto($_FILES['photo'] ?? null, 'annonces') : [];
            if (isset($photo['erreur'])) {
                $erreurs = ['photo' => $photo['erreur']];
            } else {
                $donnees['photo'] = $photo['nom'] ?? null;
                $erreurs = (new Annonce())->creer($donnees, (int) $_SESSION['utilisateur']['id']);
                if ($erreurs) {
                    supprimerPhoto($donnees['photo'], 'annonces');   // annonce refusée : on ne garde pas la photo
                }
            }
            if (!$erreurs) {
                ajouterFlash('Ton annonce a été publiée.');
                header('Location: ?page=annonces', true, 303);
                return;
            }
            http_response_code(422);
        }
        require __DIR__ . '/../views/annonce-formulaire.php';
    }

    public function cloturer(): void

    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Utilise le bouton « Terminer la garde ».');
        }
        if (!isset($_SESSION['utilisateur']['id'])) {
            ajouterFlash('Connecte-toi pour clôturer une garde.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }
        $id = filter_var($_POST['annonce'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $erreurs = (new Annonce())->cloturer((int) $id, (int) $_SESSION['utilisateur']['id']);
        if ($erreurs) {
            ajouterFlash($erreurs['general'], 'erreur');
        } else {
            ajouterFlash('La garde est clôturée.');
        }
        header('Location: ?page=mes-annonces', true, 303);
    }

    // Droit à l'effacement : le propriétaire retire la photo de son annonce.
    public function supprimerPhoto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Utilise le bouton « Supprimer la photo ».');
        }
        if (!isset($_SESSION['utilisateur']['id'])) {
            ajouterFlash('Connecte-toi pour modifier tes annonces.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }
        $id = filter_var($_POST['annonce'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $photo = (new Annonce())->retirerPhoto((int) $id, (int) $_SESSION['utilisateur']['id']);
        if ($photo) {
            supprimerPhoto($photo, 'annonces');
            ajouterFlash('La photo a été supprimée.');
        } else {
            ajouterFlash('Cette photo ne peut pas être supprimée.', 'erreur');
        }
        header('Location: ?page=mes-annonces', true, 303);
    }

    public function index(): void
    {
        $mesAnnonces = ($_GET['page'] ?? '') === 'mes-annonces';
        if ($mesAnnonces && !isset($_SESSION['utilisateur']['id'])) {
            ajouterFlash('Connecte-toi pour retrouver tes annonces.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }
        $recherche = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 150) : '';
        $animal = is_string($_GET['animal'] ?? null) ? mb_substr(trim($_GET['animal']), 0, 100) : '';
        $modele = new Annonce();
        $aujourdHui = (new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d');
        $villeDisponible = $modele->villeDisponible();
        $ville = $villeDisponible && is_string($_GET['ville'] ?? null) ? mb_substr(trim($_GET['ville']), 0, 100) : '';
        $annoncesCompletes = $modele->rechercher($recherche, $animal,
            $mesAnnonces ? (int) $_SESSION['utilisateur']['id'] : null, $ville);
        $statistiques = ['total' => count($annoncesCompletes), 'terminees' => count(array_filter($annoncesCompletes, fn($a) => $a['statut'] === 'terminee'))];
        $statut = $mesAnnonces && is_string($_GET['statut'] ?? null) && in_array($_GET['statut'], ['ouverte', 'pourvue', 'terminee'], true) ? $_GET['statut'] : '';
        if ($statut !== '') {
            $annoncesCompletes = array_values(array_filter($annoncesCompletes, fn($a) => $a['statut'] === $statut));
        }
        $total = count($annoncesCompletes);
        $nombrePages = max(1, (int) ceil($total / 6));
        $numero = filter_var($_GET['numero'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $numero = max(1, min($nombrePages, $numero));
        $annonces = array_slice($annoncesCompletes, ($numero - 1) * 6, 6);
        require __DIR__ . '/../views/annonces.php';
    }
}
