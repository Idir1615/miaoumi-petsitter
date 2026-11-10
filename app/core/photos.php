<?php

// Photos envoyées par les utilisateurs : contrôle, nettoyage (RGPD) et enregistrement dans public/uploads/.
const PHOTO_TAILLE_MAX = 5 * 1024 * 1024;
const PHOTO_COTE_MAX = 1200;

// Renvoie ['nom' => fichier] si la photo est enregistrée, ['erreur' => message] si elle est refusée,
// ou [] si aucun fichier n'a été choisi.
function enregistrerPhoto(?array $fichier, string $dossier): array
{
    if (!$fichier || ($fichier['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [];
    }
    if ($fichier['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($fichier['tmp_name'])) {
        return ['erreur' => 'La photo n’a pas pu être envoyée. Réessaie avec une image de 5 Mo maximum.'];
    }
    if ($fichier['size'] > PHOTO_TAILLE_MAX) {
        return ['erreur' => 'La photo dépasse 5 Mo.'];
    }
    // Le type est lu dans le contenu du fichier, pas dans son nom ni dans ce qu'annonce le navigateur.
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
    $dimensions = @getimagesize($fichier['tmp_name']);
    if (!in_array($type, ['image/jpeg', 'image/png'], true) || !$dimensions
        || $dimensions[0] > 8000 || $dimensions[1] > 8000) {
        return ['erreur' => 'Choisis une image JPEG ou PNG (8 000 pixels de côté maximum).'];
    }
    $image = $type === 'image/jpeg' ? @imagecreatefromjpeg($fichier['tmp_name']) : @imagecreatefrompng($fichier['tmp_name']);
    if (!$image) {
        return ['erreur' => 'Cette image est illisible.'];
    }
    $image = redresserPhoto($image, $fichier['tmp_name'], $type);

    // Recréer l'image supprime toutes ses métadonnées (position GPS, appareil, date de prise de vue).
    $largeur = imagesx($image);
    $hauteur = imagesy($image);
    $ratio = min(1, PHOTO_COTE_MAX / max($largeur, $hauteur));
    $copie = imagecreatetruecolor(max(1, (int) round($largeur * $ratio)), max(1, (int) round($hauteur * $ratio)));
    imagefill($copie, 0, 0, imagecolorallocate($copie, 255, 255, 255));
    imagecopyresampled($copie, $image, 0, 0, 0, 0, imagesx($copie), imagesy($copie), $largeur, $hauteur);

    // Nom aléatoire : impossible de deviner l'adresse des photos des autres.
    $nom = bin2hex(random_bytes(16)) . '.jpg';
    $enregistree = imagejpeg($copie, dossierPhotos($dossier) . '/' . $nom, 85);
    imagedestroy($image);
    imagedestroy($copie);
    return $enregistree ? ['nom' => $nom] : ['erreur' => 'La photo n’a pas pu être enregistrée.'];
}

// Tourne une photo de téléphone dans le bon sens avant que l'orientation (une métadonnée) soit perdue.
function redresserPhoto(GdImage $image, string $chemin, string $type): GdImage
{
    $exif = $type === 'image/jpeg' ? @exif_read_data($chemin) : false;
    $angle = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
    if ($angle === 0) {
        return $image;
    }
    $redressee = imagerotate($image, $angle, 0);
    if (!$redressee) {
        return $image;
    }
    imagedestroy($image);
    return $redressee;
}

function dossierPhotos(string $dossier): string
{
    $chemin = __DIR__ . '/../../public/uploads/' . $dossier;
    if (!is_dir($chemin)) {
        mkdir($chemin, 0755, true);
    }
    return $chemin;
}

// Efface le fichier ; le nom est contrôlé pour ne jamais sortir du dossier des photos.
function supprimerPhoto(?string $nom, string $dossier): void
{
    if ($nom !== null && preg_match('/^[a-f0-9]{32}\.jpg$/', $nom)) {
        $fichier = dossierPhotos($dossier) . '/' . $nom;
        if (is_file($fichier)) {
            unlink($fichier);
        }
    }
}

// ----- Images par type d'animal (Maria) -----

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
// La photo envoyée par le propriétaire passe en premier ; sinon, une photo ou une illustration du type d'animal.
function photoAnimal(array $annonce): array
{
    if (!empty($annonce['photo']) && preg_match('/^[a-f0-9]{32}\.jpg$/', $annonce['photo'])) {
        return ['src' => 'uploads/annonces/' . $annonce['photo'], 'photo' => true];
    }
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
