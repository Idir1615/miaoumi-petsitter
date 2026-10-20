<?php

class Annonce
{
    public function toutes(): array
    {
        return Database::getConnection()
            ->query('SELECT * FROM annonces ORDER BY id DESC')
            ->fetchAll();
    }
}
