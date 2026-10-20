<?php

class CandidatureController
{
    public function index(): void
    {
        $utilisateur = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        if (!$utilisateur) {
            ajouterFlash('Connecte-toi pour consulter tes candidatures.', 'erreur');
            $this->rediriger('?page=connexion');
        }

        $candidatures = (new Candidature())->pourUtilisateur($utilisateur);
        $erreurs = [];
        require __DIR__ . '/../views/candidatures.php';
    }

    public function postuler(): void
    {
        $id = filter_var($_GET['annonce'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $utilisateur = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        if (!$utilisateur) {
            if ($id) {
                $_SESSION['annonce_apres_connexion'] = (int) $id;
            }
            ajouterFlash('Connecte-toi pour continuer ta candidature.', 'erreur');
            $this->rediriger('?page=connexion');
        }

        $annonce = $id ? (new Annonce())->trouver($id) : null;
        $erreurs = [];
        $message = '';
        $peutPostuler = false;

        if (!$annonce) {
            http_response_code(404);
            $erreurs[] = 'Cette annonce est introuvable.';
        } elseif ((int) $annonce['id_proprietaire'] === $utilisateur) {
            http_response_code(403);
            $erreurs[] = 'Tu ne peux pas postuler à ta propre annonce.';
        } elseif ($annonce['statut'] !== 'ouverte'
            || $annonce['date_fin'] < (new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d')) {
            http_response_code(403);
            $erreurs[] = 'Cette annonce n’accepte plus de candidatures.';
        } else {
            $peutPostuler = true;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$erreurs) {
            $message = is_string($_POST['message'] ?? null) ? trim($_POST['message']) : '';
            if (mb_strlen($message) > 2000) {
                $erreurs[] = 'Le message ne doit pas dépasser 2 000 caractères.';
            } elseif ((new Candidature())->postuler((int) $id, $utilisateur, $message)) {
                ajouterFlash('Ta candidature a été envoyée.');
                $this->rediriger('?page=candidatures');
            } else {
                http_response_code(409);
                $erreurs[] = 'La candidature existe déjà ou l’annonce n’est plus accessible.';
            }
        } elseif ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            header('Allow: GET, POST');
            exit('Méthode non autorisée.');
        }

        require __DIR__ . '/../views/candidature-formulaire.php';
    }

    public function decision(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Utilise les boutons de décision.');
        }

        $proprietaire = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        if (!$proprietaire) {
            $this->rediriger('?page=connexion');
        }

        $id = filter_var($_POST['candidature'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $decision = is_string($_POST['decision'] ?? null) ? $_POST['decision'] : '';
        if (!$id || !in_array($decision, ['acceptee', 'refusee'], true)
            || !(new Candidature())->decider((int) $id, $proprietaire, $decision)) {
            http_response_code(403);
            $candidatures = (new Candidature())->pourUtilisateur($proprietaire);
            $erreurs = ['Tu ne peux pas modifier cette candidature, ou elle a déjà été traitée.'];
            require __DIR__ . '/../views/candidatures.php';
            return;
        }

        ajouterFlash('La décision a été enregistrée.');
        $this->rediriger('?page=candidatures');
    }

    private function rediriger(string $adresse): void
    {
        header('Location: ' . $adresse, true, 303);
        exit;
    }
}
