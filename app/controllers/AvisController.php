<?php

class AvisController
{
    public function index(): void
    {
        $id = filter_var($_GET['annonce'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $annonce = $id ? (new Annonce())->trouver($id) : null;
        if (!$annonce) {
            http_response_code(404);
            echo 'Annonce introuvable';
            return;
        }

        $modele = new Avis();
        $auteur = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        $erreurs = [];
        $note = '';
        $commentaire = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$auteur) {
                header('Location: ?page=connexion', true, 303);
                return;
            }
            $note = is_string($_POST['note'] ?? null) ? $_POST['note'] : '';
            $commentaire = is_string($_POST['commentaire'] ?? null) ? trim($_POST['commentaire']) : '';
            $valeur = filter_var($note, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
            $cible = filter_var($_POST['cible'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
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
                    header('Location: ?page=avis&annonce=' . $id . '&ajoute=1', true, 303);
                    return;
                }
                http_response_code(403);
                $erreurs[] = 'Cet avis n’est pas autorisé ou a déjà été déposé.';
            }
        }

        $destinataires = $auteur ? $modele->destinataires($annonce, $auteur) : [];
        $avis = $modele->pourAnnonce($id);
        require __DIR__ . '/../views/avis.php';
    }
}
