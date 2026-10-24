# Vite Gourmand

Application web de traiteur développée avec Symfony dans le cadre du titre
professionnel Développeur web et web mobile. Elle permet de présenter
des menus, de passer et suivre des commandes, avec des espaces dédiés aux
clients, aux employés et à l'administrateur.

## Technologies

| Couche | Technologies |
|---|---|
| Back-end | PHP 8.4, Symfony 8.0, Doctrine ORM |
| Front-end | Twig, Bootstrap 5, JavaScript (Stimulus, Turbo), AssetMapper |
| Données | MySQL 8.0 (données métier), MongoDB 7.0 (statistiques) |
| Outils | Docker, Docker Compose, Git, GitHub Actions, GitHub Projects |
| Développement local | Mailpit (courriels), phpMyAdmin |

## Prérequis

- Git
- Docker et Docker Compose

PHP et Composer n'ont pas besoin d'être installés sur le poste : ils sont
fournis par le conteneur `php`.

## Installation

1. Cloner le dépôt
```bash
git clone https://github.com/Bukola67/Vite-Gourmand.git
cd Vite-Gourmand
```

2. Créer la configuration locale (fichier ignoré par Git)
```bash
echo "APP_SECRET=$(openssl rand -hex 32)" > .env.local
```
Les autres variables (bases de données, Mailpit) ont des valeurs de
développement par défaut dans `.env`, cohérentes avec `compose.yaml`.

3. Construire et démarrer les services
```bash
docker compose up -d --build
docker compose ps
```

4. Installer les dépendances PHP
```bash
docker compose exec php composer install
```

5. Créer le schéma de base de données
```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

6. Charger les données de démonstration
```bash
docker compose exec -T database mysql -uroot -proot vite_gourmand < database/data.sql
docker compose exec -T database mysql -uroot -proot vite_gourmand < database/enrichissement_demo.sql
```

## Accès

| Adresse | Service |
|---|---|
| http://localhost:8000 | Application |
| http://localhost:8025 | Mailpit |
| http://localhost:8080 | phpMyAdmin |

## Comptes de démonstration


| Rôle | E-mail | Mot de passe |
|---|---|---|
| Administrateur | admin@test.com | MotDePasse123! |
| Employé | employe@test.com | MotDePasseEmploye123! |
| Utilisateur | user@test.com | MonLapin1234* |

## Tests

```bash
docker compose exec php php bin/phpunit --configuration phpunit.dist.xml
```

## Intégration continue

Le workflow `.github/workflows/ci.yml` exécute les migrations, les tests
PHPUnit et la construction de l'image de production à chaque PR.

## Branches

- `main` : production (déployée sur Railway)
- `develop` : intégration
- `feature/` : fonctionnalités

## Environnements Docker

- `Dockerfile.dev` : image de développement (utilisée par `compose.yaml`)
- `Dockerfile` : image de production (Railway et CI)

Pour tester l'image de production en local :
```bash
docker build -t vite-gourmand:local .
docker run --rm -e PORT=8080 -p 8080:8080 vite-gourmand:local
```