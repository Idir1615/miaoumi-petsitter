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

    // Vrai si la migration 003 (colonne photo) a été appliquée sur cette base.
    public function photoDisponible(): bool
    {
        return (bool) Database::getConnection()->query("SHOW COLUMNS FROM utilisateurs LIKE 'photo'")->fetch();
    }

    // Informations publiques d'un compte (sans e-mail ni mot de passe), ou null.
    public function trouver(int $id): ?array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT id, nom' . ($this->photoDisponible() ? ', photo' : '') . ' FROM utilisateurs WHERE id = ?'
        );
        $requete->execute([$id]);
        return $requete->fetch() ?: null;
    }

    // Remplace (ou retire avec null) la photo de profil et renvoie l'ancien fichier à effacer.
    public function changerPhoto(int $id, ?string $photo): ?string
    {
        $ancienne = $this->trouver($id)['photo'] ?? null;
        $requete = Database::getConnection()->prepare('UPDATE utilisateurs SET photo = ? WHERE id = ?');
        $requete->execute([$photo, $id]);
        return $ancienne;
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
