<?php

// Photos des animaux, déposées dans public/assets/animaux/ sous la forme
// chat1.jpg, chat2.jpg, chien1.jpg... (voir le README de ce dossier).

// « Chat », « chats », « Oiseaux » ou « Lapin » donnent la clé du nom de fichier : chat, oiseau, lapin.
function cleAnimal(string $type): string
{
    $cle = mb_strtolower(trim($type));
    $cle = strtr($cle, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'û' => 'u', 'ç' => 'c']);
    $cle = preg_replace('/[^a-z]/', '', $cle);
    $cle = preg_replace('/(s|x)$/', '', $cle);
    return $cle !== '' ? $cle : 'autre';
}

// Position d'une annonce parmi toutes les annonces du même type d'animal, triées par id.
function rangAnnonce(array $annonce): int
{
    static $idsParType = []; // une seule requête par type d'animal et par page
    $type = $annonce['type_animal'] ?? '';
    if (!isset($idsParType[$type])) {
        $requete = Database::getConnection()->prepare('SELECT id FROM annonces WHERE type_animal = ? ORDER BY id');
        $requete->execute([$type]);
        $idsParType[$type] = array_map('intval', $requete->fetchAll(PDO::FETCH_COLUMN));
    }
    $rang = array_search((int) ($annonce['id'] ?? 0), $idsParType[$type], true);
    return $rang === false ? 0 : $rang;
}

// Retourne ['src' => chemin de l'image, 'photo' => true si c'est une vraie photo].
function photoAnimal(array $annonce): array
{
    static $fichiers = null;
    $dossier = __DIR__ . '/../../public/assets/animaux/';
    // Lecture du dossier une seule fois par page, même s'il y a plusieurs annonces.
    $fichiers ??= is_dir($dossier) ? scandir($dossier) : [];

    $cle = cleAnimal($annonce['type_animal'] ?? '');
    $photos = [];
    foreach ($fichiers as $fichier) {
        if (preg_match('/^' . $cle . '(\d+)\.(jpe?g|png|webp|avif|jfif)$/i', $fichier, $morceaux)) {
            $photos[(int) $morceaux[1]] = $fichier;
        }
    }
    if ($photos) {
        ksort($photos);
        $photos = array_values($photos);
        // Sur une même page, chaque nouvelle annonce d'un type prend la photo suivante :
        // deux chats affichés ensemble n'ont jamais la même image (s'il y a 2 photos ou plus).
        // La première annonce du type part de son rang, pour rester stable d'une page à l'autre.
        static $suivante = [];  // prochain numéro de photo, par type
        static $attribuee = []; // numéro déjà donné à chaque annonce de la page
        $id = (int) ($annonce['id'] ?? 0);
        if (!isset($attribuee[$id])) {
            $attribuee[$id] = $suivante[$cle] ?? rangAnnonce($annonce);
            $suivante[$cle] = $attribuee[$id] + 1;
        }
        $choisie = $photos[$attribuee[$id] % count($photos)];
        return ['src' => 'assets/animaux/' . rawurlencode($choisie), 'photo' => true];
    }

    $illustration = is_file($dossier . 'illustrations/' . $cle . '.svg') ? $cle : 'autre';
    return ['src' => 'assets/animaux/illustrations/' . $illustration . '.svg', 'photo' => false];
}