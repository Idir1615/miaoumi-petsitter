<?php

class CandidatureController
{
    public function index(): void
    {
        if (!isset($_SESSION['utilisateur']['id'])) {
            ajouterFlash('Connecte-toi pour consulter tes candidatures.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }
        $id = (int) $_SESSION['utilisateur']['id'];
        $reception = ($_GET['page'] ?? '') === 'candidatures-recues';
        $annonce = null;
        if ($reception) {
            $annonceId = filter_var($_GET['annonce'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $annonce = $annonceId ? (new Annonce())->trouver($annonceId) : null;
            if (!$annonce || (int) $annonce['id_proprietaire'] !== $id) {
                http_response_code(404);
                echo 'Annonce introuvable';
                return;
            }
        }
        $modele = new Candidature();
        $candidatures = $reception ? $modele->pourProprietaire($id, (int) $annonce['id']) : $modele->pourGardien($id);
        require __DIR__ . '/../views/candidatures.php';
    }
}
