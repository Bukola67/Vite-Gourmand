SET NAMES utf8mb4;

START TRANSACTION;


-- ================================================================
-- 1. REINITIALISATION DES DONNEES METIER
-- ================================================================
-- DELETE sans WHERE : choix volontaire pour une reconstruction
-- complete de la base locale de demonstration.
--
-- Suppression dans l'ordre inverse des dependances.
-- Les contraintes de cles etrangeres restent actives.
-- ================================================================

DELETE FROM `reset_password_request`;
DELETE FROM `order_status_history`;
DELETE FROM `review`;
DELETE FROM `customer_order`;
DELETE FROM `dish_allergen`;
DELETE FROM `menu_dish`;
DELETE FROM `allergen`;
DELETE FROM `dish`;
DELETE FROM `menu`;
DELETE FROM `user`;


-- ================================================================
-- 2. UTILISATEURS
-- ================================================================
-- 6 comptes :
--   1 : client de demonstration
--   3 : employe
--   4 : administrateur
--   5 et 6 : clients de demonstration
--   7 : client sans commande, pour le cas limite LEFT JOIN
--
-- Aucun compte ne porte l'identifiant 2.
-- ================================================================

INSERT INTO `user` (
    `id`,
    `email`,
    `roles`,
    `password`,
    `first_name`,
    `last_name`,
    `phone`,
    `address`,
    `postal_code`,
    `city`,
    `is_active`,
    `created_at`
) VALUES
(
    1,
    'user@test.com',
    '["ROLE_USER"]',
    '$2y$13$yEZC5WCO0tOOyUd/ceVDY.PQlP8nS/imfzR5PASnmdvlvyQtQawrO',
    'Client',
    'de démonstration 1',
    '0600000000',
    '1 rue de Démonstration',
    '33000',
    'Bordeaux',
    1,
    '2026-06-10 10:00:00'
),
(
    3,
    'employe@test.com',
    '["ROLE_EMPLOYEE"]',
    '$2y$13$SnyIWLAru2xQTR1vGL37..YS5.boGWojyAuYahIK.Jb/YWXtvZt3m',
    'Employé',
    'de démonstration',
    '0600000000',
    '1 rue de Démonstration',
    '33000',
    'Bordeaux',
    1,
    '2026-06-10 10:00:00'
),
(
    4,
    'admin@test.com',
    '["ROLE_ADMIN"]',
    '$2y$13$ObLNhAq81dxJgTWfACjeaujhcIvyP0TCnA/m8IlrC5OsX1i2uAGiO',
    'Administrateur',
    'de démonstration',
    '0600000000',
    '1 rue de Démonstration',
    '33000',
    'Bordeaux',
    1,
    '2026-06-10 10:00:00'
),
(
    5,
    'client-demo-2@example.test',
    '["ROLE_USER"]',
    '$2y$13$yEZC5WCO0tOOyUd/ceVDY.PQlP8nS/imfzR5PASnmdvlvyQtQawrO',
    'Client',
    'de démonstration 2',
    '0600000000',
    '1 rue de Démonstration',
    '33000',
    'Bordeaux',
    1,
    '2026-06-10 10:00:00'
),
(
    6,
    'client-demo-3@example.test',
    '["ROLE_USER"]',
    '$2y$13$yEZC5WCO0tOOyUd/ceVDY.PQlP8nS/imfzR5PASnmdvlvyQtQawrO',
    'Client',
    'de démonstration 3',
    '0600000000',
    '1 rue de Démonstration',
    '33000',
    'Bordeaux',
    1,
    '2026-06-10 10:00:00'
),
(
    7,
    'client-sans-commande@example.test',
    '["ROLE_USER"]',
    '$2y$13$yEZC5WCO0tOOyUd/ceVDY.PQlP8nS/imfzR5PASnmdvlvyQtQawrO',
    'Client',
    'sans commande',
    '0600000000',
    '1 rue de Démonstration',
    '33000',
    'Bordeaux',
    1,
    '2026-06-10 10:00:00'
);


-- ================================================================
-- 3. MENUS
-- ================================================================
-- Les menus 1 a 8 ont des commandes.
-- Le menu 9 n'a aucune commande et dispose d'un stock nul.
-- ================================================================

INSERT INTO `menu` (
    `id`,
    `title`,
    `description`,
    `theme`,
    `diet`,
    `minimum_persons`,
    `base_price`,
    `condition_text`,
    `stock_available`,
    `created_at`,
    `updated_at`
) VALUES
(
    1,
    'Menu Noël',
    'Un menu festif pour les repas de fin d’année.',
    'Noël',
    'Classique',
    4,
    25.00,
    'Commander 7 jours avant la prestation.',
    74,
    '2026-06-19 11:00:00',
    NULL
),
(
    2,
    'Menu Pâques',
    'Une sélection printanière et gourmande.',
    'Pâques',
    'Végétarien',
    4,
    22.00,
    'Commander 5 jours avant la prestation.',
    76,
    '2026-06-19 11:00:00',
    NULL
),
(
    3,
    'Menu Classique',
    'Une formule simple pour tous les événements.',
    'Classique',
    'Classique',
    2,
    18.00,
    'Commander 48 heures avant la prestation.',
    62,
    '2026-06-19 11:00:00',
    NULL
),
(
    4,
    'Menu végétal convivial',
    'Salade de lentilles, curry de légumes et salade de fruits.',
    'Classique',
    'Vegan',
    4,
    21.00,
    'Commander 72 heures avant la prestation.',
    78,
    '2026-06-19 11:00:00',
    NULL
),
(
    5,
    'Buffet printanier végétarien',
    'Crudités, gratin de légumes et compote maison.',
    'Événement',
    'Végétarien',
    6,
    24.00,
    'Commander 72 heures avant la prestation.',
    76,
    '2026-06-19 11:00:00',
    NULL
),
(
    6,
    'Menu réception élégante',
    'Une formule raffinée pour les réceptions.',
    'Événement',
    'Classique',
    8,
    39.00,
    'Commander 7 jours avant la prestation.',
    80,
    '2026-06-19 11:00:00',
    NULL
),
(
    7,
    'Menu automnal',
    'Produits de saison et recettes chaleureuses.',
    'Automne',
    'Classique',
    4,
    29.00,
    'Commander 5 jours avant la prestation.',
    61,
    '2026-06-19 11:00:00',
    NULL
),
(
    8,
    'Menu été méditerranéen',
    'Saveurs ensoleillées pour réunions familiales.',
    'Été',
    'Végétarien',
    4,
    27.00,
    'Commander 4 jours avant la prestation.',
    72,
    '2026-06-19 11:00:00',
    NULL
),
(
    9,
    'Menu végétal de démonstration — épuisé',
    'Menu destiné aux vérifications de disponibilité.',
    'Classique',
    'Vegan',
    2,
    20.00,
    'Disponible uniquement après réapprovisionnement.',
    0,
    '2026-06-19 11:00:00',
    NULL
);


-- ================================================================
-- 4. PLATS
-- ================================================================

INSERT INTO `dish` (
    `id`,
    `name`,
    `description`,
    `type`,
    `created_at`
) VALUES
(1, 'Velouté de potimarron',
 'Velouté et noisettes.', 'entree', '2026-06-19 11:00:00'),

(2, 'Saumon fumé et blinis',
 'Assortiment festif.', 'entree', '2026-06-19 11:00:00'),

(3, 'Dinde farcie aux marrons',
 'Viande et légumes rôtis.', 'plat', '2026-06-19 11:00:00'),

(4, 'Gratin dauphinois',
 'Crème et pommes de terre.', 'plat', '2026-06-19 11:00:00'),

(5, 'Bûche de Noël au chocolat',
 'Bûche festive.', 'dessert', '2026-06-19 11:00:00'),

(6, 'Œufs mimosa',
 'Entrée froide traditionnelle.', 'entree', '2026-06-19 11:00:00'),

(7, 'Tarte printanière aux légumes',
 'Tarte aux légumes verts.', 'plat', '2026-06-19 11:00:00'),

(8, 'Risotto aux asperges',
 'Risotto crémeux.', 'plat', '2026-06-19 11:00:00'),

(9, 'Nid de Pâques praliné',
 'Dessert au chocolat praliné.', 'dessert', '2026-06-19 11:00:00'),

(10, 'Salade de saison',
 'Légumes frais.', 'entree', '2026-06-19 11:00:00'),

(11, 'Poulet rôti fermier',
 'Poulet et légumes de saison.', 'plat', '2026-06-19 11:00:00'),

(12, 'Tarte aux pommes maison',
 'Dessert classique.', 'dessert', '2026-06-19 11:00:00'),

(13, 'Salade de lentilles et carottes',
 'Lentilles, carottes, huile d’olive et citron.',
 'entree', '2026-06-19 11:00:00'),

(14, 'Curry de légumes au riz',
 'Légumes, pois chiches, lait de coco et riz.',
 'plat', '2026-06-19 11:00:00'),

(15, 'Salade de fruits frais',
 'Fruits frais de saison.', 'dessert', '2026-06-19 11:00:00'),

(16, 'Crudités au citron',
 'Carottes, concombre, citron et huile d’olive.',
 'entree', '2026-06-19 11:00:00'),

(17, 'Gratin de légumes au fromage',
 'Courgettes, pommes de terre, crème et fromage.',
 'plat', '2026-06-19 11:00:00'),

(18, 'Compote pomme-poire',
 'Pommes, poires et cannelle.', 'dessert', '2026-06-19 11:00:00'),

(19, 'Tartare de tomates et basilic',
 'Tomates de saison et basilic.', 'entree', '2026-06-19 11:00:00'),

(20, 'Filet de dorade au citron',
 'Dorade et légumes grillés.', 'plat', '2026-06-19 11:00:00'),

(21, 'Panna cotta aux fruits rouges',
 'Dessert lacté.', 'dessert', '2026-06-19 11:00:00'),

(22, 'Soupe de châtaignes',
 'Soupe automnale et crème.', 'entree', '2026-06-19 11:00:00'),

(23, 'Parmentier de canard',
 'Canard confit et purée.', 'plat', '2026-06-19 11:00:00'),

(24, 'Poire pochée aux épices',
 'Poire et épices douces.', 'dessert', '2026-06-19 11:00:00'),

(25, 'Taboulé de quinoa',
 'Quinoa, herbes et légumes.', 'entree', '2026-06-19 11:00:00'),

(26, 'Lasagnes aux légumes',
 'Lasagnes végétariennes et fromage.', 'plat', '2026-06-19 11:00:00'),

(27, 'Tarte aux abricots',
 'Tarte aux fruits.', 'dessert', '2026-06-19 11:00:00');


-- ================================================================
-- 5. ALLERGENES
-- ================================================================
-- Associations fictives pour demonstration.
-- Ne constituent pas un inventaire alimentaire certifie.
-- ================================================================

INSERT INTO `allergen` (`id`, `name`) VALUES
(1, 'Gluten'),
(2, 'Lait'),
(3, 'Oeufs'),
(4, 'Poissons'),
(5, 'Fruits à coque');


-- ================================================================
-- 6. RELATIONS MENUS / PLATS
-- ================================================================

INSERT INTO `menu_dish` (`menu_id`, `dish_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),

(2, 6),
(2, 7),
(2, 8),
(2, 9),

(3, 10),
(3, 11),
(3, 12),

(4, 13),
(4, 14),
(4, 15),

(5, 16),
(5, 17),
(5, 18),

(6, 19),
(6, 20),
(6, 21),

(7, 22),
(7, 23),
(7, 24),

(8, 25),
(8, 26),
(8, 27),

(9, 13),
(9, 14),
(9, 15);


-- ================================================================
-- 7. RELATIONS PLATS / ALLERGENES
-- ================================================================

INSERT INTO `dish_allergen` (`dish_id`, `allergen_id`) VALUES
(1, 2),
(1, 5),
(2, 1),
(2, 4),
(3, 1),
(4, 2),
(5, 1),
(5, 2),
(5, 3),
(6, 3),
(7, 1),
(7, 2),
(8, 2),
(9, 2),
(9, 3),
(9, 5),
(12, 1),
(12, 2),
(12, 3),
(17, 2),
(20, 4),
(21, 2),
(22, 2),
(23, 2),
(26, 1),
(26, 2),
(27, 1),
(27, 3);


-- ================================================================
-- 8. COMMANDES
-- ================================================================
-- 25 commandes.
-- Les frais de livraison sont tous a 0 : Bordeaux.
-- Les remises et totaux sont corrects des l'insertion.
--
-- Seule la commande 9 atteint ici le seuil de remise :
-- menu 1, minimum 4, seuil 9, effectif 10.
-- 10 x 25.00 = 250.00 ; remise 25.00 ; total 225.00.
--
-- Les commandes annulees conservent leurs montants historiques.
-- ================================================================

INSERT INTO `customer_order` (
    `id`,
    `customer_first_name`,
    `customer_last_name`,
    `customer_email`,
    `customer_phone`,
    `delivery_address`,
    `delivery_postal_code`,
    `delivery_city`,
    `delivery_time`,
    `delivery_date`,
    `delivery_place`,
    `person_count`,
    `menu_price`,
    `delivery_price`,
    `discount_amount`,
    `total_price`,
    `status`,
    `cancel_reason`,
    `contact_mode`,
    `created_at`,
    `updated_at`,
    `user_id`,
    `menu_id`
) VALUES

(1,
 'Client', 'de démonstration 1', 'user@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-07-20', 'Adresse de livraison',
 6, 25.00, 0.00, 0.00, 150.00,
 'terminee', NULL, NULL,
 '2026-07-02 10:00:00', '2026-07-22 16:00:00',
 1, 1),

(2,
 'Client', 'de démonstration 2', 'client-demo-2@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-07-22', 'Adresse de livraison',
 8, 21.00, 0.00, 0.00, 168.00,
 'terminee', NULL, NULL,
 '2026-07-10 10:00:00', '2026-07-24 16:00:00',
 5, 4),

(3,
 'Client', 'de démonstration 3', 'client-demo-3@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-07-31', 'Adresse de livraison',
 10, 24.00, 0.00, 0.00, 240.00,
 'terminee', NULL, NULL,
 '2026-07-15 10:00:00', '2026-08-02 16:00:00',
 6, 5),

(4,
 'Client', 'de démonstration employé', 'employe@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-08-03', 'Adresse de livraison',
 12, 39.00, 0.00, 0.00, 468.00,
 'terminee', NULL, NULL,
 '2026-07-19 10:00:00', '2026-08-05 16:00:00',
 3, 6),

(5,
 'Client', 'de démonstration administrateur', 'admin@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-07-29', 'Adresse de livraison',
 5, 18.00, 0.00, 0.00, 90.00,
 'annulee', 'Annulation demandée par le client.', 'email',
 '2026-07-21 10:00:00', '2026-07-22 11:00:00',
 4, 3),

(6,
 'Client', 'de démonstration 2', 'client-demo-2@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-08-11', 'Adresse de livraison',
 8, 27.00, 0.00, 0.00, 216.00,
 'terminee', NULL, NULL,
 '2026-08-01 10:00:00', '2026-08-13 16:00:00',
 5, 8),

(7,
 'Client', 'de démonstration 3', 'client-demo-3@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-08-17', 'Adresse de livraison',
 6, 29.00, 0.00, 0.00, 174.00,
 'terminee', NULL, NULL,
 '2026-08-04 10:00:00', '2026-08-19 16:00:00',
 6, 7),

(8,
 'Client', 'de démonstration 1', 'user@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-08-17', 'Adresse de livraison',
 8, 22.00, 0.00, 0.00, 176.00,
 'terminee', NULL, NULL,
 '2026-08-07 10:00:00', '2026-08-19 16:00:00',
 1, 2),

(9,
 'Client', 'de démonstration employé', 'employe@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-08-30', 'Adresse de livraison',
 10, 25.00, 0.00, 25.00, 225.00,
 'terminee', NULL, NULL,
 '2026-08-15 10:00:00', '2026-09-01 16:00:00',
 3, 1),

(10,
 'Client', 'de démonstration administrateur', 'admin@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-08-28', 'Adresse de livraison',
 3, 18.00, 0.00, 0.00, 54.00,
 'terminee', NULL, NULL,
 '2026-08-18 10:00:00', '2026-08-30 16:00:00',
 4, 3),

(11,
 'Client', 'de démonstration 2', 'client-demo-2@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-09-01', 'Adresse de livraison',
 6, 21.00, 0.00, 0.00, 126.00,
 'annulee', 'Annulation demandée par le client.', 'email',
 '2026-08-20 10:00:00', '2026-08-21 11:00:00',
 5, 4),

(12,
 'Client', 'de démonstration 3', 'client-demo-3@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-09-13', 'Adresse de livraison',
 9, 24.00, 0.00, 0.00, 216.00,
 'terminee', NULL, NULL,
 '2026-09-02 10:00:00', '2026-09-15 16:00:00',
 6, 5),

(13,
 'Client', 'de démonstration 1', 'user@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-09-20', 'Adresse de livraison',
 10, 39.00, 0.00, 0.00, 390.00,
 'terminee', NULL, NULL,
 '2026-09-05 10:00:00', '2026-09-22 16:00:00',
 1, 6),

(14,
 'Client', 'de démonstration employé', 'employe@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-09-22', 'Adresse de livraison',
 8, 27.00, 0.00, 0.00, 216.00,
 'terminee', NULL, NULL,
 '2026-09-08 10:00:00', '2026-09-24 16:00:00',
 3, 8),

(15,
 'Client', 'de démonstration administrateur', 'admin@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-09-22', 'Adresse de livraison',
 5, 29.00, 0.00, 0.00, 145.00,
 'terminee', NULL, NULL,
 '2026-09-12 10:00:00', '2026-09-24 16:00:00',
 4, 7),

(16,
 'Client', 'de démonstration 1', 'user@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-09-29', 'Adresse de livraison',
 8, 22.00, 0.00, 0.00, 176.00,
 'terminee', NULL, NULL,
 '2026-09-17 10:00:00', '2026-10-01 16:00:00',
 1, 2),

(17,
 'Client', 'de démonstration 2', 'client-demo-2@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-10-13', 'Adresse de livraison',
 6, 21.00, 0.00, 0.00, 126.00,
 'terminee', NULL, NULL,
 '2026-10-01 10:00:00', '2026-10-15 16:00:00',
 5, 4),

(18,
 'Client', 'de démonstration 3', 'client-demo-3@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-10-16', 'Adresse de livraison',
 8, 25.00, 0.00, 0.00, 200.00,
 'terminee', NULL, NULL,
 '2026-10-04 10:00:00', '2026-10-18 16:00:00',
 6, 1),

(19,
 'Client', 'de démonstration employé', 'employe@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-10-18', 'Adresse de livraison',
 7, 24.00, 0.00, 0.00, 168.00,
 'terminee', NULL, NULL,
 '2026-10-07 10:00:00', '2026-10-20 16:00:00',
 3, 5),

(20,
 'Client', 'de démonstration administrateur', 'admin@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-10-27', 'Adresse de livraison',
 4, 22.00, 0.00, 0.00, 88.00,
 'en_attente_retour_materiel', NULL, NULL,
 '2026-10-15 10:00:00', '2026-10-28 12:00:00',
 4, 2),

(21,
 'Client', 'de démonstration 2', 'client-demo-2@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-10-29', 'Adresse de livraison',
 4, 18.00, 0.00, 0.00, 72.00,
 'livree', NULL, NULL,
 '2026-10-17 10:00:00', '2026-10-29 14:00:00',
 5, 3),

(22,
 'Client', 'de démonstration 3', 'client-demo-3@example.test', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-11-12', 'Adresse de livraison',
 8, 39.00, 0.00, 0.00, 312.00,
 'en_livraison', NULL, NULL,
 '2026-10-20 10:00:00', '2026-11-12 09:00:00',
 6, 6),

(23,
 'Client', 'de démonstration 1', 'user@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-11-13', 'Adresse de livraison',
 6, 27.00, 0.00, 0.00, 162.00,
 'en_preparation', NULL, NULL,
 '2026-10-25 10:00:00', '2026-11-12 14:00:00',
 1, 8),

(24,
 'Client', 'de démonstration employé', 'employe@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-11-14', 'Adresse de livraison',
 6, 22.00, 0.00, 0.00, 132.00,
 'acceptee', NULL, NULL,
 '2026-10-28 10:00:00', '2026-10-29 11:00:00',
 3, 2),

(25,
 'Client', 'de démonstration administrateur', 'admin@test.com', '0600000000',
 '1 rue de Démonstration', '33000', 'Bordeaux',
 '12:30:00', '2026-11-24', 'Adresse de livraison',
 8, 21.00, 0.00, 0.00, 168.00,
 'en_attente', NULL, NULL,
 '2026-11-01 10:00:00', NULL,
 4, 4);


-- ================================================================
-- 9. HISTORIQUES DES COMMANDES
-- ================================================================
-- Generation des etapes correspondant au statut atteint.
--
-- Les dates reproduisent la chronologie du jeu :
--   creation             : created_at
--   acceptation          : lendemain de la creation, 11 h
--   preparation          : veille de livraison, 14 h
--   depart en livraison  : jour de livraison, 9 h
--   livraison            : jour de livraison, 14 h
--   retour materiel      : lendemain de livraison, 12 h
--   cloture              : updated_at
--
-- Une commande annulee possede ici deux etapes :
-- en_attente puis annulee.
--
-- Les identifiants d'historique restent generes par AUTO_INCREMENT.
-- Ils ne sont pas supposes repartir a 1 apres les DELETE.
-- ================================================================

INSERT INTO `order_status_history` (
    `status`,
    `changed_at`,
    `customer_order_id`
)
SELECT
    etape.statut,
    CASE etape.rang
        WHEN 1 THEN o.created_at

        WHEN 2 THEN TIMESTAMP(
            DATE_ADD(DATE(o.created_at), INTERVAL 1 DAY),
            '11:00:00'
        )

        WHEN 3 THEN TIMESTAMP(
            DATE_SUB(o.delivery_date, INTERVAL 1 DAY),
            '14:00:00'
        )

        WHEN 4 THEN TIMESTAMP(
            o.delivery_date,
            '09:00:00'
        )

        WHEN 5 THEN TIMESTAMP(
            o.delivery_date,
            '14:00:00'
        )

        WHEN 6 THEN TIMESTAMP(
            DATE_ADD(o.delivery_date, INTERVAL 1 DAY),
            '12:00:00'
        )

        WHEN 7 THEN o.updated_at
    END,
    o.id
FROM `customer_order` AS o
INNER JOIN (
    SELECT 1 AS rang, 'en_attente' AS statut
    UNION ALL SELECT 2, 'acceptee'
    UNION ALL SELECT 3, 'en_preparation'
    UNION ALL SELECT 4, 'en_livraison'
    UNION ALL SELECT 5, 'livree'
    UNION ALL SELECT 6, 'en_attente_retour_materiel'
    UNION ALL SELECT 7, 'terminee'
) AS etape
    ON etape.rang <= CASE o.status
        WHEN 'en_attente' THEN 1
        WHEN 'acceptee' THEN 2
        WHEN 'en_preparation' THEN 3
        WHEN 'en_livraison' THEN 4
        WHEN 'livree' THEN 5
        WHEN 'en_attente_retour_materiel' THEN 6
        WHEN 'terminee' THEN 7
        WHEN 'annulee' THEN 1
        ELSE 0
    END
ORDER BY o.id ASC, etape.rang ASC;


INSERT INTO `order_status_history` (
    `status`,
    `changed_at`,
    `customer_order_id`
)
SELECT
    'annulee',
    o.updated_at,
    o.id
FROM `customer_order` AS o
WHERE o.status = 'annulee'
ORDER BY o.id ASC;


-- ================================================================
-- 10. AVIS
-- ================================================================

INSERT INTO `review` (
    `id`,
    `rating`,
    `comment`,
    `is_validated`,
    `created_at`,
    `user_id`
) VALUES
(
    1, 5,
    'Prestation de démonstration : très satisfaisante.',
    1, '2026-07-28 10:00:00', 1
),
(
    2, 4,
    'Livraison et service appréciés.',
    1, '2026-08-01 16:00:00', 5
),
(
    3, 5,
    'Menu végétal savoureux.',
    1, '2026-08-04 10:00:00', 6
),
(
    4, 4,
    'Bonne organisation du buffet.',
    1, '2026-09-15 09:00:00', 3
),
(
    5, 3,
    'Présentation à améliorer.',
    0, '2026-10-01 12:00:00', 4
),
(
    6, 5,
    'Très bon rapport qualité-prix.',
    0, '2026-11-02 14:00:00', 5
);


-- ================================================================
-- 11. VERIFICATIONS INTEGREES — LECTURE SEULE
-- ================================================================
-- Ces SELECT affichent les controles dans phpMyAdmin.
-- Ils ne declenchent pas un ROLLBACK automatique.
--
-- Resultats attendus :
--   utilisateurs       :   6
--   menus              :   9
--   plats              :  27
--   allergenes         :   5
--   associations plats :  30
--   commandes          :  25
--   historiques        : 144
--   avis               :   6
-- ================================================================

SELECT 'utilisateurs' AS table_name, COUNT(*) AS nombre
FROM `user`

UNION ALL
SELECT 'menus', COUNT(*) FROM `menu`

UNION ALL
SELECT 'plats', COUNT(*) FROM `dish`

UNION ALL
SELECT 'allergenes', COUNT(*) FROM `allergen`

UNION ALL
SELECT 'associations_menu_plat', COUNT(*) FROM `menu_dish`

UNION ALL
SELECT 'commandes', COUNT(*) FROM `customer_order`

UNION ALL
SELECT 'historiques', COUNT(*) FROM `order_status_history`

UNION ALL
SELECT 'avis', COUNT(*) FROM `review`;


-- ------------------------------------------------
-- 11.1 Controle de l'equation des prix
-- Resultat attendu : aucune ligne.
-- ------------------------------------------------

SELECT
    o.id,
    o.menu_price,
    o.person_count,
    o.delivery_price,
    o.discount_amount,
    o.total_price
FROM `customer_order` AS o
WHERE o.total_price <> ROUND(
    o.menu_price * o.person_count
    + o.delivery_price
    - o.discount_amount,
    2
)
ORDER BY o.id ASC;


-- ------------------------------------------------
-- 11.2 Controle de la remise de demonstration
-- Resultat attendu : aucune ligne.
--
-- Ce controle utilise le minimum actuel des menus,
-- stable dans ce jeu fixe.
-- Ce n'est pas une methode de recalcul general
-- de commandes historiques d'une application reelle.
-- ------------------------------------------------

SELECT
    o.id,
    o.person_count,
    m.minimum_persons,
    o.discount_amount
FROM `customer_order` AS o
INNER JOIN `menu` AS m
    ON m.id = o.menu_id
WHERE o.discount_amount <> CASE
    WHEN o.person_count >= m.minimum_persons + 5
        THEN ROUND(o.menu_price * o.person_count * 0.10, 2)
    ELSE 0.00
END
ORDER BY o.id ASC;


-- ------------------------------------------------
-- 11.3 Livraison gratuite a Bordeaux
-- Resultat attendu : aucune ligne.
-- ------------------------------------------------

SELECT
    o.id,
    o.delivery_city,
    o.delivery_price
FROM `customer_order` AS o
WHERE o.delivery_city = 'Bordeaux'
  AND o.delivery_price <> 0.00
ORDER BY o.id ASC;


-- ------------------------------------------------
-- 11.4 Effectif conforme au minimum du menu
-- Resultat attendu : aucune ligne.
-- ------------------------------------------------

SELECT
    o.id,
    o.person_count,
    m.title,
    m.minimum_persons
FROM `customer_order` AS o
INNER JOIN `menu` AS m
    ON m.id = o.menu_id
WHERE o.person_count < m.minimum_persons
ORDER BY o.id ASC;


-- ------------------------------------------------
-- 11.5 INNER JOIN : commandes avec client et menu
-- Resultat limite et ordre deterministe.
-- ------------------------------------------------

SELECT
    o.id,
    u.email AS compte_client,
    m.title AS menu,
    o.status,
    o.person_count,
    o.total_price,
    o.created_at,
    o.delivery_date
FROM `customer_order` AS o
INNER JOIN `user` AS u
    ON u.id = o.user_id
INNER JOIN `menu` AS m
    ON m.id = o.menu_id
ORDER BY o.created_at DESC, o.id DESC
LIMIT 15;


-- ------------------------------------------------
-- 11.6 LEFT JOIN : clients, y compris sans commande
--
-- Le compte 7 doit apparaitre avec 0 commande.
-- On filtre ici le role directement stocke en JSON :
-- cette requete ne calcule pas la hierarchie Symfony.
-- ------------------------------------------------

SELECT
    u.id,
    u.email,
    u.first_name,
    u.last_name,
    u.is_active,
    COUNT(o.id) AS nombre_commandes
FROM `user` AS u
LEFT JOIN `customer_order` AS o
    ON o.user_id = u.id
WHERE JSON_CONTAINS(u.roles, JSON_QUOTE('ROLE_USER'))
GROUP BY
    u.id,
    u.email,
    u.first_name,
    u.last_name,
    u.is_active
ORDER BY nombre_commandes ASC, u.id ASC;


-- ------------------------------------------------
-- 11.7 LEFT JOIN : tous les menus, meme sans commande
--
-- Le menu 9 doit apparaitre avec :
--   nb_commandes = 0
--   montant_commande = 0.00
--
-- COUNT(o.id), et non COUNT(*), permet le vrai zero.
--
-- Cette vue complete contient un menu sans commande.
-- Le service MongoDB actuel, base sur des projections
-- de commandes, ne cree pas automatiquement cette ligne.
-- ------------------------------------------------

SELECT
    m.id AS menu_id,
    m.title,
    COUNT(o.id) AS nb_commandes,
    COALESCE(SUM(o.total_price), 0.00) AS montant_commande
FROM `menu` AS m
LEFT JOIN `customer_order` AS o
    ON o.menu_id = m.id
GROUP BY m.id, m.title
ORDER BY nb_commandes DESC, m.id ASC;


-- ------------------------------------------------
-- 11.8 REFERENCE DU DASHBOARD — SANS FILTRE
--
-- Toutes les commandes creees, annulations comprises.
-- INNER JOIN : seuls les menus ayant des commandes
-- apparaissent, comme avec la projection MongoDB actuelle.
--
-- Resultat attendu :
--   menu 2 : 4 commandes /  572.00
--   menu 4 : 4 commandes /  588.00
--   menu 1 : 3 commandes /  575.00
--   menu 3 : 3 commandes /  216.00
--   menu 5 : 3 commandes /  624.00
--   menu 6 : 3 commandes / 1170.00
--   menu 8 : 3 commandes /  594.00
--   menu 7 : 2 commandes /  319.00
--
-- Il s'agit d'une reference SQL pour ce jeu de donnees :
-- elle ne remplit pas MongoDB.
-- ------------------------------------------------

SELECT
    m.id AS menu_id,
    m.title,
    COUNT(o.id) AS total_orders,
    SUM(o.total_price) AS montant_commande
FROM `customer_order` AS o
INNER JOIN `menu` AS m
    ON m.id = o.menu_id
GROUP BY m.id, m.title
ORDER BY total_orders DESC, m.id ASC;


-- ------------------------------------------------
-- 11.9 TOTAL DE REFERENCE
-- Attendu : 25 commandes / 4658.00 euros.
-- ------------------------------------------------

SELECT
    COUNT(o.id) AS total_commandes_creees,
    SUM(o.total_price) AS montant_total_commande
FROM `customer_order` AS o;


-- ------------------------------------------------
-- 11.10 REFERENCE MENSUELLE PAR DATE DE CREATION
--
-- Attendu :
--   2026-07 : 5 commandes / 1116.00
--   2026-08 : 6 commandes /  971.00
--   2026-09 : 5 commandes / 1143.00
--   2026-10 : 8 commandes / 1260.00
--   2026-11 : 1 commande  /  168.00
--
-- Aucun filtre de statut.
-- Ne pas utiliser delivery_date ou updated_at.
--
-- Pour ce jeu a horaires fixes en journee, les mois
-- restent les memes entre UTC et Europe/Paris.
-- Pour de vraies donnees proches de minuit en fin de mois,
-- adapter les conversions a la convention de stockage
-- avant comparaison avec le service MongoDB.
-- ------------------------------------------------

SELECT
    DATE_FORMAT(o.created_at, '%Y-%m') AS mois_creation,
    COUNT(o.id) AS total_orders,
    SUM(o.total_price) AS montant_commande
FROM `customer_order` AS o
GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
ORDER BY mois_creation ASC;


-- ------------------------------------------------
-- 11.11 EXEMPLE DE PERIODE : OCTOBRE 2026
--
-- Borne de debut incluse.
-- Borne du mois suivant exclue.
-- Cela couvre octobre entier, sans utiliser
-- une heure artificielle de fin de mois.
--
-- Resultat total attendu : 8 commandes / 1260.00.
-- Seuls les menus ayant des commandes sur la periode.
-- ------------------------------------------------

SELECT
    m.id AS menu_id,
    m.title,
    COUNT(o.id) AS total_orders,
    SUM(o.total_price) AS montant_commande
FROM `customer_order` AS o
INNER JOIN `menu` AS m
    ON m.id = o.menu_id
WHERE o.created_at >= '2026-10-01 00:00:00'
  AND o.created_at <  '2026-11-01 00:00:00'
GROUP BY m.id, m.title
ORDER BY total_orders DESC, m.id ASC;


-- ------------------------------------------------
-- 11.12 EXEMPLE PEDAGOGIQUE ON / WHERE
--
-- Meme periode, mais tous les menus sont conserves.
-- Les conditions de date sont dans ON.
-- Les menus sans commande en octobre ont un compteur 0.
--
-- Ce n'est pas exactement la presentation du dashboard,
-- qui ne produit actuellement que les groupes presents
-- dans les documents MongoDB correspondants.
-- ------------------------------------------------

SELECT
    m.id AS menu_id,
    m.title,
    COUNT(o.id) AS nb_commandes_octobre,
    COALESCE(SUM(o.total_price), 0.00) AS montant_octobre
FROM `menu` AS m
LEFT JOIN `customer_order` AS o
    ON o.menu_id = m.id
   AND o.created_at >= '2026-10-01 00:00:00'
   AND o.created_at <  '2026-11-01 00:00:00'
GROUP BY m.id, m.title
ORDER BY nb_commandes_octobre DESC, m.id ASC;


-- ------------------------------------------------
-- 11.13 Controle des stocks
--
-- Le menu 9 doit apparaitre avec stock_available = 0.
-- Aucun mouvement supplementaire n'est effectue.
-- ------------------------------------------------

SELECT
    m.id,
    m.title,
    m.minimum_persons,
    m.stock_available
FROM `menu` AS m
ORDER BY m.id ASC;


-- Resultat attendu : aucune ligne.
SELECT
    m.id,
    m.title,
    m.stock_available
FROM `menu` AS m
WHERE m.stock_available < 0
ORDER BY m.id ASC;


-- ------------------------------------------------
-- 11.14 Presence de l'historique initial
-- Resultat attendu : aucune ligne.
-- ------------------------------------------------

SELECT
    o.id,
    o.status,
    o.created_at
FROM `customer_order` AS o
LEFT JOIN `order_status_history` AS h
    ON h.customer_order_id = o.id
   AND h.status = 'en_attente'
   AND h.changed_at = o.created_at
WHERE h.id IS NULL
ORDER BY o.id ASC;


-- ------------------------------------------------
-- 11.15 Coherence du dernier statut historique
-- Resultat attendu : aucune ligne.
-- ------------------------------------------------

SELECT
    o.id,
    o.status AS statut_commande,
    dernier.status AS dernier_statut_historique,
    dernier.changed_at
FROM `customer_order` AS o
LEFT JOIN (
    SELECT
        h.customer_order_id,
        h.status,
        h.changed_at,
        ROW_NUMBER() OVER (
            PARTITION BY h.customer_order_id
            ORDER BY h.changed_at DESC, h.id DESC
        ) AS position_historique
    FROM `order_status_history` AS h
) AS dernier
    ON dernier.customer_order_id = o.id
   AND dernier.position_historique = 1
WHERE dernier.customer_order_id IS NULL
   OR dernier.status <> o.status
ORDER BY o.id ASC;


-- ================================================================
-- 12. VALIDATION DE L'UNIQUE TRANSACTION
-- ================================================================

COMMIT;


-- ================================================================
-- FIN DU SCRIPT
--
-- Aucune seconde transaction.
-- Aucun UPDATE de correction des prix apres insertion.
-- Aucun recalcul supplementaire du stock.
--
-- IMPORTANT :
-- La projection MongoDB created_order_stats n'a pas ete modifiee.
-- Apres cet import, la reconstruire avec la commande Symfony
-- existante avant de comparer les chiffres du dashboard.
--
-- Ce script charge un jeu de demonstration.
-- Il ne constitue pas un test du controleur, du verrou pessimiste,
-- de la gestion des exceptions ou de commandes concurrentes.
-- ================================================================