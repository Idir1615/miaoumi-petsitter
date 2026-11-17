<?php

class FavoriController
{
    // POST ?page=basculer_favori : ajoute l'annonce aux favoris, ou la retire si elle y est déjà.
    // Les règles sont vérifiées dans l'ordre du sujet. Le jeton CSRF est contrôlé par bootstrap.php (403).
    public function basculer(): void
    {
        // 1. Connecté, méthode POST (jeton CSRF déjà vérifié pour tout POST).
        // La méthode est contrôlée d'abord : un GET reçoit toujours 405, connecté ou non.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Utilise le bouton de la fiche de l’annonce.');
        }
        $gardien = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        if (!$gardien) {
            ajouterFlash('Connecte-toi pour gérer tes favoris.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }

        // 2. L'annonce existe.
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $annonce = $id ? (new Annonce())->trouver($id) : null;
        if (!$annonce) {
            http_response_code(404);
            require __DIR__ . '/../views/introuvable.php';
            return;
        }
        $fiche = '?page=annonce&id=' . $id;

        // 3. Ce n'est pas sa propre annonce.
        if ((int) $annonce['id_proprietaire'] === $gardien) {
            ajouterFlash('Tu ne peux pas mettre ta propre annonce en favori.', 'erreur');
            header('Location: ' . $fiche, true, 303);
            return;
        }

        // 4. Déjà en favori : on la retire ; sinon : on l'ajoute.
        $favoris = new Favori();
        if ($favoris->existe($gardien, $id)) {
            $favoris->retirer($gardien, $id);
            ajouterFlash('L’annonce a été retirée de tes favoris.');
        } else {
            $favoris->ajouter($gardien, $id);
            ajouterFlash('L’annonce a été ajoutée à tes favoris.');
        }

        // 5. Retour : seulement deux destinations possibles, jamais une adresse envoyée par le formulaire.
        $retour = ($_POST['retour'] ?? '') === 'mes_favoris' ? '?page=mes_favoris' : $fiche;
        header('Location: ' . $retour, true, 303);
    }

    // GET ?page=mes_favoris : la liste des annonces mises de côté.
    public function mesFavoris(): void
    {
        $gardien = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        if (!$gardien) {
            ajouterFlash('Connecte-toi pour retrouver tes favoris.', 'erreur');
            header('Location: ?page=connexion', true, 303);
            return;
        }
        $annonces = (new Favori())->parGardien($gardien);
        require __DIR__ . '/../views/favoris.php';
    }
}
