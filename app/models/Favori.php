<?php

// Favoris : relation plusieurs-à-plusieurs entre un gardien (utilisateur) et des annonces.
// La table favoris a une clé primaire composée (id_gardien, id_annonce) : impossible d'ajouter deux fois la même annonce.
class Favori
{
    public function existe(int $gardien, int $annonce): bool
    {
        $requete = Database::getConnection()->prepare('SELECT 1 FROM favoris WHERE id_gardien = ? AND id_annonce = ?');
        $requete->execute([$gardien, $annonce]);
        return (bool) $requete->fetchColumn();
    }

    // Ajoute l'annonce aux favoris, sauf si c'est la sienne ou si elle y est déjà ; vrai si une ligne a été créée.
    public function ajouter(int $gardien, int $annonce): bool
    {
        // INSERT … SELECT : la ligne n'est insérée que si l'annonce existe et n'appartient pas au gardien.
        // IGNORE : un doublon (clé primaire déjà présente) ne provoque pas d'erreur, rien n'est inséré.
        $requete = Database::getConnection()->prepare(
            'INSERT IGNORE INTO favoris (id_gardien, id_annonce)
             SELECT ?, id FROM annonces WHERE id = ? AND id_proprietaire <> ?'
        );
        $requete->execute([$gardien, $annonce, $gardien]);
        return $requete->rowCount() === 1;
    }

    public function retirer(int $gardien, int $annonce): bool
    {
        $requete = Database::getConnection()->prepare('DELETE FROM favoris WHERE id_gardien = ? AND id_annonce = ?');
        $requete->execute([$gardien, $annonce]);
        return $requete->rowCount() === 1;
    }

    // Annonces mises de côté par un gardien, avec le nom du propriétaire ; le dernier favori ajouté en premier.
    public function parGardien(int $gardien): array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT annonces.*, u.nom AS nom_proprietaire, f.date_ajout
             FROM favoris f
             JOIN annonces ON annonces.id = f.id_annonce
             JOIN utilisateurs u ON u.id = annonces.id_proprietaire
             WHERE f.id_gardien = ?
             ORDER BY f.date_ajout DESC'
        );
        $requete->execute([$gardien]);
        return $requete->fetchAll();
    }

    // Nombre de gardiens qui ont mis l'annonce en favori (affiché à son propriétaire seulement).
    public function compterPourAnnonce(int $annonce): int
    {
        $requete = Database::getConnection()->prepare('SELECT COUNT(*) FROM favoris WHERE id_annonce = ?');
        $requete->execute([$annonce]);
        return (int) $requete->fetchColumn();
    }
}
