-- Enrichissement LOCAL de Vite & Gourmand, version 1.
-- Faire une sauvegarde avant import. Executer UNE SEULE FOIS.
-- Ne remplace pas data.sql : necessite les menus 1,2,3 et le client 1.
-- Aucun compte, mot de passe ou enregistrement existant n'est modifie.
-- En cas d'erreur : arreter l'import et effectuer ROLLBACK dans la meme session.
-- Ne pas utiliser un import qui poursuit les instructions apres une erreur.
USE vite_gourmand;
SET NAMES utf8mb4;
START TRANSACTION;
SET @seed_now = NOW();
SET @seed_day = CURDATE();

INSERT INTO menu (title,description,theme,diet,minimum_persons,base_price,condition_text,stock_available,created_at,updated_at) VALUES ('Menu végétal convivial','Une formule entièrement végétale : salade de lentilles, curry de légumes et salade de fruits.','Classique','Vegan',4,21.00,'Commander au moins 72 heures avant la prestation.',12,@seed_now,NULL);
SET @seed_menu_1 = LAST_INSERT_ID();

INSERT INTO dish (name,description,type,created_at) VALUES ('Salade de lentilles et carottes','Lentilles, carottes, huile d’olive et citron.','entree',@seed_now);
SET @seed_dish_1_1 = LAST_INSERT_ID();
INSERT INTO menu_dish (menu_id,dish_id) VALUES (@seed_menu_1,@seed_dish_1_1);

INSERT INTO dish (name,description,type,created_at) VALUES ('Curry de légumes au riz','Légumes, pois chiches, lait de coco et riz ; épices sans mélange allergène ajouté.','plat',@seed_now);
SET @seed_dish_1_2 = LAST_INSERT_ID();
INSERT INTO menu_dish (menu_id,dish_id) VALUES (@seed_menu_1,@seed_dish_1_2);

INSERT INTO dish (name,description,type,created_at) VALUES ('Salade de fruits frais','Pommes, poires, oranges et jus de citron.','dessert',@seed_now);
SET @seed_dish_1_3 = LAST_INSERT_ID();
INSERT INTO menu_dish (menu_id,dish_id) VALUES (@seed_menu_1,@seed_dish_1_3);

INSERT INTO menu (title,description,theme,diet,minimum_persons,base_price,condition_text,stock_available,created_at,updated_at) VALUES ('Buffet printanier végétarien','Une formule à partager : crudités, gratin de légumes et compote maison.','Événement','Végétarien',6,24.00,'Commander au moins 72 heures avant la prestation.',8,@seed_now,NULL);
SET @seed_menu_2 = LAST_INSERT_ID();

INSERT INTO dish (name,description,type,created_at) VALUES ('Crudités au citron','Carottes, concombre, citron et huile d’olive.','entree',@seed_now);
SET @seed_dish_2_1 = LAST_INSERT_ID();
INSERT INTO menu_dish (menu_id,dish_id) VALUES (@seed_menu_2,@seed_dish_2_1);

INSERT INTO dish (name,description,type,created_at) VALUES ('Gratin de légumes au fromage','Courgettes, pommes de terre, crème et fromage au lait de vache.','plat',@seed_now);
SET @seed_dish_2_2 = LAST_INSERT_ID();
INSERT INTO menu_dish (menu_id,dish_id) VALUES (@seed_menu_2,@seed_dish_2_2);

INSERT INTO dish (name,description,type,created_at) VALUES ('Compote pomme-poire','Pommes, poires et cannelle.','dessert',@seed_now);
SET @seed_dish_2_3 = LAST_INSERT_ID();
INSERT INTO menu_dish (menu_id,dish_id) VALUES (@seed_menu_2,@seed_dish_2_3);

-- Allergene du gratin : reutilisation de Lait sans supposer son identifiant.
INSERT INTO allergen (name)
SELECT 'Lait' WHERE NOT EXISTS (SELECT 1 FROM allergen WHERE name = 'Lait');
INSERT INTO dish_allergen (dish_id,allergen_id)
SELECT @seed_dish_2_2, id FROM allergen WHERE name = 'Lait' ORDER BY id LIMIT 1;
-- Les autres recettes ajoutees ne comportent pas d'allergene declare ici.
-- Ceci est un jeu fictif, pas une certification d'absence de traces.

-- Commande de demonstration 1 : en_attente.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL 14 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,25.00,0.00,0.00,100.00,
 'en_attente',NULL,NULL,@seed_created,NULL,1,1);
SET @seed_order_1 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_1);

-- Commande de demonstration 2 : acceptee.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL 12 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,22.00,0.00,0.00,88.00,
 'acceptee',NULL,NULL,@seed_created,NULL,1,2);
SET @seed_order_2 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_2);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('acceptee',DATE_ADD(@seed_created, INTERVAL 1 DAY),@seed_order_2);

UPDATE customer_order SET updated_at = (SELECT MAX(changed_at) FROM order_status_history WHERE customer_order_id = @seed_order_2) WHERE id = @seed_order_2;

-- Commande de demonstration 3 : en_preparation.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL 1 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,18.00,0.00,0.00,72.00,
 'en_preparation',NULL,NULL,@seed_created,NULL,1,3);
SET @seed_order_3 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_3);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('acceptee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 9 HOUR),@seed_order_3);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_preparation',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 10 HOUR),@seed_order_3);

UPDATE customer_order SET updated_at = (SELECT MAX(changed_at) FROM order_status_history WHERE customer_order_id = @seed_order_3) WHERE id = @seed_order_3;

-- Commande de demonstration 4 : en_livraison.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL 0 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,25.00,0.00,0.00,100.00,
 'en_livraison',NULL,NULL,@seed_created,NULL,1,1);
SET @seed_order_4 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_4);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('acceptee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 9 HOUR),@seed_order_4);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_preparation',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 10 HOUR),@seed_order_4);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_livraison',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 11 HOUR),@seed_order_4);

UPDATE customer_order SET updated_at = (SELECT MAX(changed_at) FROM order_status_history WHERE customer_order_id = @seed_order_4) WHERE id = @seed_order_4;

-- Commande de demonstration 5 : livree.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL -1 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,22.00,0.00,0.00,88.00,
 'livree',NULL,NULL,@seed_created,NULL,1,2);
SET @seed_order_5 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_5);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('acceptee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 9 HOUR),@seed_order_5);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_preparation',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 10 HOUR),@seed_order_5);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_livraison',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 11 HOUR),@seed_order_5);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('livree',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 12 HOUR),@seed_order_5);

UPDATE customer_order SET updated_at = (SELECT MAX(changed_at) FROM order_status_history WHERE customer_order_id = @seed_order_5) WHERE id = @seed_order_5;

-- Commande de demonstration 6 : en_attente_retour_materiel.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL -3 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,18.00,0.00,0.00,72.00,
 'en_attente_retour_materiel',NULL,NULL,@seed_created,NULL,1,3);
SET @seed_order_6 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_6);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('acceptee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 9 HOUR),@seed_order_6);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_preparation',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 10 HOUR),@seed_order_6);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_livraison',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 11 HOUR),@seed_order_6);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('livree',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 12 HOUR),@seed_order_6);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente_retour_materiel',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 13 HOUR),@seed_order_6);

UPDATE customer_order SET updated_at = (SELECT MAX(changed_at) FROM order_status_history WHERE customer_order_id = @seed_order_6) WHERE id = @seed_order_6;

-- Commande de demonstration 7 : terminee.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL -40 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,25.00,0.00,0.00,100.00,
 'terminee',NULL,NULL,@seed_created,NULL,1,1);
SET @seed_order_7 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_7);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('acceptee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 9 HOUR),@seed_order_7);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_preparation',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 10 HOUR),@seed_order_7);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_livraison',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 11 HOUR),@seed_order_7);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('livree',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 12 HOUR),@seed_order_7);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente_retour_materiel',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 13 HOUR),@seed_order_7);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('terminee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 14 HOUR),@seed_order_7);

UPDATE customer_order SET updated_at = (SELECT MAX(changed_at) FROM order_status_history WHERE customer_order_id = @seed_order_7) WHERE id = @seed_order_7;

-- Commande de demonstration 8 : terminee.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL -20 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,22.00,0.00,0.00,88.00,
 'terminee',NULL,NULL,@seed_created,NULL,1,2);
SET @seed_order_8 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_8);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('acceptee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 9 HOUR),@seed_order_8);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_preparation',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 10 HOUR),@seed_order_8);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_livraison',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 11 HOUR),@seed_order_8);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('livree',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 12 HOUR),@seed_order_8);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente_retour_materiel',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 13 HOUR),@seed_order_8);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('terminee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 14 HOUR),@seed_order_8);

UPDATE customer_order SET updated_at = (SELECT MAX(changed_at) FROM order_status_history WHERE customer_order_id = @seed_order_8) WHERE id = @seed_order_8;

-- Commande de demonstration 9 : terminee.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL -10 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,18.00,0.00,0.00,72.00,
 'terminee',NULL,NULL,@seed_created,NULL,1,3);
SET @seed_order_9 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_9);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('acceptee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 9 HOUR),@seed_order_9);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_preparation',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 10 HOUR),@seed_order_9);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_livraison',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 11 HOUR),@seed_order_9);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('livree',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 12 HOUR),@seed_order_9);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente_retour_materiel',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 13 HOUR),@seed_order_9);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('terminee',DATE_ADD(TIMESTAMP(@seed_delivery), INTERVAL 14 HOUR),@seed_order_9);

UPDATE customer_order SET updated_at = (SELECT MAX(changed_at) FROM order_status_history WHERE customer_order_id = @seed_order_9) WHERE id = @seed_order_9;

-- Commande de demonstration 10 : annulee.
SET @seed_delivery = DATE_ADD(@seed_day, INTERVAL 10 DAY);
SET @seed_created = DATE_SUB(@seed_delivery, INTERVAL 21 DAY);
INSERT INTO customer_order
(customer_first_name,customer_last_name,customer_email,customer_phone,
 delivery_address,delivery_postal_code,delivery_city,delivery_time,delivery_date,
 delivery_place,person_count,menu_price,delivery_price,discount_amount,total_price,
 status,cancel_reason,contact_mode,created_at,updated_at,user_id,menu_id)
VALUES ('Client','Démonstration','client-demo@example.test','0600000000',
 '1 rue de Démonstration','33000','Bordeaux','12:30:00',@seed_delivery,
 'Adresse fictive de démonstration',4,25.00,0.00,0.00,100.00,
 'annulee','Annulation fictive à la demande du client.','email',@seed_created,NULL,1,1);
SET @seed_order_10 = LAST_INSERT_ID();

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('en_attente',@seed_created,@seed_order_10);

INSERT INTO order_status_history (status,changed_at,customer_order_id) VALUES ('annulee',DATE_ADD(@seed_created, INTERVAL 1 DAY),@seed_order_10);

UPDATE customer_order SET updated_at = (SELECT MAX(changed_at) FROM order_status_history WHERE customer_order_id = @seed_order_10) WHERE id = @seed_order_10;

INSERT INTO review (rating,comment,is_validated,created_at,user_id) VALUES (5,'Très bon repas de démonstration, livraison ponctuelle.',1,DATE_ADD(@seed_day, INTERVAL -9 DAY),1);

INSERT INTO review (rating,comment,is_validated,created_at,user_id) VALUES (4,'Une formule végétarienne appréciée par nos invités.',1,DATE_ADD(@seed_day, INTERVAL -8 DAY),1);

INSERT INTO review (rating,comment,is_validated,created_at,user_id) VALUES (3,'Bonne prestation, présentation à améliorer.',0,DATE_ADD(@seed_day, INTERVAL -2 DAY),1);

INSERT INTO review (rating,comment,is_validated,created_at,user_id) VALUES (5,'Merci pour ce repas convivial.',0,DATE_ADD(@seed_day, INTERVAL -1 DAY),1);

COMMIT;

-- Verification des seuls ajouts de ce script.
SELECT id,title,theme,diet FROM menu WHERE id IN (@seed_menu_1,@seed_menu_2);
SELECT id,status,user_id,menu_id,total_price FROM customer_order
WHERE id IN (@seed_order_1,@seed_order_2,@seed_order_3,@seed_order_4,@seed_order_5,
@seed_order_6,@seed_order_7,@seed_order_8,@seed_order_9,@seed_order_10);
-- Attendu : 10 commandes, dont 3 terminees pour 260.00 euros au total.
