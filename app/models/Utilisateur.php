<?php

class Utilisateur
{
    // Valide puis enregistre un compte. Retourne les erreurs ; une liste vide signifie que le compte a été créé.
    public function creer(string $nom, string $email, string $password): array
    {
        $nom = trim($nom);
        $email = trim($email);
        $erreurs = [];
        if ($nom === '' || mb_strlen($nom) > 100) {
            $erreurs['nom'] = 'Le nom est obligatoire (100 caractères maximum).';
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 150) {
            $erreurs['email'] = 'Saisis une adresse e-mail valide (150 caractères maximum).';
        }
        // strlen compte les octets : password_hash (bcrypt) ignore tout ce qui dépasse 72 octets.
        if (strlen($password) < 8 || strlen($password) > 72) {
            $erreurs['password'] = 'Le mot de passe doit contenir entre 8 et 72 octets.';
        }
        if (!isset($erreurs['email']) && $this->trouverParEmail($email)) {
            $erreurs['email'] = 'Cette adresse e-mail est déjà utilisée.';
        }
        if ($erreurs) {
            return $erreurs;
        }

        $requete = Database::getConnection()->prepare(
            'INSERT INTO utilisateurs (nom, email, password) VALUES (?, ?, ?)'
        );
        try {
            $requete->execute([$nom, $email, password_hash($password, PASSWORD_DEFAULT)]);
        } catch (PDOException $exception) {
            // 1062 = doublon sur la colonne UNIQUE email (deux inscriptions au même moment).
            if (($exception->errorInfo[1] ?? null) === 1062) {
                return ['email' => 'Cette adresse e-mail est déjà utilisée.'];
            }
            throw $exception;
        }
        return [];
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
