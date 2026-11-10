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

    // Derniers avis positifs (4 ou 5) avec commentaire, pour la page d'accueil.
    public function derniers(int $limite): array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT avis.*, auteur.nom AS auteur, cible.nom AS cible, annonces.titre, annonces.type_animal
             FROM avis JOIN utilisateurs auteur ON auteur.id = avis.id_auteur
             JOIN utilisateurs cible ON cible.id = avis.id_cible
             JOIN annonces ON annonces.id = avis.id_annonce
             WHERE CHAR_LENGTH(avis.commentaire) > 0 AND avis.note >= 4 ORDER BY avis.dates DESC LIMIT ?'
        );
        // LIMIT ? exige un entier : bindValue avec PDO::PARAM_INT.
        $requete->bindValue(1, $limite, PDO::PARAM_INT);
        $requete->execute();
        return $requete->fetchAll();
    }

    // Nombre d'avis et note moyenne de tout le site.
    public function statistiques(): array
    {
        return Database::getConnection()->query('SELECT COUNT(*) AS total, ROUND(AVG(note), 1) AS moyenne FROM avis')->fetch();
    }

    // Note moyenne reçue par un utilisateur (une décimale), ou null s'il n'a encore aucun avis.
    public function noteMoyenne(int $utilisateur): ?float
    {
        $requete = Database::getConnection()->prepare('SELECT AVG(note) FROM avis WHERE id_cible = ?');
        $requete->execute([$utilisateur]);
        $moyenne = $requete->fetchColumn();
        // AVG renvoie NULL quand aucune ligne ne correspond.
        return $moyenne === null ? null : round((float) $moyenne, 1);
    }

    // La garde est finie si son propriétaire l'a clôturée ou si sa date de fin est passée.
    public function gardeFinie(array $annonce): bool
    {
        $aujourdHui = (new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d');
        return $annonce['statut'] === 'terminee' || $annonce['date_fin'] < $aujourdHui;
    }

    public function destinataires(array $annonce, int $auteur): array
    {
        if (!$this->gardeFinie($annonce)) {
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
