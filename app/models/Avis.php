<?php

class Avis
{
    public function pourAnnonce(int $annonce): array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT avis.*, auteur.nom AS auteur, cible.nom AS cible
             FROM avis JOIN utilisateurs auteur ON auteur.id = avis.id_auteur
             JOIN utilisateurs cible ON cible.id = avis.id_cible
             WHERE avis.id_annonce = ? ORDER BY avis.id DESC'
        );
        $requete->execute([$annonce]);
        return $requete->fetchAll();
    }

    // Avis reçus par un utilisateur, pour son profil public.
    public function recus(int $utilisateur): array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT avis.*, auteur.nom AS auteur, annonces.titre
             FROM avis JOIN utilisateurs auteur ON auteur.id = avis.id_auteur
             JOIN annonces ON annonces.id = avis.id_annonce
             WHERE avis.id_cible = ? ORDER BY avis.id DESC'
        );
        $requete->execute([$utilisateur]);
        return $requete->fetchAll();
    }

    public function destinataires(array $annonce, int $auteur): array
    {
        if ($annonce['date_fin'] >= (new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d')) {
            return [];
        }

        $requete = Database::getConnection()->prepare(
            "SELECT DISTINCT u.id, u.nom FROM candidatures c
             JOIN utilisateurs u ON u.id = CASE WHEN ? = ? THEN c.id_gardien ELSE ? END
             WHERE c.id_annonce = ? AND c.statut = 'acceptee'
             AND (? = ? OR c.id_gardien = ?) AND u.id <> ?
             AND NOT EXISTS (SELECT 1 FROM avis v WHERE v.id_annonce = c.id_annonce
                 AND v.id_auteur = ? AND v.id_cible = u.id)"
        );
        $proprietaire = (int) $annonce['id_proprietaire'];
        $requete->execute([$auteur, $proprietaire, $proprietaire, $annonce['id'],
            $auteur, $proprietaire, $auteur, $auteur, $auteur]);
        return $requete->fetchAll();
    }

    public function creer(int $annonce, int $auteur, int $cible, int $note, string $commentaire): bool
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            // Verrouiller l'annonce sérialise les dépôts simultanés et évite les doublons.
            $requete = $pdo->prepare('SELECT * FROM annonces WHERE id = ? FOR UPDATE');
            $requete->execute([$annonce]);
            $garde = $requete->fetch();
            $autorises = $garde ? array_column($this->destinataires($garde, $auteur), 'id') : [];
            if ($note < 1 || $note > 5 || mb_strlen($commentaire) > 2000 || !in_array($cible, $autorises)) {
                $pdo->rollBack();
                return false;
            }
            $requete = $pdo->prepare('INSERT INTO avis (note, commentaire, id_annonce, id_auteur, id_cible) VALUES (?, ?, ?, ?, ?)');
            $requete->execute([$note, $commentaire, $annonce, $auteur, $cible]);
            $pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }
}
