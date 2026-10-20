<?php

class Annonce
{
    // Retourne les erreurs ; une liste vide signifie que l'annonce a été créée.
    public function creer(array $donnees, int $proprietaire): array
    {
        $erreurs = [];
        foreach (['titre' => 150, 'type_animal' => 100] as $champ => $maximum) {
            if (!is_string($donnees[$champ] ?? null) || trim($donnees[$champ]) === '' || mb_strlen($donnees[$champ]) > $maximum) {
                $erreurs[$champ] = 'Ce champ est obligatoire (' . $maximum . ' caractères maximum).';
            }
        }
        $fuseau = new DateTimeZone('Europe/Paris');
        $dates = [];
        foreach (['date_debut', 'date_fin'] as $champ) {
            $valeur = $donnees[$champ] ?? null;
            $date = is_string($valeur) ? DateTimeImmutable::createFromFormat('!Y-m-d', $valeur, $fuseau) : false;
            if (!$date || $date->format('Y-m-d') !== $valeur || $date->format('Y') < '1000') {
                $erreurs[$champ] = 'Saisis une date valide.';
            } else {
                $dates[$champ] = $date;
            }
        }
        if (isset($dates['date_debut']) && $dates['date_debut'] < new DateTimeImmutable('today', $fuseau)) {
            $erreurs['date_debut'] = 'La garde doit commencer aujourd’hui ou plus tard.';
        }
        if (isset($dates['date_debut'], $dates['date_fin']) && $dates['date_fin'] < $dates['date_debut']) {
            $erreurs['date_fin'] = 'La fin ne peut pas précéder le début.';
        }
        if (!is_string($donnees['description'] ?? null) || mb_strlen($donnees['description']) > 5000) {
            $erreurs['description'] = 'La description est limitée à 5 000 caractères.';
        }
        if ($proprietaire < 1) {
            $erreurs['general'] = 'Connecte-toi pour créer une annonce.';
        }
        if ($erreurs) {
            return $erreurs;
        }
        $pdo = Database::getConnection();
        $utilisateur = $pdo->prepare('SELECT id FROM utilisateurs WHERE id = ?');
        $utilisateur->execute([$proprietaire]);
        if (!$utilisateur->fetch()) {
            return ['general' => 'Ce compte n’existe plus. Reconnecte-toi.'];
        }
        $requete = $pdo->prepare(
            "INSERT INTO annonces (titre, type_animal, date_debut, date_fin, description, statut, id_proprietaire)
             VALUES (?, ?, ?, ?, ?, 'ouverte', ?)"
        );
        $requete->execute([trim($donnees['titre']), trim($donnees['type_animal']),
            $donnees['date_debut'], $donnees['date_fin'], trim($donnees['description']), $proprietaire]);
        return [];
    }

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
