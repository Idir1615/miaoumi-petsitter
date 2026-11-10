<?php

// Ajouter avant une redirection ; le header affiche et efface les messages.
function ajouterFlash(string $message, string $type = 'succes'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function lireFlash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}
