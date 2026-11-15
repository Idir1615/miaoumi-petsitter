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
        $modele = new Annonce();
        $villeDisponible = $modele->villeDisponible();
        $photoDisponible = $modele->photoDisponible();
        $annonce = null; // pas d'annonce existante : le formulaire est en mode création
        $donnees = array_fill_keys(self::CHAMPS, '');
        $erreurs = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $donnees = $this->lireChamps();
            $erreurs = $modele->valider($donnees);
            // La photo n'est traitée qu'une fois les champs valides : pas de fichier pour une annonce refusée.
            $photo = null;
            if (!$erreurs && $photoDisponible && $this->photoChoisie()) {
                try {
                    $photo = PhotoUploader::enregistrer($_FILES['photo']);
                } catch (RuntimeException $exception) {
                    $erreurs['photo'] = $exception->getMessage();
                }
            }
            if (!$erreurs) {
                $donnees['photo'] = $photo;
                $erreurs = $modele->creer($donnees, (int) $_SESSION['utilisateur']['id']);
                if ($erreurs && $photo !== null) {
                    PhotoUploader::supprimer($photo);   // annonce refusée : on ne garde pas la photo
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

    // Modification d'une annonce : ?page=modifier-annonce&id=… (propriétaire seulement, annonce ouverte).
    public function modifier(): void
    {
        if (!isset($_SESSION['utilisateur']['id'])) {
            ajouterFlash('Connecte-toi pour modifier tes annonces.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }
        $utilisateur = (int) $_SESSION['utilisateur']['id'];
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $modele = new Annonce();
        $annonce = $id ? $modele->trouver($id) : null;
        if (!$annonce) {
            http_response_code(404);
            require __DIR__ . '/../views/introuvable.php';
            return;
        }
        if (!$modele->estModifiable($annonce, $utilisateur)) {
            http_response_code(403);
            $messageInterdit = 'Seul le propriétaire peut modifier une annonce, et seulement tant qu’elle est ouverte.';
            require __DIR__ . '/../views/interdit.php';
            return;
        }
        $villeDisponible = $modele->villeDisponible();
        $photoDisponible = $modele->photoDisponible();
        $donnees = array_intersect_key(array_map('strval', $annonce), array_flip(self::CHAMPS)) + array_fill_keys(self::CHAMPS, '');
        $erreurs = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $donnees = $this->lireChamps();
            $erreurs = $modele->valider($donnees, $annonce['date_debut']);
            $nouvelle = null;
            if (!$erreurs && $photoDisponible && $this->photoChoisie()) {
                try {
                    $nouvelle = PhotoUploader::enregistrer($_FILES['photo']);
                } catch (RuntimeException $exception) {
                    $erreurs['photo'] = $exception->getMessage();
                }
            }
            if (!$erreurs) {
                $erreurs = $modele->modifier($id, $utilisateur, $donnees);
            }
            if (!$erreurs) {
                $ancienne = $annonce['photo'] ?? null;
                if ($nouvelle !== null) {
                    // Nouvelle photo : elle remplace l'ancienne, dont le fichier est effacé du disque.
                    $modele->definirPhoto($id, $nouvelle);
                    PhotoUploader::supprimer((string) $ancienne);
                } elseif (($_POST['supprimer_photo'] ?? '') === 'oui' && $ancienne) {
                    $modele->definirPhoto($id, null);
                    PhotoUploader::supprimer($ancienne);
                }
                ajouterFlash('Ton annonce a été modifiée.');
                header('Location: ?page=annonce&id=' . $id, true, 303);
                return;
            }
            if ($nouvelle !== null) {
                PhotoUploader::supprimer($nouvelle);
            }
            http_response_code(422);
        }
        require __DIR__ . '/../views/annonce-formulaire.php';
    }

    // Suppression d'une annonce ouverte par son propriétaire ; le fichier de sa photo est effacé.
    public function supprimer(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Utilise le bouton « Supprimer l’annonce ».');
        }
        if (!isset($_SESSION['utilisateur']['id'])) {
            ajouterFlash('Connecte-toi pour modifier tes annonces.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }
        $utilisateur = (int) $_SESSION['utilisateur']['id'];
        $id = filter_var($_POST['annonce'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $modele = new Annonce();
        $annonce = $id ? $modele->trouver($id) : null;
        if (!$annonce) {
            http_response_code(404);
            require __DIR__ . '/../views/introuvable.php';
            return;
        }
        if ((int) $annonce['id_proprietaire'] !== $utilisateur) {
            http_response_code(403);
            $messageInterdit = 'Seul le propriétaire peut supprimer cette annonce.';
            require __DIR__ . '/../views/interdit.php';
            return;
        }
        if ($modele->supprimer($id, $utilisateur)) {
            PhotoUploader::supprimer((string) ($annonce['photo'] ?? ''));
            ajouterFlash('L’annonce a été supprimée.');
        } else {
            ajouterFlash('Seule une annonce encore ouverte peut être supprimée.', 'erreur');
        }
        header('Location: ?page=mes-annonces', true, 303);
    }

    private const CHAMPS = ['titre', 'type_animal', 'ville', 'date_debut', 'date_fin', 'description'];

    private function lireChamps(): array
    {
        $donnees = [];
        foreach (self::CHAMPS as $champ) {
            $donnees[$champ] = is_string($_POST[$champ] ?? null) ? trim($_POST[$champ]) : '';
        }
        return $donnees;
    }

    // Vrai si un fichier a été choisi dans le champ photo (le champ est facultatif).
    private function photoChoisie(): bool
    {
        return isset($_FILES['photo']['error']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE;
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
            PhotoUploader::supprimer($photo);
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

        // Bandeau d'accueil : seulement sur la liste publique, première page, sans filtre.
        $accueil = null;
        if (!$mesAnnonces && $numero === 1 && $recherche === '' && $animal === '' && $ville === '') {
            $avis = new Avis();
            $accueil = [
                'annonces' => $modele->statistiques(),
                'avis' => $avis->statistiques(),
                'derniersAvis' => $avis->derniers(3),
            ];
        }
        require __DIR__ . '/../views/annonces.php';
    }

    // Page de détail d'une annonce : ?page=annonce&id=…
    public function voir(): void
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $annonce = $id ? (new Annonce())->detail($id) : null;
        if (!$annonce) {
            http_response_code(404);
            ajouterFlash('Annonce introuvable.', 'erreur');
            require __DIR__ . '/../views/introuvable.php';
            return;
        }
        $avisModele = new Avis();
        $avis = $avisModele->pourAnnonce($id);
        $noteProprietaire = $avisModele->noteMoyenne((int) $annonce['id_proprietaire']);
        $utilisateur = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        $aujourdHui = (new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d');
        $estProprietaire = $utilisateur === (int) $annonce['id_proprietaire'];
        $peutPostuler = $annonce['statut'] === 'ouverte' && $annonce['date_fin'] >= $aujourdHui && !$estProprietaire;
        $peutModifier = (new Annonce())->estModifiable($annonce, $utilisateur);
        // Favoris : bouton pour un utilisateur connecté qui n'est pas le propriétaire ; compteur pour le propriétaire.
        $favoris = new Favori();
        $estFavori = $utilisateur && !$estProprietaire && $favoris->existe($utilisateur, $id);
        $interesses = $estProprietaire ? $favoris->compterPourAnnonce($id) : null;
        require __DIR__ . '/../views/annonce.php';
    }
}
