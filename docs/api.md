# Échanges HTTP — Vite & Gourmand

Contrat correspondant au lot de corrections du 7 novembre 2026, préparé sur le commit `ce3eecb88a1c8cdbd9184a7de3b7ae9c749d01e5`.

Cette documentation décrit les échanges utilisés par le navigateur et le serveur. L'application reste une application Symfony/Twig avec authentification par session. Une réponse HTTP peut contenir du JSON, un fragment HTML, une page complète ou une redirection : tout n'est pas une API JSON.

## Vue d'ensemble

| Méthode et chemin | Appelant | Résultat utile | Accès |
|---|---|---|---|
| `GET /api/menus` | `public/js/menu-filters.js` | Liste JSON des menus filtrés | Public |
| `GET /commande/new/{id}?fragment=1` | `public/js/order-workflow.js` | Fragment HTML du formulaire | Utilisateur connecté |
| `POST /commande/simulation/{id}` | `public/js/order-workflow.js` | JSON des prix calculés par le serveur | Utilisateur connecté + CSRF |
| `POST /commande/new/{id}` | Formulaire HTML de commande | Création puis redirection | Utilisateur connecté + CSRF du formulaire |
| `GET /commande/confirmation/{id}` | Navigateur après redirection | Page HTML de confirmation | Propriétaire de la commande |
| `GET /admin/stats` | Formulaire HTML de filtres | Page HTML du dashboard | `ROLE_ADMIN` |
| `GET https://data.geopf.fr/geocodage/search` | Service PHP `GeoplateformeGeocoder` | JSON de géocodage externe | Appel serveur sortant |

`{id}` représente l'identifiant réel d'un menu ou d'une commande selon la route. Le préfixe `/api` n'est pas nécessaire pour qu'une route retourne du JSON.

## Filtrage des menus

### Requête

`GET /api/menus`, sans corps de requête. Le navigateur envoie `Accept: application/json`.

Les paramètres sont facultatifs. Une valeur absente ou vide désactive le filtre ; les espaces périphériques sont supprimés. Un tableau à la place d'une valeur simple est rejeté. Les paramètres inconnus sont ignorés.

| Paramètre | Valeur admise | Effet |
|---|---|---|
| `minPrice` | Prix de 0 à 99999999.99, 8 chiffres au maximum avant le point, au plus 2 décimales | `basePrice >= minPrice` |
| `maxPrice` | Même format | `basePrice <= maxPrice` |
| `theme` | Texte de 100 caractères au maximum | Égalité du thème dans la requête SQL, selon la collation de la base |
| `diet` | Texte de 100 caractères au maximum | Égalité du régime dans la requête SQL, selon la collation de la base |
| `minPersons` | Entier de 1 à 2147483647 | `minimumPersons >= minPersons` |

Le prix minimum doit être inférieur ou égal au prix maximum si les deux sont fournis. Le séparateur décimal du contrat HTTP est le point : `25.50`, pas `25,50`. Les nombres négatifs, exponentiels et les textes non numériques sont rejetés pour les prix. Un thème inconnu est un filtre valide pouvant retourner une liste vide : le formulaire de gestion des menus permet actuellement des thèmes et régimes libres.

**Sémantique conservée :** `minPersons=4` signifie « menus dont le minimum exigé est au moins 4 ». Cela ne signifie pas « menus convenant à un groupe de 4 personnes ». Si ce deuxième besoin est souhaité, il faudra changer explicitement le libellé et la condition métier ; ce lot ne le fait pas.

Les prix comparés sont les `basePrice` des menus, donc le prix de base par personne dans le parcours de commande, pas le total d'une commande.

### Réponse 200

`Content-Type: application/json`. Un tableau JSON, éventuellement vide (`[]`), trié par identifiant de menu croissant par le repository.

| Champ de chaque menu | Type JSON | Signification |
|---|---|---|
| `id` | Nombre entier | Identifiant du menu |
| `title` | Chaîne | Titre |
| `description` | Chaîne ou `null` | Description |
| `theme` | Chaîne ou `null` | Thème |
| `diet` | Chaîne ou `null` | Régime |
| `basePrice` | Chaîne décimale | Prix provenant du champ Doctrine `DECIMAL` |
| `minimumPersons` | Nombre entier | Minimum de personnes |
| `detailUrl` | Chaîne | URL générée par Symfony pour la fiche |

### Réponse 400

Le code corrigé renvoie `400 Bad Request` si les filtres reconnus sont invalides. Le JSON contient `message` (chaîne) et `errors` (objet associant chaque nom de paramètre invalide à un message). Le repository n'est alors pas interrogé.

Exemples de requêtes invalides à essayer localement : `?minPrice=-1`, `?minPrice=abc`, `?minPrice=30&maxPrice=20`, `?minPersons=2.5`, `?minPrice[]=10`.

### Exemples reproductibles

Ces commandes envoient des requêtes d'exemple ; aucun résultat chiffré fictif n'est présenté comme une donnée réelle.

```bash
curl -i -G 'http://localhost:8000/api/menus' \
  -H 'Accept: application/json' \
  --data-urlencode 'maxPrice=25.50' \
  --data-urlencode 'theme=Noël'

curl -i 'http://localhost:8000/api/menus?minPrice=-1' \
  -H 'Accept: application/json'
```

## Simulation du prix

### Requête

`POST /commande/simulation/{id}` avec l'identifiant d'un menu existant. Le client envoie un `FormData`, avec les cookies de session de même origine et `Accept: application/json`. Le navigateur construit lui-même le `Content-Type: multipart/form-data` et sa frontière : ne pas le fixer manuellement dans `fetch()`.

| Nom dans le formulaire | Contrainte réellement utilisée par la simulation |
|---|---|
| `customer_order[deliveryAddress]` | Texte non vide, maximum 255 caractères |
| `customer_order[deliveryPostalCode]` | Texte non vide, maximum 20 caractères |
| `customer_order[deliveryCity]` | Texte non vide, maximum 100 caractères |
| `customer_order[personCount]` | Entier au moins égal au minimum du menu |
| `customer_order[_token]` | Jeton CSRF valide pour `customer_order` |

Le `FormData` peut contenir les autres champs du formulaire : ils ne sont pas tous exploités par la simulation. La validation finale de création reste plus complète. Une simulation réussie n'enregistre aucune commande et ne met pas à jour les statistiques.

### Réponses

| Situation | Comportement |
|---|---|
| Simulation réussie | `200`, JSON avec `menuPrice`, `deliveryPrice`, `discountAmount`, `totalPrice`, `unitPrice` (chaînes décimales) et `personCount` (entier) |
| CSRF absent ou invalide | `403`, JSON avec `message` |
| Champs de simulation invalides | `422`, JSON avec `message` et `errors` par nom de champ |
| Échec de géocodage/calcul | `503`, JSON avec `message` ; détail de l'exception dans les journaux serveur |
| Menu introuvable | `404` ; ne pas supposer un JSON structuré |
| Session absente ou expirée | Le pare-feu à formulaire peut rediriger vers la connexion ; `fetch()` peut recevoir finalement du HTML |

Le script existant vérifie le type de contenu avant de lire le JSON afin de traiter notamment une session expirée. Les réponses réussies de simulation portent `Cache-Control: private, no-store`.

Pour reproduire une simulation authentifiée sans mettre des secrets dans cette documentation : se connecter, ouvrir les outils développeur, onglet Réseau, modifier l'adresse ou le nombre de personnes, puis examiner la requête `simulation`. Une commande « Copier en tant que cURL » doit rester locale : elle peut contenir des cookies et un jeton CSRF.

## Formulaire et confirmation

`GET /commande/new/{id}` retourne normalement la page complète. Avec `?fragment=1`, il retourne le fragment `order/_form.html.twig`, chargé dans la modale. La réponse est privée et non stockable. Le JavaScript contrôle la réponse et la présence du contenu attendu ; en cas de problème, il conserve la navigation vers la page complète.

`POST /commande/new/{id}` soumet un formulaire HTML classique, pas un JSON. Symfony valide les données et le CSRF, puis **recalcule les prix côté serveur**. Les montants affichés par JavaScript ne constituent jamais une autorité pour enregistrer la commande.

Après création confirmée en MySQL : projection MongoDB en meilleur effort, envoi de confirmation, puis `303 See Other` vers `/commande/confirmation/{id}`. La destination est consultable uniquement par le propriétaire ; un autre utilisateur reçoit un refus d'accès. Une erreur de formulaire réaffiche la page HTML ; ne pas la traiter comme une API JSON. L'échec MongoDB n'annule pas la commande enregistrée : il est journalisé et doit être réconcilié par la reconstruction manuelle.

## Dashboard administrateur

`GET /admin/stats`, réservé à `ROLE_ADMIN`. L'authentification demeure fondée sur la session ; aucune API d'authentification par jeton n'a été ajoutée. Un utilisateur connecté sans le rôle requis ne peut pas accéder à la page.

| Paramètre facultatif | Validation |
|---|---|
| `menuId` | Entier positif jusqu'à 2147483647 et menu existant |
| `startMonth` | Mois `AAAA-MM`, année de 1000 à 9999, mois de 01 à 12 |
| `endMonth` | Même format ; doit être supérieur ou égal au début si les deux sont renseignés |

Une borne absente est ouverte. Les bornes fournies sont incluses. Les mois correspondent à `CustomerOrder.createdAt`, regroupé en fuseau `Europe/Paris`, **pas** à la date de prestation ni au passage au statut terminé.

Le formulaire recharge la page. Le graphique et le tableau utilisent le même résultat filtré. Réponses : `200` si la lecture réussit (y compris sans résultat), `400` avec page HTML et erreurs si les filtres sont invalides, `503` avec page HTML explicite si les statistiques ne peuvent pas être lues. Il n'existe pas de nouvelle route JSON de statistiques dans ce lot.

Définition métier et limites : [règles des statistiques](statistiques.md).

## Géocodage externe

Le service PHP appelle `GET https://data.geopf.fr/geocodage/search` avec `q` (adresse recherchée) et `limit=1`. Il lit les premières coordonnées GeoJSON, dans l'ordre **longitude puis latitude**, et renvoie un tableau PHP nommé `longitude`/`latitude`.

Cet appel ne part pas directement du navigateur. L'adresse de livraison est transmise à un service tiers pour le calcul ; cet usage doit être expliqué dans la documentation de confidentialité du projet. La distance actuellement calculée est une distance à vol d'oiseau (Haversine), pas un itinéraire routier. Le traitement spécifique à la ville de Bordeaux évite le géocodage dans ce cas. Ces règles n'ont pas été changées dans ce lot.

## Éléments hors périmètre

Pas de remplacement de l'authentification par session, pas de JWT ajouté, pas de nouvelle suite de tests, pas de filtrage asynchrone du dashboard, pas de gestion des annulations. Les tests existants restent inchangés. Aucun cookie, mot de passe, token ou secret réel ne doit être inséré dans les exemples publiés.
