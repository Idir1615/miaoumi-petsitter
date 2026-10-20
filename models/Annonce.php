<?php

class Annonce
{
    public function trouver(int $id): ?array
    {
        $requete = Database::getConnection()->prepare('SELECT * FROM annonces WHERE id = ?');
        $requete->execute([$id]);
        return $requete->fetch() ?: null;
    }

    public function toutes(): array
    {
        return Database::getConnection()
            ->query('SELECT * FROM annonces ORDER BY id DESC')
            ->fetchAll();
    }
}
