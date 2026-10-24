# Introduction

Mon parcours en visual merchandising et en enseignement m’a amenée à m’intéresser à la conception de supports visuels, à leur ergonomie et à leur capacité à faciliter la compréhension. Cette sensibilité m’a progressivement conduite vers le numérique et la conception d’interfaces. Dans le cadre de ma reconversion, j’ai choisi la formation Développeur web et web mobile de STUDI afin d’acquérir des compétences techniques en développement front-end et back-end, et de mieux comprendre le cycle de réalisation d’une application web.

# 5. Présentation du projet

## 5.1 Contexte

Vite & Gourmand est une entreprise fictive de traiteur située à Bordeaux. Elle souhaite disposer d’une application web permettant de présenter ses menus, de recevoir des commandes et d’organiser le suivi des prestations. L’application comprend également un espace de travail pour les employés et un espace de pilotage pour l’administrateur.

Ce projet est réalisé dans le cadre de la formation STUDI au titre professionnel Développeur web et web mobile.

## 5.2 Objectifs

Les principaux objectifs fonctionnels sont les suivants :

- Présenter les menus et leurs caractéristiques : thème, régime alimentaire, prix, nombre minimal de personnes, plats, allergènes et disponibilité.
- Proposer une recherche multicritère dynamique.
- Permettre l’inscription, la connexion et la gestion des accès selon les rôles.
- Permettre à un client connecté de commander un menu, avec un prix calculé côté serveur.
- Permettre au client de consulter ses commandes et d’annuler une commande encore en attente.
- Permettre aux employés de suivre les commandes, d’en modifier le statut et de modérer les avis.
- Permettre à l’administrateur de gérer les comptes employés et de consulter les statistiques de vente par menu.
- Stocker les données métier dans MySQL et les agrégats statistiques dans MongoDB.
- Déployer l’application dans un environnement de production accessible en HTTPS.

## 5.3 User stories

Les user stories ci-dessous présentent les besoins principaux des différents profils utilisateurs. Les critères d’acceptation détaillés ont été retirés afin de conserver une vue synthétique des fonctionnalités attendues.

### 5.3.1 Affichage et navigation

**Page d’accueil**  
En tant que visiteur, je veux consulter une page d’accueil présentant l’entreprise, son équipe et les avis clients validés.

**Liste des menus avec filtres dynamiques**  
En tant que visiteur, je veux filtrer les menus par thème, régime alimentaire, fourchette de prix et nombre de personnes, afin de trouver une offre adaptée à mes besoins.

**Détail d’un menu**  
En tant que visiteur, je veux consulter les détails d’un menu, notamment les plats, les allergènes, les conditions de commande et la disponibilité.

**Navigation responsive**  
En tant que visiteur ou utilisateur, je veux naviguer sur le site depuis un ordinateur, une tablette ou un téléphone mobile.

### 5.3.2 Gestion des comptes utilisateurs

**Inscription**  
En tant que visiteur, je veux créer un compte afin de pouvoir passer commande.

**Connexion**  
En tant qu’utilisateur, employé ou administrateur, je veux me connecter avec mes identifiants afin d’accéder à l’espace correspondant à mon rôle.

**Réinitialisation du mot de passe**  
En tant qu’utilisateur, je veux pouvoir réinitialiser mon mot de passe en cas d’oubli.

### 5.3.3 Commande

**Passer commande**  
En tant qu’utilisateur connecté, je veux commander un menu disponible.

**Formulaire de commande prérempli**  
En tant qu’utilisateur connecté, je veux que le formulaire de commande reprenne les informations déjà présentes dans mon profil et me demande uniquement les compléments nécessaires.

**Calcul automatique du prix total**  
En tant qu’utilisateur connecté, je veux connaître le prix total de ma commande avant de la valider, avec le détail des éléments qui composent ce montant.

### 5.3.4 Suivi et gestion des commandes

**Suivi de commande par le client**  
En tant qu’utilisateur connecté, je veux consulter mes commandes, leur statut actuel et l’historique de leur évolution.

**Modification du statut par l’employé**  
En tant qu’employé, je veux consulter les commandes et modifier leur statut depuis un espace dédié, dans le respect des transitions autorisées.

### 5.3.5 Avis clients

**Dépôt d’un avis**  
En tant qu’utilisateur ayant passé une commande, je veux déposer une note et un commentaire afin de partager mon expérience après la prestation.

**Modération des avis**  
En tant qu’employé, je veux consulter les avis en attente et les valider ou les refuser avant leur éventuelle publication.

### 5.3.6 Administration

**Création d’un compte employé**  
En tant qu’administrateur, je veux créer un compte employé afin de lui donner accès aux fonctionnalités nécessaires à ses missions.

**Activation et désactivation d’un compte employé**  
En tant qu’administrateur, je veux activer ou désactiver un compte employé selon les besoins, tout en conservant l’historique de ses actions.

**Statistiques de vente par menu**  
En tant qu’administrateur, je veux consulter les statistiques de vente par menu et les filtrer par période afin de suivre l’activité.

## 5.4 Diagramme de cas d’utilisation – acteurs et droits

Afin de formaliser les besoins identifiés dans le cahier des charges, j’ai réalisé un diagramme de cas d’utilisation présentant les actions accessibles à chaque profil. Ce travail m’a permis de relier les principales fonctionnalités aux acteurs concernés et de préciser les limites d’accès de chacun.

| Acteur | Fonctions principales | Limites d’accès |
|---|---|---|
| Visiteur | Consulter le site, les menus et les avis validés ; créer un compte ; se connecter. | Ne peut pas commander ni accéder aux espaces privés. |
| Client | Commander, consulter ses commandes, annuler une commande en attente et déposer un avis après une prestation terminée. | N’accède qu’à ses propres données. |
| Employé | Consulter les commandes, modifier leur statut et modérer les avis. | N’accède pas aux fonctions réservées à l’administrateur. |
| Administrateur | Utiliser les fonctions de l’employé, gérer les comptes employés et consulter les statistiques. | Accès protégé par son rôle. |

# Conclusion et perspectives

La réalisation de Vite & Gourmand m’a permis de mobiliser les principales compétences front-end et back-end du titre professionnel Développeur web et web mobile : préparation de l’environnement de travail, conception des interfaces, intégration avec Twig et Bootstrap, développement de fonctionnalités dynamiques, modélisation des données, accès aux bases SQL et NoSQL, mise en œuvre de règles métier, sécurisation des actions sensibles et déploiement.

Ce projet a représenté un travail conséquent, mais il m’a surtout permis de mieux comprendre les différentes étapes d’un projet web et leurs interdépendances. J’ai appris à faire des choix techniques, à mieux anticiper les difficultés et à mesurer l’importance des tests, de la documentation et de la cohérence entre les besoins fonctionnels et leur implémentation.

Cette expérience constitue une première étape dans mon parcours de développeuse web. Pour la suite, je souhaite consolider mes acquis en poursuivant la réalisation de projets, en approfondissant les bonnes pratiques de conception, d’accessibilité, de sécurité et de qualité du code, et en découvrant progressivement d’autres outils et approches. À moyen terme, je souhaite également continuer à développer mes compétences en UX/UI afin de mieux articuler conception de l’expérience utilisateur et réalisation technique.
