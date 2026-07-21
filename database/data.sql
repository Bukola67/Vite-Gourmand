USE vite_gourmand;

INSERT INTO menu (id, title, description, theme, diet, minimum_persons, base_price, condition_text, stock_available, created_at, updated_at) VALUES
(1, 'Menu Noël', 'Un menu festif pour les repas de fin d’année.', 'Noël', 'Classique', 4, 25.00, 'Commande à effectuer au moins 7 jours avant la prestation.', 5, '2026-06-19 11:00:00', NULL),
(2, 'Menu Pâques', 'Un menu gourmand pour vos repas de Pâques.', 'Pâques', 'Végétarien', 4, 22.00, 'Commande à effectuer au moins 5 jours avant la prestation.', 5, '2026-06-19 11:00:00', NULL),
(3, 'Menu Classique', 'Une formule simple et savoureuse pour tous vos événements.', 'Classique', 'Classique', 2, 18.00, 'Commande à effectuer 48h avant la prestation.', 10, '2026-06-19 11:00:00', NULL);

INSERT INTO user (id, email, roles, password, first_name, last_name, phone, address, postal_code, city, is_active, created_at) VALUES
(1, 'user@test.com', '["ROLE_USER"]', '$2y$13$yEZC5WCO0tOOyUd/ceVDY.PQlP8nS/imfzR5PASnmdvlvyQtQawrO', 'Test', 'User', '0600000000', '1 rue du Test', '33000', 'Bordeaux', 1, '2026-06-10 14:14:16'),
(2, 'stephie.saurel@gmail.com', '["ROLE_USER"]', '$2y$13$gr1JIq87.EjsocCYm6.mROFtLdorsSIlioEESFLbPvKilP8.jpiCW', 'Stéphie', 'Saurel', '0683399551', '4 Impasse Plobmann', '67600', 'Sélestat', 1, '2026-06-17 09:08:15'),
(3, 'employe@test.com', '["ROLE_EMPLOYEE"]', '$2y$13$SnyIWLAru2xQTR1vGL37..YS5.boGWojyAuYahIK.Jb/YWXtvZt3m', 'Cristiano', 'Ronaldo', '0600000000', '10 rue de Bordeaux', '33000', 'Bordeaux', 1, '2026-06-25 10:53:07'),
(4, 'admin@test.com', '["ROLE_ADMIN"]', '$2y$13$ObLNhAq81dxJgTWfACjeaujhcIvyP0TCnA/m8IlrC5OsX1i2uAGiO', 'Padre', 'Pizzaïolo', '0600000000', '10 rue de Bordeaux', '33000', 'Bordeaux', 1, '2026-06-26 09:24:23');

INSERT INTO dish (id, name, description, type, created_at) VALUES
(1, 'Velouté de potimarron', 'Velouté onctueux servi chaud avec éclats de noisettes.', 'entree', '2026-06-19 11:00:00'),
(2, 'Saumon fumé et blinis', 'Assortiment festif de saumon fumé avec blinis.', 'entree', '2026-06-19 11:05:00'),
(3, 'Dinde farcie aux marrons', 'Plat de fête accompagné de légumes rôtis.', 'plat', '2026-06-19 11:10:00'),
(4, 'Gratin dauphinois', 'Accompagnement fondant à base de crème et pommes de terre.', 'plat', '2026-06-19 11:15:00'),
(5, 'Bûche de Noël au chocolat', 'Dessert traditionnel de fin d’année.', 'dessert', '2026-06-19 11:20:00'),
(6, 'Œufs mimosa', 'Entrée froide traditionnelle revisitée.', 'entree', '2026-06-19 11:25:00'),
(7, 'Tarte printanière aux légumes', 'Tarte salée de saison aux légumes verts.', 'plat', '2026-06-19 11:30:00'),
(8, 'Risotto aux asperges', 'Risotto crémeux aux asperges vertes.', 'plat', '2026-06-19 11:35:00'),
(9, 'Nid de Pâques praliné', 'Dessert gourmand au chocolat praliné.', 'dessert', '2026-06-19 11:40:00'),
(10, 'Salade de saison', 'Entrée fraîche et légère.', 'entree', '2026-06-19 11:45:00'),
(11, 'Poulet rôti fermier', 'Poulet rôti accompagné de légumes de saison.', 'plat', '2026-06-19 11:50:00'),
(12, 'Tarte aux pommes maison', 'Dessert classique et convivial.', 'dessert', '2026-06-19 11:55:00');

INSERT INTO allergen (id, name) VALUES
(1, 'Gluten'),
(2, 'Lait'),
(3, 'Oeufs'),
(4, 'Poissons'),
(5, 'Fruits à coque');

INSERT INTO menu_dish (menu_id, dish_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5),
(2, 6), (2, 7), (2, 8), (2, 9),
(3, 10), (3, 11), (3, 12);

INSERT INTO dish_allergen (dish_id, allergen_id) VALUES
(1, 2), (1, 5),
(2, 1), (2, 4),
(3, 1),
(4, 2),
(5, 2), (5, 3),
(6, 3),
(7, 1), (7, 2),
(8, 2),
(9, 2), (9, 3), (9, 5),
(12, 1), (12, 2), (12, 3);
