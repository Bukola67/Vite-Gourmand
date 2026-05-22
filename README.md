# Vite & Gourmand

Application web réalisée dans le cadre de l’ECF **Développeur Web et Web Mobile**.  
Le projet a pour objectif de permettre à l’entreprise fictive **Vite & Gourmand** de présenter ses menus en ligne, gérer les commandes clients et proposer des espaces dédiés aux utilisateurs, employés et administrateurs. [file:1]

## Objectifs du projet

L’application doit permettre de :

- présenter l’entreprise sur une page d’accueil ;
- afficher l’ensemble des menus avec filtres ;
- permettre la création de compte et la connexion ;
- permettre la commande d’un menu en ligne ;
- proposer un espace utilisateur pour suivre ses commandes ;
- proposer un espace employé pour gérer les menus, commandes et avis ;
- proposer un espace administrateur avec gestion des employés et statistiques. [file:1]

## Stack technique

Le projet est développé avec la stack suivante :

- **Front-end** : Twig, Bootstrap, JavaScript
- **Back-end** : Symfony
- **Base de données relationnelle** : MySQL
- **Base de données NoSQL** : MongoDB
- **Gestion de projet** : GitHub Projects [file:1]

## État du projet

Ce dépôt correspond à la version de développement du projet.  
Le projet est en cours de réalisation et sera enrichi progressivement avec :

- les fonctionnalités métier ;
- la documentation technique ;
- les scripts SQL ;
- les données de démonstration ;
- les informations de déploiement.

## Fonctionnalités prévues

### Partie publique

- Page d’accueil
- Liste des menus
- Filtres dynamiques
- Détail d’un menu
- Page contact
- Mentions légales
- Conditions générales de vente [file:1]

### Authentification

- Inscription utilisateur
- Connexion
- Réinitialisation de mot de passe
- Gestion des rôles [file:1]

### Partie commande

- Création d’une commande
- Calcul du prix
- Gestion de la livraison
- Confirmation de commande par email [file:1]

### Espaces privés

- Espace utilisateur
- Espace employé
- Espace administrateur
- Statistiques via MongoDB [file:1]

## Installation en local

### Prérequis

- PHP 8.2 ou version compatible avec Symfony
- Composer
- Symfony CLI
- MySQL
- MongoDB
- Node.js et npm (optionnel si besoin d’assets complémentaires)
- Git

### Étapes d’installation

1. Cloner le dépôt :
```bash
git clone <URL_DU_DEPOT>
cd <NOM_DU_PROJET>
```

2. Installer les dépendances PHP :
```bash
composer install
```

3. Copier et configurer les variables d’environnement :
```bash
cp .env .env.local
```

4. Renseigner dans `.env.local` :
- la connexion MySQL
- la connexion MongoDB
- les paramètres mail si nécessaire

5. Créer la base de données :
```bash
php bin/console doctrine:database:create
```

6. Exécuter les migrations :
```bash
php bin/console doctrine:migrations:migrate
```

7. Charger les données de test si disponibles :
```bash
php bin/console doctrine:fixtures:load
```

8. Lancer le serveur local :
```bash
symfony server:start
```

9. Ouvrir l’application dans le navigateur :
```txt
http://127.0.0.1:8000
```

## Structure Git

Le projet suit une organisation Git simple :

- `main` : branche stable
- `develop` : branche de développement
- `feature/...` : une branche par fonctionnalité [file:1]

## Livrables prévus

Le projet final comprendra :

- le dépôt GitHub public ;
- l’application déployée ;
- le lien GitHub Projects ;
- le README ;
- les scripts SQL ;
- le manuel utilisateur ;
- la charte graphique ;
- la documentation technique ;
- la documentation de gestion de projet. [file:1]

## Sécurité et accessibilité

Le projet prendra en compte :

- la gestion sécurisée des mots de passe ;
- les rôles et permissions ;
- la validation des formulaires ;
- les protections CSRF ;
- l’accessibilité de l’interface conformément aux attentes du sujet. [file:1]

## Auteur

Projet réalisé par : Stéphie Saurel
Dans le cadre de l’ECF **Développeur Web et Web Mobile**.
