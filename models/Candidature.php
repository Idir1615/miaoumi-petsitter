<?php

// Consultation des candidatures ; les actions restent à développer par Ama.
class Candidature
{
    public function pourGardien(int $gardien): array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT c.*, a.titre, a.type_animal, a.date_debut, a.date_fin
             FROM candidatures c JOIN annonces a ON a.id = c.id_annonce
             WHERE c.id_gardien = ? ORDER BY c.id DESC'
        );
        $requete->execute([$gardien]);
        return $requete->fetchAll();
    }

    public function pourProprietaire(int $proprietaire, int $annonce): array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT c.*, u.nom FROM candidatures c
             JOIN annonces a ON a.id = c.id_annonce JOIN utilisateurs u ON u.id = c.id_gardien
             WHERE a.id_proprietaire = ? AND a.id = ? ORDER BY c.id DESC'
        );
        $requete->execute([$proprietaire, $annonce]);
        return $requete->fetchAll();
    }
}
