# Démarrer Miaoumi avec XAMPP sur Windows

PHP exécute le site ; MySQL stocke les comptes et les annonces. Les deux doivent fonctionner. « Connexion non validée » signifiait que MySQL ne répondait pas pendant le contrôle, pas que ton schéma SQL était incorrect.

## 1. Démarrer MySQL

Ouvre `C:\xampp\xampp-control.exe` puis clique sur **Start** sur la ligne **MySQL**. La ligne doit devenir verte. Pour utiliser phpMyAdmin, démarre également **Apache**.

Si MySQL refuse de démarrer, lis son bouton **Logs** et conserve le message d’erreur. Ne supprime pas les fichiers de données pour essayer de réparer.

## 2. Importer la base une seule fois

Ouvre <http://localhost/phpmyadmin>, clique sur **Importer**, sélectionne le fichier `sql/miaoumi.sql` du projet et exécute l’importation. Il crée la base `miaoumi` et ses quatre tables.

Si les tables existent déjà, ne réimporte pas le fichier : vérifie simplement la présence de `utilisateurs`, `annonces`, `candidatures` et `avis` dans la base. Le script n’est pas prévu pour être réexécuté sur des tables existantes.

Si la base existait déjà avant l’ajout des photos (pas de colonne `photo` dans `annonces` ni `utilisateurs`), exécute une seule fois `sql/migrations/003_photos.sql` dans l’onglet **SQL** de phpMyAdmin.

Pour pouvoir envoyer des photos, ouvre `C:\xampp\php\php.ini`, retire le `;` devant la ligne `extension=gd`, enregistre, puis clique sur **Stop** puis **Start** pour Apache dans XAMPP.

## 3. Configurer le site

Chaque copie du projet a besoin de son propre fichier `config/config.php` : il n’est pas distribué par Git. Le fichier d’exemple est prérempli pour une installation XAMPP locale standard.

Vérifie aussi le nom du dossier dans l’adresse du navigateur : pour `C:\xampp\htdocs\miaoumi`, ouvre `http://localhost/miaoumi/public/?page=annonces` ; pour un dossier `miaoumi-feature`, utilise ce nom à la place. Modifier une copie sur le Bureau ne met pas automatiquement à jour celle dans `htdocs`.

Copie `config/config.example.php` vers `config/config.php`. Pour une installation XAMPP locale standard non modifiée, le contenu est généralement :

```php
<?php
return [
    'host' => '127.0.0.1',
    'dbname' => 'miaoumi',
    'user' => 'root',
    'password' => '',
];
```

Si tu as défini un mot de passe ou un autre utilisateur MySQL, utilise tes propres identifiants. Ce fichier reste sur ton ordinateur et est ignoré par Git. Ne publie jamais les identifiants.

## 4. Lancer le site

Dans le terminal PowerShell de VS Code, ouvert dans le dossier du projet :

```powershell
& C:\xampp\php\php.exe -S localhost:8000 -t public
```

Garde ce terminal ouvert et visite <http://localhost:8000/?page=inscription>. Apache n’est pas nécessaire pour ce serveur PHP, mais MySQL doit rester démarré.

## 5. Vérifier l’authentification

1. Crée un compte avec ton nom, ton e-mail et un mot de passe d’au moins huit caractères (maximum 72 octets ; les accents peuvent occuper plusieurs octets).
2. La page de connexion indique que le compte est créé.
3. Connecte-toi : la liste des annonces affiche « Bonjour » avec ton nom.
4. Clique sur **Se déconnecter** : la page de connexion réapparaît et ton nom disparaît.
5. Vérifie qu’un mauvais mot de passe est refusé et qu’une seconde inscription avec le même e-mail est refusée.

Dans phpMyAdmin, le champ `password` doit contenir un hachage, jamais le mot de passe saisi. Une liste d’annonces vide est normale tant que la base ne contient aucune annonce.

Après ce parcours réussi avec MySQL, la case « inscription / connexion fonctionne » peut être cochée.
