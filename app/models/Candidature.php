<?php

class Candidature
{
    public function pourUtilisateur(int $utilisateur): array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT c.id, c.message, c.statut, c.date_candidature, c.id_gardien,
                    a.id AS id_annonce, a.titre, a.date_debut, a.date_fin,
                    a.statut AS statut_annonce, a.id_proprietaire,
                    gardien.nom AS nom_gardien, proprietaire.nom AS nom_proprietaire
             FROM candidatures c
             JOIN annonces a ON a.id = c.id_annonce
             JOIN utilisateurs gardien ON gardien.id = c.id_gardien
             JOIN utilisateurs proprietaire ON proprietaire.id = a.id_proprietaire
             WHERE c.id_gardien = ? OR a.id_proprietaire = ?
             ORDER BY c.date_candidature DESC, c.id DESC'
        );
        $requete->execute([$utilisateur, $utilisateur]);
        return $requete->fetchAll();
    }

    public function postuler(int $annonce, int $gardien, string $message): bool
    {
        $message = trim($message);
        if ($gardien < 1 || mb_strlen($message) > 2000) {
            return false;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            // Le verrou empêche deux envois simultanés de créer un doublon.
            $requete = $pdo->prepare('SELECT * FROM annonces WHERE id = ? FOR UPDATE');
            $requete->execute([$annonce]);
            $offre = $requete->fetch();

            if (!$offre || !$this->estAccessible($offre)
                || (int) $offre['id_proprietaire'] === $gardien) {
                $pdo->rollBack();
                return false;
            }

            $requete = $pdo->prepare(
                'SELECT id FROM candidatures WHERE id_annonce = ? AND id_gardien = ? LIMIT 1'
            );
            $requete->execute([$annonce, $gardien]);
            if ($requete->fetch()) {
                $pdo->rollBack();
                return false;
            }

            $requete = $pdo->prepare(
                "INSERT INTO candidatures (message, statut, id_annonce, id_gardien)
                 VALUES (?, 'en_attente', ?, ?)"
            );
            $requete->execute([$message, $annonce, $gardien]);
            $pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function decider(int $candidature, int $proprietaire, string $decision): bool
    {
        if ($candidature < 1 || $proprietaire < 1
            || !in_array($decision, ['acceptee', 'refusee'], true)) {
            return false;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $requete = $pdo->prepare(
                'SELECT c.id, c.statut, c.id_annonce, a.id_proprietaire
                 FROM candidatures c
                 JOIN annonces a ON a.id = c.id_annonce
                 WHERE c.id = ? FOR UPDATE'
            );
            $requete->execute([$candidature]);
            $ligne = $requete->fetch();

            if (!$ligne || (int) $ligne['id_proprietaire'] !== $proprietaire
                || $ligne['statut'] !== 'en_attente') {
                $pdo->rollBack();
                return false;
            }

            $requete = $pdo->prepare(
                "UPDATE candidatures SET statut = ? WHERE id = ? AND statut = 'en_attente'"
            );
            $requete->execute([$decision, $candidature]);
            $modifiee = $requete->rowCount() === 1;
            // Accepter un gardien : l'annonce devient pourvue et les autres candidatures en attente sont refusées.
            if ($modifiee && $decision === 'acceptee') {
                (new Annonce())->marquerPourvue((int) $ligne['id_annonce']);
                $pdo->prepare(
                    "UPDATE candidatures SET statut = 'refusee'
                     WHERE id_annonce = ? AND id <> ? AND statut = 'en_attente'"
                )->execute([$ligne['id_annonce'], $candidature]);
            }
            $pdo->commit();
            return $modifiee;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function estAccessible(array $annonce): bool
    {
        $aujourdHui = (new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d');
        return $annonce['statut'] === 'ouverte' && $annonce['date_fin'] >= $aujourdHui;
    }
}
