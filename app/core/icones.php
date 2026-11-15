<?php

// Petites icônes SVG dessinées au trait, intégrées dans la page (aucun fichier ni service externe).
// Elles prennent la couleur du texte (currentColor) et sont décoratives : aria-hidden.
function icone(string $nom, int $taille = 18): string
{
    $traces = [
        'calendrier' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'valide' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'lieu' => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'etoile' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2-5.5-2.9-5.5 2.9 1-6.2L3 9.6l6.2-.9z"/>',
        'crayon' => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>',
        'personnes' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14a6.5 6.5 0 0 1 3.5 6"/>',
        'coeur' => '<path d="M12 20s-7.5-4.6-9.2-9.1C1.7 7.7 3.8 4.5 7.1 4.5c2 0 3.4 1.1 4.9 2.9 1.5-1.8 2.9-2.9 4.9-2.9 3.3 0 5.4 3.2 4.3 6.4C19.5 15.4 12 20 12 20z"/>',
        'recherche' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'filtres' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'patte' => '<circle cx="7" cy="9" r="2"/><circle cx="12" cy="6" r="2"/><circle cx="17" cy="9" r="2"/><path d="M12 12c-3 0-5.5 3.2-5.5 5.3 0 1.6 1.3 2.2 2.6 2.2 1.1 0 1.9-.6 2.9-.6s1.8.6 2.9.6c1.3 0 2.6-.6 2.6-2.2 0-2.1-2.5-5.3-5.5-5.3z"/>',
        'chevron' => '<path d="m9 6 6 6-6 6"/>',
    ];
    if (!isset($traces[$nom])) {
        return '';
    }
    return '<svg class="icone icone-' . $nom . '" width="' . $taille . '" height="' . $taille . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . $traces[$nom] . '</svg>';
}
