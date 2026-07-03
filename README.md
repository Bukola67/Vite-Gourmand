# Vite Gourmand

## Présentation
Vite Gourmand est une application web de réservation de repas développée avec Symfony dans le cadre de l’ECF Développeur Web et Web Mobile.

Le projet permet à l’entreprise fictive Vite & Gourmand de présenter ses menus en ligne, gérer les commandes clients et proposer des espaces dédiés aux utilisateurs, employés et administrateurs.

## Technologies

### Front-end
- Twig
- Bootstrap
- JavaScript

### Back-end
- Symfony
- Doctrine ORM

### Bases de données
- MySQL
- MongoDB

### Outils
- Docker
- Docker Compose
- GitHub Projects

## Prérequis
- Docker
- Docker Compose
- Composer
- PHP 8.3

## Fonctionnalités

### Partie publique
- Page d’accueil
- Liste des menus
- Détail d’un menu
- Page contact
- Mentions légales
- Conditions générales de vente

### Authentification
- Inscription et connexion
- Gestion des rôles : client, employé, administrateur
- Réinitialisation du mot de passe

### Partie commande
- Réservation d’un menu
- Calcul du prix
- Confirmation de commande par email

### Espaces privés
- Espace client
- Espace employé
- Espace administrateur
- Statistiques avec MongoDB

## Installation

1. Cloner le dépôt
```bash
git clone https://github.com/Bukola67/Vite-Gourmand.git
cd Vite-Gourmand
```

2. Installer les dépendances PHP
```bash
composer install
```

3. Démarrer les conteneurs Docker
```bash
docker compose up -d
```

4. Copier le fichier d’environnement
```bash
cp .env .env.local
```

5. Configurer `.env.local`
Renseigner les variables nécessaires, notamment :
- `DATABASE_URL`
- la connexion à MongoDB

6. Créer la base de données
```bash
docker compose exec php php bin/console doctrine:database:create
```

7. Importer la structure et les données
```bash
docker compose exec -T database mysql -uroot -proot vite_gourmand < schema.sql
docker compose exec -T database mysql -uroot -proot vite_gourmand < data.sql
```

8. Accéder à l’application
Ouvrir le navigateur à l’adresse suivante :
`http://localhost:8000`

## Comptes de démonstration
- Admin : admin@test.com
- Employé : employe@test.com
- Utilisateur : user@test.com