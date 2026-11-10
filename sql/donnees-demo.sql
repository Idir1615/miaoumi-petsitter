-- Jeu de données de démonstration Miaoumi.
-- À importer APRÈS « sql/miaoumi.sql », dans la base miaoumi (phpMyAdmin > Importer).
-- Comptes fictifs, mot de passe commun : demo1234
-- Relançable : les comptes de démo existants sont supprimés d'abord (leurs annonces,
-- candidatures et avis partent avec eux grâce à ON DELETE CASCADE).
-- Les dates sont calculées à partir du jour de l'import pour garder des états cohérents.

USE miaoumi;
-- Le fichier est en UTF-8 : sans cette ligne, les accents peuvent être mal importés.
SET NAMES utf8mb4;

DELETE FROM utilisateurs WHERE email LIKE '%@demo.miaoumi.fr';

-- Comptes : un même compte peut confier et garder.
-- Le hash correspond au mot de passe demo1234 (password_hash, bcrypt).
SET @hash = '$2y$10$jaU23I4ejiVJtDed1EGoi.nPiBOJFxSivTfwT432aufz3PoViBnVG';
INSERT INTO utilisateurs (nom, email, password) VALUES ('Claire', 'claire@demo.miaoumi.fr', @hash); SET @claire = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Karim', 'karim@demo.miaoumi.fr', @hash);   SET @karim = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Sofia', 'sofia@demo.miaoumi.fr', @hash);   SET @sofia = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Lucas', 'lucas@demo.miaoumi.fr', @hash);   SET @lucas = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Inès', 'ines@demo.miaoumi.fr', @hash);     SET @ines = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Thomas', 'thomas@demo.miaoumi.fr', @hash); SET @thomas = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Amélie', 'amelie@demo.miaoumi.fr', @hash); SET @amelie = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Julien', 'julien@demo.miaoumi.fr', @hash); SET @julien = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Nadia', 'nadia@demo.miaoumi.fr', @hash);   SET @nadia = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Hugo', 'hugo@demo.miaoumi.fr', @hash);     SET @hugo = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Léa', 'lea@demo.miaoumi.fr', @hash);       SET @lea = LAST_INSERT_ID();
INSERT INTO utilisateurs (nom, email, password) VALUES ('Mehdi', 'mehdi@demo.miaoumi.fr', @hash);   SET @mehdi = LAST_INSERT_ID();

-- ---------------------------------------------------------------------------
-- Scénarios décrits dans le README (à garder pour la démonstration)
-- ---------------------------------------------------------------------------

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
    ('Nourrir Bulle et Caillou', 'Poisson', 'Marseille',
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

INSERT INTO avis (note, commentaire, dates, id_annonce, id_auteur, id_cible) VALUES
    (5, 'Karim a été parfait : photos tous les jours et Plume très détendue à mon retour.', NOW() - INTERVAL 17 DAY, @plume, @claire, @karim),
    (4, 'Claire avait tout préparé, consignes très claires. Je recommande.', NOW() - INTERVAL 16 DAY, @plume, @karim, @claire),
    (4, 'Sofia a été ponctuelle et a même nettoyé l’aquarium.', NOW() - INTERVAL 34 DAY, @bulle, @lucas, @sofia);

-- ---------------------------------------------------------------------------
-- Annonces ouvertes supplémentaires (liste publique, filtres, pagination)
-- ---------------------------------------------------------------------------

INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Promenades quotidiennes pour Oslo', 'Chien', 'Paris 15e', CURDATE() + INTERVAL 5 DAY, CURDATE() + INTERVAL 12 DAY,
     'Oslo est un husky de 3 ans, très sociable. Deux sorties d’une heure par jour, idéalement au parc Georges-Brassens. Il tire un peu en laisse au début.', 'ouverte', @ines);
SET @oslo = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Garde de Pistache et Noisette', 'Lapin', 'Bordeaux', CURDATE() + INTERVAL 14 DAY, CURDATE() + INTERVAL 21 DAY,
     'Deux lapins nains très câlins. Foin, granulés le matin et un peu de salade le soir. L’enclos est à nettoyer tous les deux jours.', 'ouverte', @thomas);
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Visites pour Gribouille, chat senior', 'Chat', 'Nantes', CURDATE() + INTERVAL 7 DAY, CURDATE() + INTERVAL 10 DAY,
     'Gribouille a 14 ans et dort beaucoup. Une visite par jour suffit : pâtée, eau fraîche, litière et quelques caresses.', 'ouverte', @amelie);
SET @gribouille = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Week-end avec Biscuit', 'Chien', 'Lille', CURDATE() + INTERVAL 2 DAY, CURDATE() + INTERVAL 4 DAY,
     'Biscuit, beagle de 5 ans, gourmand et affectueux. Je cherche quelqu’un qui peut le prendre chez lui pour le week-end.', 'ouverte', @julien);
SET @biscuit = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Nourrir Kiwi et Mango', 'Oiseau', 'Marseille', CURDATE() + INTERVAL 6 DAY, CURDATE() + INTERVAL 13 DAY,
     'Deux perruches ondulées. Graines et eau tous les jours, fruits frais un jour sur deux. La cage reste dans le salon.', 'ouverte', @nadia);
SET @kiwi = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Garde de Simba et Nala', 'Chat', 'Toulouse', CURDATE() + INTERVAL 20 DAY, CURDATE() + INTERVAL 30 DAY,
     'Frère et sœur de 2 ans, joueurs et un peu farceurs. Deux visites par jour, de préférence avec une séance de jeu le soir.', 'ouverte', @hugo);
SET @simba = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Surveiller mon aquarium', 'Poisson', 'Paris 11e', CURDATE() + INTERVAL 9 DAY, CURDATE() + INTERVAL 16 DAY,
     'Aquarium de 120 litres avec des néons et deux corydoras. Nourriture une fois par jour et vérification de la température.', 'ouverte', @lea);
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Balades pour Rocky', 'Chien', 'Lyon', CURDATE() + INTERVAL 1 DAY, CURDATE() + INTERVAL 3 DAY,
     'Rocky est un bouledogue français tranquille. Trois petites balades par jour, il n’aime pas trop la chaleur.', 'ouverte', @mehdi);
SET @rocky = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Garde de Câline pendant mon déménagement', 'Chat', 'Paris', CURDATE() + INTERVAL 12 DAY, CURDATE() + INTERVAL 15 DAY,
     'Câline est un peu craintive avec les inconnus. Il faudrait l’accueillir chez vous, dans un endroit calme, pendant trois jours.', 'ouverte', @sofia);
SET @caline = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Pépite, hamster à nourrir', 'Autre', 'Strasbourg', CURDATE() + INTERVAL 4 DAY, CURDATE() + INTERVAL 8 DAY,
     'Petit hamster doré. Graines et eau chaque jour, et changer la litière une fois pendant la garde. Très simple !', 'ouverte', @karim);
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Caramel, tortue de jardin', 'Autre', 'Nice', CURDATE() + INTERVAL 8 DAY, CURDATE() + INTERVAL 18 DAY,
     'Caramel vit dans le jardin. Salade, pissenlits et eau propre chaque jour. Vérifier que l’enclos est bien fermé.', 'ouverte', @ines);
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Lola en vacances avec vous ?', 'Chien', 'Nantes', CURDATE() + INTERVAL 30 DAY, CURDATE() + INTERVAL 37 DAY,
     'Lola, golden retriever de 7 ans, adore l’eau et les enfants. Je fournis croquettes, panier et jouets.', 'ouverte', @amelie);
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Plume pendant mon stage', 'Lapin', 'Paris', CURDATE() + INTERVAL 25 DAY, CURDATE() + INTERVAL 40 DAY,
     'Je pars en stage deux semaines. Plume est propre et habituée aux visites ; une passe par jour suffit.', 'ouverte', @claire);
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Coco, perroquet bavard', 'Oiseau', 'Bordeaux', CURDATE() + INTERVAL 11 DAY, CURDATE() + INTERVAL 14 DAY,
     'Coco est un gris du Gabon qui adore parler. Il a besoin de compagnie : au moins une heure de présence par jour.', 'ouverte', @thomas);

-- ---------------------------------------------------------------------------
-- Gardes terminées avec avis (profils et notes moyennes)
-- ---------------------------------------------------------------------------

INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Garde d’Oslo cet été', 'Chien', 'Paris 15e', CURDATE() - INTERVAL 60 DAY, CURDATE() - INTERVAL 55 DAY,
     'Cinq jours de promenades pour Oslo pendant mon séjour à l’étranger.', 'terminee', @ines);
SET @t_oslo = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Pistache pour un week-end', 'Lapin', 'Bordeaux', CURDATE() - INTERVAL 45 DAY, CURDATE() - INTERVAL 43 DAY,
     'Un week-end de garde pour Pistache.', 'terminee', @thomas);
SET @t_pistache = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Biscuit pendant mes congés', 'Chien', 'Lille', CURDATE() - INTERVAL 30 DAY, CURDATE() - INTERVAL 20 DAY,
     'Dix jours de garde à domicile pour Biscuit.', 'terminee', @julien);
SET @t_biscuit = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Simba et Nala, une semaine', 'Chat', 'Toulouse', CURDATE() - INTERVAL 25 DAY, CURDATE() - INTERVAL 18 DAY,
     'Deux visites par jour pendant une semaine.', 'terminee', @hugo);
SET @t_simba = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Kiwi et Mango, trois jours', 'Oiseau', 'Marseille', CURDATE() - INTERVAL 15 DAY, CURDATE() - INTERVAL 12 DAY,
     'Graines, eau et fruits pendant un long week-end.', 'terminee', @nadia);
SET @t_kiwi = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Gribouille au printemps', 'Chat', 'Nantes', CURDATE() - INTERVAL 90 DAY, CURDATE() - INTERVAL 80 DAY,
     'Une visite par jour pour Gribouille.', 'terminee', @amelie);
SET @t_gribouille = LAST_INSERT_ID();
INSERT INTO annonces (titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
    ('Rocky pendant un séminaire', 'Chien', 'Lyon', CURDATE() - INTERVAL 12 DAY, CURDATE() - INTERVAL 10 DAY,
     'Trois jours de balades pour Rocky.', 'terminee', @mehdi);
SET @t_rocky = LAST_INSERT_ID();

INSERT INTO candidatures (message, statut, date_candidature, id_annonce, id_gardien) VALUES
    ('J’adore les huskies, j’en ai gardé un l’an dernier.', 'acceptee', NOW() - INTERVAL 70 DAY, @t_oslo, @sofia),
    ('Je peux passer matin et soir.', 'refusee', NOW() - INTERVAL 69 DAY, @t_oslo, @hugo),
    ('J’habite à côté et j’ai déjà eu des lapins.', 'acceptee', NOW() - INTERVAL 50 DAY, @t_pistache, @lea),
    ('Je télétravaille, Biscuit ne sera jamais seul.', 'acceptee', NOW() - INTERVAL 40 DAY, @t_biscuit, @karim),
    ('Disponible toute la semaine.', 'acceptee', NOW() - INTERVAL 32 DAY, @t_simba, @mehdi),
    ('J’ai l’habitude des oiseaux, je peux passer tous les jours.', 'acceptee', NOW() - INTERVAL 20 DAY, @t_kiwi, @sofia),
    ('Je suis étudiante vétérinaire, les chats âgés ne me font pas peur.', 'acceptee', NOW() - INTERVAL 100 DAY, @t_gribouille, @lea),
    ('Je travaille à côté, je peux le sortir le midi.', 'acceptee', NOW() - INTERVAL 18 DAY, @t_rocky, @thomas);

INSERT INTO avis (note, commentaire, dates, id_annonce, id_auteur, id_cible) VALUES
    (5, 'Sofia m’a envoyé des photos et des vidéos tous les jours. Oslo est revenu fatigué et heureux !', NOW() - INTERVAL 54 DAY, @t_oslo, @ines, @sofia),
    (5, 'Inès avait laissé un carnet complet sur les habitudes d’Oslo. Super expérience.', NOW() - INTERVAL 53 DAY, @t_oslo, @sofia, @ines),
    (4, 'Léa a été très sérieuse, l’enclos était impeccable. Je recommande.', NOW() - INTERVAL 42 DAY, @t_pistache, @thomas, @lea),
    (5, 'Propriétaire très arrangeant, tout était prêt à mon arrivée.', NOW() - INTERVAL 41 DAY, @t_pistache, @lea, @thomas),
    (5, 'Karim a pris Biscuit chez lui comme un membre de la famille. Merci mille fois.', NOW() - INTERVAL 19 DAY, @t_biscuit, @julien, @karim),
    (4, 'Biscuit est adorable, juste un peu voleur de chaussettes !', NOW() - INTERVAL 19 DAY, @t_biscuit, @karim, @julien),
    (4, 'Mehdi a bien suivi les consignes, les chats étaient détendus.', NOW() - INTERVAL 17 DAY, @t_simba, @hugo, @mehdi),
    (5, 'Hugo répond vite aux messages et ses chats sont adorables.', NOW() - INTERVAL 16 DAY, @t_simba, @mehdi, @hugo),
    (5, 'Sofia a même nettoyé la cage avant mon retour. Parfait !', NOW() - INTERVAL 11 DAY, @t_kiwi, @nadia, @sofia),
    (5, 'Léa a su rassurer Gribouille, je lui confierai à nouveau sans hésiter.', NOW() - INTERVAL 79 DAY, @t_gribouille, @amelie, @lea),
    (4, 'Amélie m’a bien expliqué le traitement de Gribouille. Très bonne communication.', NOW() - INTERVAL 78 DAY, @t_gribouille, @lea, @amelie),
    (3, 'Garde correcte, mais Thomas est arrivé en retard le premier jour.', NOW() - INTERVAL 9 DAY, @t_rocky, @mehdi, @thomas),
    (5, 'Rocky est un amour et Mehdi a tout bien organisé.', NOW() - INTERVAL 9 DAY, @t_rocky, @thomas, @mehdi);

-- ---------------------------------------------------------------------------
-- Candidatures en attente sur les nouvelles annonces ouvertes
-- ---------------------------------------------------------------------------

INSERT INTO candidatures (message, statut, date_candidature, id_annonce, id_gardien) VALUES
    ('Bonjour Inès, je connais bien Oslo pour l’avoir gardé cet été !', 'en_attente', NOW() - INTERVAL 3 HOUR, @oslo, @sofia),
    ('Je cours tous les matins, Oslo pourra m’accompagner.', 'en_attente', NOW() - INTERVAL 1 DAY, @oslo, @hugo),
    ('Je peux accueillir Biscuit chez moi, j’ai un petit jardin.', 'en_attente', NOW() - INTERVAL 5 HOUR, @biscuit, @karim),
    ('J’habite à dix minutes, disponible tous les jours.', 'en_attente', NOW() - INTERVAL 2 DAY, @kiwi, @mehdi),
    ('J’ai deux chats, Simba et Nala seront entre de bonnes mains.', 'en_attente', NOW() - INTERVAL 1 DAY, @simba, @amelie),
    ('Je suis libre ces trois jours-là.', 'en_attente', NOW() - INTERVAL 6 HOUR, @rocky, @hugo),
    ('Mon appartement est très calme, Câline sera tranquille.', 'en_attente', NOW() - INTERVAL 4 HOUR, @caline, @claire),
    ('Je peux la prendre, j’ai déjà accueilli un chat craintif.', 'en_attente', NOW() - INTERVAL 1 DAY, @caline, @thomas),
    ('Les chats âgés, c’est ma spécialité.', 'en_attente', NOW() - INTERVAL 2 DAY, @gribouille, @lea);
