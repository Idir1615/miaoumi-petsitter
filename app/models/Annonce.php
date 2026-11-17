<?php

class Annonce
{
    // Liste fermée des types d'animaux : la même pour le formulaire, le filtre et la validation.
    public const TYPES = ['Chien', 'Chat', 'Lapin', 'Oiseau', 'Poisson', 'Autre'];

    public function villeDisponible(): bool
    {
        return (bool) Database::getConnection()->query("SHOW COLUMNS FROM annonces LIKE 'ville'")->fetch();
    }

    // Vrai si la migration 003 (colonne photo) a été appliquée sur cette base.
    public function photoDisponible(): bool
    {
        return (bool) Database::getConnection()->query("SHOW COLUMNS FROM annonces LIKE 'photo'")->fetch();
    }

    public function rechercher(string $recherche, string $animal, ?int $proprietaire = null, string $ville = ''): array
    {
        $conditions = [];
        $parametres = [];
        if ($recherche !== '') {
            $conditions[] = '(titre LIKE ? OR description LIKE ?)';
            $parametres[] = '%' . $recherche . '%';
            $parametres[] = '%' . $recherche . '%';
        }
        if ($animal !== '') {
            $conditions[] = 'type_animal = ?';
            $parametres[] = $animal;
        }
        if ($ville !== '' && $this->villeDisponible()) {
            $conditions[] = 'ville LIKE ?';
            $parametres[] = '%' . $ville . '%';
        }
        if ($proprietaire !== null) {
            $conditions[] = 'id_proprietaire = ?';
            $parametres[] = $proprietaire;
        } else {
            $conditions[] = "statut = 'ouverte'";
        }
        // Jointure pour le nom du propriétaire ; la sous-requête calcule sa note moyenne reçue.
        $sql = 'SELECT annonces.*, u.nom AS nom_proprietaire,
                    (SELECT ROUND(AVG(v.note), 1) FROM avis v WHERE v.id_cible = annonces.id_proprietaire) AS note_proprietaire
                FROM annonces JOIN utilisateurs u ON u.id = annonces.id_proprietaire'
            . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY annonces.id DESC';
        $requete = Database::getConnection()->prepare($sql);
        $requete->execute($parametres);
        return $requete->fetchAll();
    }

    // Retourne les erreurs ; une liste vide signifie que l'annonce a été créée.
    // Règles communes à la création et à la modification ; renvoie les erreurs par champ.
    // En modification, $debutActuel permet de garder une date de début déjà passée sans la changer.
    public function valider(array $donnees, ?string $debutActuel = null): array
    {
        $erreurs = [];
        if (!is_string($donnees['titre'] ?? null) || trim($donnees['titre']) === '' || mb_strlen($donnees['titre']) > 150) {
            $erreurs['titre'] = 'Ce champ est obligatoire (150 caractères maximum).';
        }
        // Le type doit faire partie de la liste : une valeur forgée hors du menu est refusée.
        if (!in_array($donnees['type_animal'] ?? null, self::TYPES, true)) {
            $erreurs['type_animal'] = 'Choisis un type d’animal dans la liste.';
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
        if (isset($dates['date_debut']) && $donnees['date_debut'] !== $debutActuel
            && $dates['date_debut'] < new DateTimeImmutable('today', $fuseau)) {
            $erreurs['date_debut'] = 'La garde doit commencer aujourd’hui ou plus tard.';
        }
        if (isset($dates['date_debut'], $dates['date_fin']) && $dates['date_fin'] < $dates['date_debut']) {
            $erreurs['date_fin'] = 'La fin ne peut pas précéder le début.';
        }
        if (!is_string($donnees['description'] ?? null) || mb_strlen($donnees['description']) > 5000) {
            $erreurs['description'] = 'La description est limitée à 5 000 caractères.';
        }
        $ville = $donnees['ville'] ?? '';
        if (!is_string($ville) || mb_strlen($ville) > 100) {
            $erreurs['ville'] = 'La ville est limitée à 100 caractères.';
        }
        return $erreurs;
    }

    public function creer(array $donnees, int $proprietaire): array
    {
        $erreurs = $this->valider($donnees);
        $ville = $donnees['ville'] ?? '';
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
        $colonnes = ['titre', 'type_animal', 'date_debut', 'date_fin', 'description', 'id_proprietaire'];
        $valeurs = [trim($donnees['titre']), trim($donnees['type_animal']),
            $donnees['date_debut'], $donnees['date_fin'], trim($donnees['description']), $proprietaire];
        if ($this->villeDisponible()) {
            $colonnes[] = 'ville';
            $valeurs[] = trim($ville);
        }
        // La photo a déjà été contrôlée par PhotoUploader ; on ne garde que son nom.
        if (!empty($donnees['photo']) && $this->photoDisponible()) {
            $colonnes[] = 'photo';
            $valeurs[] = $donnees['photo'];
        }
        $requete = $pdo->prepare(
            'INSERT INTO annonces (' . implode(', ', $colonnes) . ", statut) VALUES ("
            . implode(', ', array_fill(0, count($valeurs), '?')) . ", 'ouverte')"
        );
        $requete->execute($valeurs);
        return [];
    }

    // Vrai si l'utilisateur peut modifier ou supprimer l'annonce : il en est le propriétaire et elle est encore ouverte.
    public function estModifiable(array $annonce, int $utilisateur): bool
    {
        return (int) $annonce['id_proprietaire'] === $utilisateur && $annonce['statut'] === 'ouverte';
    }

    // Met à jour les champs d'une annonce ouverte de son propriétaire ; renvoie les erreurs, vide si c'est fait.
    public function modifier(int $annonce, int $proprietaire, array $donnees): array
    {
        $garde = $annonce > 0 ? $this->trouver($annonce) : null;
        if (!$garde || !$this->estModifiable($garde, $proprietaire)) {
            return ['general' => 'Seul le propriétaire peut modifier une annonce encore ouverte.'];
        }
        $erreurs = $this->valider($donnees, $garde['date_debut']);
        if ($erreurs) {
            return $erreurs;
        }
        $colonnes = ['titre = ?', 'type_animal = ?', 'date_debut = ?', 'date_fin = ?', 'description = ?'];
        $valeurs = [trim($donnees['titre']), trim($donnees['type_animal']),
            $donnees['date_debut'], $donnees['date_fin'], trim($donnees['description'])];
        if ($this->villeDisponible()) {
            $colonnes[] = 'ville = ?';
            $valeurs[] = trim($donnees['ville'] ?? '');
        }
        // Les conditions de droit sont répétées dans le WHERE, comme pour cloturer().
        $valeurs[] = $annonce;
        $valeurs[] = $proprietaire;
        Database::getConnection()
            ->prepare('UPDATE annonces SET ' . implode(', ', $colonnes) . " WHERE id = ? AND id_proprietaire = ? AND statut = 'ouverte'")
            ->execute($valeurs);
        return [];
    }

    // Supprime une annonce ouverte de son propriétaire (candidatures et favoris partent en cascade).
    public function supprimer(int $annonce, int $proprietaire): bool
    {
        $requete = Database::getConnection()->prepare(
            "DELETE FROM annonces WHERE id = ? AND id_proprietaire = ? AND statut = 'ouverte'"
        );
        $requete->execute([$annonce, $proprietaire]);
        return $requete->rowCount() === 1;
    }

    // Enregistre le nom de la photo (ou NULL pour la retirer) ; le fichier est géré par PhotoUploader.
    public function definirPhoto(int $id, ?string $fichier): void
    {
        Database::getConnection()->prepare('UPDATE annonces SET photo = ? WHERE id = ?')->execute([$fichier, $id]);
    }

    // Adresse de l'image à afficher : la photo envoyée, sinon une photo ou une illustration du type d'animal.
    public function photoUrl(array $annonce): string
    {
        return photoAnimal($annonce)['src'];
    }

    // Clôture une garde ; seul son propriétaire peut le faire. Une liste vide signifie que la garde est clôturée.
    public function cloturer(int $annonce, int $proprietaire): array
    {
        $garde = $annonce > 0 ? $this->trouver($annonce) : null;
        if (!$garde) {
            return ['general' => 'Cette annonce est introuvable.'];
        }
        if ((int) $garde['id_proprietaire'] !== $proprietaire) {
            return ['general' => 'Seul le propriétaire peut clôturer cette garde.'];
        }
        if ($garde['statut'] === 'terminee') {
            return ['general' => 'Cette garde est déjà clôturée.'];
        }
        $requete = Database::getConnection()->prepare(
            "UPDATE annonces SET statut = 'terminee' WHERE id = ? AND id_proprietaire = ? AND statut <> 'terminee'"
        );
        $requete->execute([$annonce, $proprietaire]);
        return $requete->rowCount() === 1 ? [] : ['general' => 'Cette garde est déjà clôturée.'];
    }

    // Appelée quand une candidature est acceptée ; seule une annonce ouverte devient pourvue.
    public function marquerPourvue(int $annonce): bool
    {
        $requete = Database::getConnection()->prepare(
            "UPDATE annonces SET statut = 'pourvue' WHERE id = ? AND statut = 'ouverte'"
        );
        $requete->execute([$annonce]);
        return $requete->rowCount() === 1;
    }

    // Retire la photo d'une annonce de son propriétaire et renvoie le nom du fichier à effacer, ou null.
    public function retirerPhoto(int $annonce, int $proprietaire): ?string
    {
        $garde = $annonce > 0 ? $this->trouver($annonce) : null;
        if (!$garde || (int) $garde['id_proprietaire'] !== $proprietaire || empty($garde['photo'])) {
            return null;
        }
        $requete = Database::getConnection()->prepare('UPDATE annonces SET photo = NULL WHERE id = ? AND id_proprietaire = ?');
        $requete->execute([$annonce, $proprietaire]);
        return $requete->rowCount() === 1 ? $garde['photo'] : null;
    }

    // Annonce avec le nom du propriétaire, pour la page de détail.
    public function detail(int $id): ?array
    {
        $requete = Database::getConnection()->prepare(
            'SELECT annonces.*, u.nom AS nom_proprietaire,
                    (SELECT COUNT(*) FROM candidatures c WHERE c.id_annonce = annonces.id) AS nombre_candidatures
             FROM annonces JOIN utilisateurs u ON u.id = annonces.id_proprietaire
             WHERE annonces.id = ?'
        );
        $requete->execute([$id]);
        return $requete->fetch() ?: null;
    }

    // Chiffres affichés sur la page d'accueil.
    public function statistiques(): array
    {
        // Sans la migration 001, la colonne ville n'existe pas : on compte alors 0 ville.
        $villes = $this->villeDisponible() ? "COUNT(DISTINCT NULLIF(ville, ''))" : '0';
        return Database::getConnection()->query(
            "SELECT SUM(statut = 'ouverte') AS ouvertes, SUM(statut = 'terminee') AS terminees,
                    $villes AS villes
             FROM annonces"
        )->fetch();
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
