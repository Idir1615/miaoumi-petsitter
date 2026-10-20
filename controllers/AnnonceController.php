<?php

class AnnonceController
{
    public function index(): void
    {
        $modele = new Annonce();
        $annonces = $modele->toutes();
        require __DIR__ . '/../views/annonces.php';
    }
}
