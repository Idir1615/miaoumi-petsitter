<?php

// Image affichée pour une annonce : la photo envoyée par le propriétaire (PhotoUploader) ;
// sans photo, l'illustration (logo) du type d'animal, jamais une photo prise au hasard.

// « Chat », « chats », « Oiseaux » ou « Lapin » donnent la clé du fichier : chat, oiseau, lapin.
function cleAnimal(string $type): string
{
    $cle = mb_strtolower(trim($type));
    $cle = strtr($cle, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'û' => 'u', 'ç' => 'c']);
    $cle = preg_replace('/[^a-z]/', '', $cle);
    $cle = preg_replace('/(s|x)$/', '', $cle);
    return $cle !== '' ? $cle : 'autre';
}

// Retourne ['src' => chemin de l'image, 'photo' => true si c'est une vraie photo].
// Illustrations (Maria) : public/assets/animaux/illustrations/chat.svg, chien.svg… et autre.svg par défaut.
function photoAnimal(array $annonce): array
{
    $envoyee = PhotoUploader::url($annonce['photo'] ?? null);
    if ($envoyee !== null) {
        return ['src' => $envoyee, 'photo' => true];
    }
    $cle = cleAnimal($annonce['type_animal'] ?? '');
    $dossier = __DIR__ . '/../../public/assets/animaux/illustrations/';
    $illustration = is_file($dossier . $cle . '.svg') ? $cle : 'autre';
    return ['src' => 'assets/animaux/illustrations/' . $illustration . '.svg', 'photo' => false];
}
