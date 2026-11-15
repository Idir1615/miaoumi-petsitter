<?php

/**
 * PhotoUploader : envoi sécurisé d'une photo (lot A).
 *
 * Utilisation (dans un contrôleur) :
 *     try {
 *         $nom = PhotoUploader::enregistrer($_FILES['photo']);   // ex. « 3f9a…c1.jpg »
 *     } catch (RuntimeException $exception) {
 *         $erreurs['photo'] = $exception->getMessage();           // message affichable tel quel
 *     }
 *     PhotoUploader::supprimer($ancienNom);                      // efface l'ancien fichier
 *
 * Rien de ce qu'envoie l'utilisateur n'est cru : ni le nom, ni l'extension, ni la taille annoncée.
 * Le type est lu dans le contenu (finfo), seuls JPEG, PNG et WebP sont acceptés, 2 Mo et
 * 4 000 pixels de côté au maximum, et le fichier reçoit un nom aléatoire choisi par le serveur.
 * On stocke en base uniquement le nom renvoyé, jamais un chemin.
 * Le second paramètre (facultatif) choisit le sous-dossier de public/uploads/ : 'annonces' ou 'profils'.
 */
final class PhotoUploader
{
    public const TAILLE_MAX = 2 * 1024 * 1024; // 2 Mo
    public const COTE_MAX = 4000;              // pixels

    // Liste blanche : type réel détecté => extension donnée au fichier enregistré.
    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const DOSSIERS = ['annonces', 'profils'];

    // Contrôle et enregistre la photo ; renvoie le nom du fichier ou lève une RuntimeException.
    public static function enregistrer(array $fichier, string $dossier = 'annonces'): string
    {
        // 1. Le code d'erreur de l'envoi, rempli par PHP.
        $erreur = $fichier['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($erreur !== UPLOAD_ERR_OK) {
            $messages = [
                UPLOAD_ERR_INI_SIZE => 'La photo est trop lourde (2 Mo maximum).',
                UPLOAD_ERR_FORM_SIZE => 'La photo est trop lourde (2 Mo maximum).',
                UPLOAD_ERR_PARTIAL => 'L’envoi de la photo a été interrompu. Réessaie.',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier choisi.',
            ];
            throw new RuntimeException($messages[$erreur] ?? 'La photo n’a pas pu être reçue par le serveur. Réessaie plus tard.');
        }
        $temporaire = $fichier['tmp_name'] ?? '';
        if (!is_string($temporaire) || !is_uploaded_file($temporaire)) {
            throw new RuntimeException('Le fichier reçu n’est pas un envoi valide.');
        }

        // 2. La taille réelle sur le disque, pas $fichier['size'] envoyé par le navigateur.
        if (filesize($temporaire) > self::TAILLE_MAX) {
            throw new RuntimeException('La photo dépasse 2 Mo.');
        }

        // 3. Le type réel, lu dans le contenu du fichier, comparé à la liste blanche.
        $type = (new finfo(FILEINFO_MIME_TYPE))->file($temporaire);
        if (!isset(self::TYPES[$type])) {
            throw new RuntimeException('Format refusé : choisis une image JPEG, PNG ou WebP.');
        }

        // 4. L'image doit être lisible et pas démesurée.
        $dimensions = @getimagesize($temporaire);
        if (!$dimensions) {
            throw new RuntimeException('Ce fichier n’est pas une image lisible.');
        }
        if ($dimensions[0] > self::COTE_MAX || $dimensions[1] > self::COTE_MAX) {
            throw new RuntimeException('L’image dépasse 4 000 pixels de côté.');
        }

        // 5. Nom choisi par le serveur : 32 caractères hexadécimaux + extension du type détecté.
        $nom = bin2hex(random_bytes(16)) . '.' . self::TYPES[$type];
        if (!move_uploaded_file($temporaire, self::dossier($dossier) . '/' . $nom)) {
            throw new RuntimeException('La photo n’a pas pu être enregistrée.');
        }
        self::retirerMetadonnees(self::dossier($dossier) . '/' . $nom, $type);
        return $nom;
    }

    // Efface un fichier à partir de son nom ; ne fait rien s'il n'existe plus.
    public static function supprimer(string $nom, string $dossier = 'annonces'): void
    {
        // basename() garde seulement le nom : « ../../config/config.php » ne peut pas sortir du dossier.
        $nom = basename($nom);
        if (!self::nomValide($nom)) {
            return;
        }
        $chemin = self::dossier($dossier) . '/' . $nom;
        if (is_file($chemin)) {
            unlink($chemin);
        }
    }

    // Adresse publique d'une photo enregistrée, ou null si le nom n'a pas la forme attendue.
    public static function url(?string $nom, string $dossier = 'annonces'): ?string
    {
        return $nom !== null && self::nomValide($nom) && in_array($dossier, self::DOSSIERS, true)
            ? 'uploads/' . $dossier . '/' . $nom
            : null;
    }

    private static function nomValide(string $nom): bool
    {
        return (bool) preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $nom);
    }

    private static function dossier(string $dossier): string
    {
        if (!in_array($dossier, self::DOSSIERS, true)) {
            throw new InvalidArgumentException('Dossier de photos inconnu.');
        }
        $chemin = __DIR__ . '/../../public/uploads/' . $dossier;
        if (!is_dir($chemin) && !mkdir($chemin, 0755, true) && !is_dir($chemin)) {
            throw new RuntimeException('Le dossier des photos n’a pas pu être créé.');
        }
        return $chemin;
    }

    // RGPD : si l'extension GD est active, l'image est recréée dans le même format, ce qui efface
    // ses métadonnées (position GPS, appareil, date). Sans GD, le fichier contrôlé est gardé tel quel.
    private static function retirerMetadonnees(string $chemin, string $type): void
    {
        $lecture = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'][$type];
        $ecriture = ['image/jpeg' => 'imagejpeg', 'image/png' => 'imagepng', 'image/webp' => 'imagewebp'][$type];
        if (!function_exists($lecture) || !function_exists($ecriture)) {
            return;
        }
        $image = @$lecture($chemin);
        if (!$image) {
            return;
        }
        // L'orientation d'une photo de téléphone est une métadonnée : on l'applique avant de la perdre.
        if ($type === 'image/jpeg' && function_exists('exif_read_data')) {
            $angle = [3 => 180, 6 => -90, 8 => 90][@exif_read_data($chemin)['Orientation'] ?? 1] ?? 0;
            if ($angle !== 0 && ($tournee = imagerotate($image, $angle, 0))) {
                imagedestroy($image);
                $image = $tournee;
            }
        }
        if ($type !== 'image/jpeg') {
            imagesavealpha($image, true); // garder la transparence des PNG et WebP
        }
        $type === 'image/png' ? $ecriture($image, $chemin) : $ecriture($image, $chemin, 85);
        imagedestroy($image);
    }
}
