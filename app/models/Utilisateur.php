<?php

class Utilisateur
{
    public function creer(string $nom, string $email, string $password): void
    {
        $requete = Database::getConnection()->prepare(
            'INSERT INTO utilisateurs (nom, email, password) VALUES (?, ?, ?)'
        );
        $requete->execute([$nom, $email, password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function trouverParEmail(string $email): ?array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT id, nom, password FROM utilisateurs WHERE email = ?'
        );
        $requete->execute([$email]);
        return $requete->fetch() ?: null;
    }
}
