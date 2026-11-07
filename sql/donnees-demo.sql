-- Jeu de données de démonstration Miaoumi.
-- À importer APRÈS « sql/miaoumi.sql », dans la base miaoumi (phpMyAdmin > Importer).
-- Comptes fictifs, mot de passe commun : demo1234
-- Relançable : les comptes de démo existants sont supprimés d'abord (leurs annonces,
-- candidatures et avis partent avec eux grâce à ON DELETE CASCADE).
-- Les dates sont calculées à partir du jour de l'import pour garder des états cohérents.

USE miaoumi;

DELETE FROM utilisateurs WHERE email IN (
    'claire@demo.miaoumi.fr', 'karim@demo.miaoumi.fr', 'sofia@demo.miaoumi.fr', 'lucas@demo.miaoumi.fr'
);

-- Comptes : un même compte peut confier et garder.
INSERT INTO utilisateurs (nom, email, password) VALUES
    ('Claire', 'claire@demo.miaoumi.fr', '$2y$10$jaU23I4ejiVJtDed1EGoi.nPiBOJFxSivTfwT432aufz3PoViBnVG');
SET @claire = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES
    ('Karim', 'karim@demo.miaoumi.fr', '$2y$10$jaU23I4ejiVJtDed1EGoi.nPiBOJFxSivTfwT432aufz3PoViBnVG');
SET @karim = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES
    ('Sofia', 'sofia@demo.miaoumi.fr', '$2y$10$jaU23I4ejiVJtDed1EGoi.nPiBOJFxSivTfwT432aufz3PoViBnVG');
SET @sofia = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES
    ('Lucas', 'lucas@demo.miaoumi.fr', '$2y$10$jaU23I4ejiVJtDed1EGoi.nPiBOJFxSivTfwT432aufz3PoViBnVG');
SET @lucas = LAST_INSERT_ID();

-- Annonce ouverte : deux candidatures en attente, à accepter ou refuser en démo.
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Garde de Moka pendant mes vacances', 'Chat', 'Paris',
     CURDATE() + INTERVAL 10 DAY, CURDATE() + INTERVAL 17 DAY,
     'Moka est une chatte calme de 4 ans. Deux visites par jour : croquettes, eau fraîche et litière.',
     'ouverte', @claire);
SET @moka = LAST_INSERT_ID();

-- Annonce pourvue : une candidature acceptée, une refusée.
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Promenades pour Rex', 'Chien', 'Lyon',
     CURDATE() + INTERVAL 3 DAY, CURDATE() + INTERVAL 8 DAY,
     'Rex, berger australien très joueur, a besoin de deux grandes promenades par jour.',
     'pourvue', @lucas);
SET @rex = LAST_INSERT_ID();

-- Annonce terminée : avis déposés dans les deux sens.
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Week-end avec Plume', 'Lapin', 'Paris',
     CURDATE() - INTERVAL 20 DAY, CURDATE() - INTERVAL 18 DAY,
     'Plume vit en liberté dans l’appartement. Foin à volonté et légumes frais le soir.',
     'terminee', @claire);
SET @plume = LAST_INSERT_ID();

-- Annonce terminée : un seul avis déposé, l'autre reste à laisser pendant la démo.
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Nourrir Bulle et Caillou', 'Poissons', 'Marseille',
     CURDATE() - INTERVAL 40 DAY, CURDATE() - INTERVAL 35 DAY,
     'Deux poissons rouges à nourrir une fois par jour, rien de plus.',
     'terminee', @lucas);
SET @bulle = LAST_INSERT_ID();

INSERT INTO candidatures (message, statut, date_candidature, id_annonce, id_gardien) VALUES
    ('Bonjour Claire, j’ai gardé plusieurs chats et j’habite à deux rues.', 'en_attente', NOW() - INTERVAL 1 DAY, @moka, @karim),
    ('Disponible sur toutes ces dates, je peux passer matin et soir.', 'en_attente', NOW() - INTERVAL 2 HOUR, @moka, @sofia),
    ('J’ai un grand jardin et l’habitude des bergers.', 'acceptee', NOW() - INTERVAL 5 DAY, @rex, @sofia),
    ('Je suis libre seulement le matin.', 'refusee', NOW() - INTERVAL 6 DAY, @rex, @karim),
    ('Je connais bien les lapins, j’en ai deux à la maison.', 'acceptee', NOW() - INTERVAL 30 DAY, @plume, @karim),
    ('Je peux passer tous les jours, j’habite le quartier.', 'acceptee', NOW() - INTERVAL 50 DAY, @bulle, @sofia);

-- La date de l'avis prend sa valeur par défaut (moment de l'import).
INSERT INTO avis (note, commentaire, id_annonce, id_auteur, id_cible) VALUES
    (5, 'Karim a été parfait : photos tous les jours et Plume très détendue à mon retour.', @plume, @claire, @karim),
    (4, 'Claire avait tout préparé, consignes très claires. Je recommande.', @plume, @karim, @claire),
    (4, 'Sofia a été ponctuelle et a même nettoyé l’aquarium.', @bulle, @lucas, @sofia);
