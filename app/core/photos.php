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
