<?php

class AvisController
{
    public function index(): void
    {
        $id = filter_var($_GET['annonce'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $annonce = $id ? (new Annonce())->trouver($id) : null;
        if (!$annonce) {
            http_response_code(404);
            // Le header lit les messages flash : celui-ci s'affiche sur la page 404 commune.
            ajouterFlash('Annonce introuvable.', 'erreur');
            require __DIR__ . '/../views/introuvable.php';
            return;
        }

        $modele = new Avis();
        $auteur = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        $erreurs = [];
        $note = '';
        $commentaire = '';
        $cible = 0; // personne choisie dans la liste, conservée si le formulaire contient une erreur
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$auteur) {
                header('Location: ?page=connexion', true, 303);
                return;
            }
            $note = is_string($_POST['note'] ?? null) ? $_POST['note'] : '';
            $commentaire = is_string($_POST['commentaire'] ?? null) ? trim($_POST['commentaire']) : '';
            $valeur = filter_var($note, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
            $cible = (int) filter_var($_POST['cible'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$valeur) {
                $erreurs[] = 'Choisis une note de 1 à 5.';
            }
            if (!is_string($_POST['commentaire'] ?? null) || mb_strlen($commentaire) > 2000) {
                $erreurs[] = 'Le commentaire doit contenir au maximum 2 000 caractères.';
            }
            if (!$cible) {
                $erreurs[] = 'Choisis la personne concernée par ton avis.';
            }
            if (!$erreurs) {
                if ($modele->creer($id, $auteur, $cible, $valeur, $commentaire)) {
                    ajouterFlash('Ton avis a été publié. Merci !');
                    header('Location: ?page=avis&annonce=' . $id, true, 303);
                    return;
                }
                http_response_code(403);
                $erreurs[] = 'Cet avis n’est pas autorisé ou a déjà été déposé.';
            } else {
                http_response_code(422);
            }
        }

        $destinataires = $auteur ? $modele->destinataires($annonce, $auteur) : [];
        $avis = $modele->pourAnnonce($id);
        $gardeFinie = $modele->gardeFinie($annonce);
        require __DIR__ . '/../views/avis.php';
    }
}
