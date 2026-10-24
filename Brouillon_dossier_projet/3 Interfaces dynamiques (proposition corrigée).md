# 5.4 b — Interfaces dynamiques (proposition corrigée)

## Mode d'emploi de cette proposition

- Ce texte remplace la sous-partie actuelle « b. Un filtrage entièrement côté client et évolution vers une communication asynchrone avec fetch() » (pages 19 à 21 du dossier). La sous-partie « a. Affichage conditionnel selon la connexion et le rôle » et sa Figure 16 restent inchangées.
- Numérotation : ton dossier actuel contient deux « Figure 16 » (affichage conditionnel et MenuRepository). La numérotation ci-dessous démarre à la Figure 17 après l'affichage conditionnel. Les figures suivantes du dossier (actuellement 32 à 35 en partie 6.3) devront être décalées.
- Annexes : ta partie 9 contient actuellement « Figure 1 Compose.yaml », considérée ici comme l'annexe A1. Les annexes A2 à A5 sont à créer.
- Les blocs « Figure à insérer » et « Annexe » sont des consignes de mise en page : ils ne doivent pas apparaître tels quels dans le dossier final.

---

## b. Filtrage asynchrone

Le filtrage des menus est la réalisation la plus représentative de cette compétence, car je l'ai fait évoluer après avoir identifié une limite de ma première version.

Dans cette première version, j'avais réalisé le filtrage entièrement en JavaScript. Chaque carte portait des attributs `data-price`, `data-theme`, `data-diet` et `data-persons`, et mon script parcourait les cartes pour masquer celles qui ne correspondaient pas aux critères (`display: none`). Cette solution fonctionnait, mais tous les menus étaient chargés dans la page avant le filtrage : filtrer revenait seulement à masquer des éléments déjà transférés au navigateur.

J'ai donc déplacé la sélection des résultats côté serveur. L'utilisateur peut désormais renseigner un prix minimum, un prix maximum, un thème, un régime alimentaire et un nombre minimum de personnes. Chaque modification déclenche une requête vers une route API Symfony, et un bouton permet de réinitialiser les critères.

J'ai réparti le traitement entre trois responsabilités :

- **JavaScript** récupère les critères, construit la requête, appelle l'API avec `fetch()` et reconstruit la liste des cartes ;
- **le contrôleur** `MenuController` reçoit les paramètres, appelle le repository et renvoie les menus au format JSON ;
- **le repository** `MenuRepository` construit la requête Doctrine en n'ajoutant que les conditions correspondant aux critères renseignés.

Côté client, j'ai d'abord relié les champs et le bouton de réinitialisation à la fonction de filtrage :

```javascript
].forEach(input => {
    input.addEventListener('input', filterMenus);
    input.addEventListener('change', filterMenus);
});

resetButton.addEventListener('click', () => {
    maxPriceInput.value = '';
    minPriceInput.value = '';
    themeInput.value = '';
    dietInput.value = '';
    personsInput.value = '';

    filterMenus();
});
```

Dans `filterMenus()`, je construis les paramètres avec `URLSearchParams` en n'ajoutant que les critères renseignés, puis j'appelle l'API et je contrôle le statut de la réponse avant de lire le JSON :

```javascript
const params = new URLSearchParams();

if (maxPriceInput.value) {
    params.append('maxPrice', maxPriceInput.value);
}
// … même principe pour minPrice, theme, diet et minPersons

const response = await fetch(`/api/menus?${params.toString()}`);

if (!response.ok) {
    throw new Error(`Erreur serveur : ${response.status}`);
}

const menus = await response.json();
renderMenus(menus);
```

> **Figure 17 à insérer** — Extrait de `templates/menu/index.html.twig` : écouteurs d'événements et fonction `filterMenus()`.
> Annoter : (1) déclenchement sur `input`/`change`, (2) `URLSearchParams`, (3) `fetch()`, (4) contrôle `response.ok`, (5) appel de `renderMenus()`.

Côté serveur, j'ai créé une route GET dédiée dans `MenuController::filterApi()`. Elle transmet les critères au repository puis renvoie un tableau JSON contenant, pour chaque menu, le titre, la description, le thème, le régime, le prix, le nombre minimum de personnes et l'adresse de la page de détail :

```php
#[Route('/api/menus', name: 'app_menu_api_filter', methods: ['GET'])]
// …
$menus = $menuRepository->findByFilters(
    ($minPrice !== null && $minPrice !== '') ? (float) $minPrice : null,
    ($maxPrice !== null && $maxPrice !== '') ? (float) $maxPrice : null,
    $theme ?: null,
    $diet ?: null,
    ($minPersons !== null && $minPersons !== '') ? (int) $minPersons : null,
);
// …
return $this->json($data);
```

> **Figure 18 à insérer** — Extrait de `src/Controller/MenuController.php`, méthode `filterApi()`.
> Annoter : route GET, conversion des paramètres vides en `null`, réponse `$this->json()`.

Dans `MenuRepository::findByFilters()`, j'ajoute une condition uniquement lorsque le critère est renseigné. J'ai lié chaque valeur à un paramètre nommé avec `setParameter()` au lieu de la concaténer dans la requête DQL :

```php
if ($theme) {
    $qb->andWhere('m.theme = :theme')
       ->setParameter('theme', $theme);
}

if ($minPersons !== null) {
    $qb->andWhere('m.minimumPersons >= :minPersons')
       ->setParameter('minPersons', $minPersons);
}

return $qb->orderBy('m.id', 'ASC')->getQuery()->getResult();
```

Ce choix suit la recommandation de la documentation Doctrine : les valeurs transmises par `setParameter()` sont traitées comme des données, alors que la concaténation d'une saisie dans une requête DQL ou une expression du QueryBuilder expose à l'injection SQL ([Doctrine ORM — Security](https://www.doctrine-project.org/projects/doctrine-orm/en/3.7/reference/security.html)).

> **Figure 19 à insérer** — Extrait de `src/Repository/MenuRepository.php`, méthode `findByFilters()`.
> Annoter : condition ajoutée seulement si le critère existe, paramètre nommé, `setParameter()`.

> **Figure 20 à insérer** — Onglet Réseau des outils de développement : requête `GET /api/menus?theme=Pâques`, statut 200, `Content-Type: application/json` et réponse JSON. Tes Figures 19 et 20 actuelles peuvent être réutilisées et regroupées ici en 20 a et 20 b.

Cette évolution a une limite que j'ai identifiée : lors de l'ouverture de `/menus`, la page charge encore tous les menus (`findBy([], ['createdAt' => 'DESC'])`) ; seules les recherches suivantes renvoient une sélection. Par ailleurs, `findByFilters()` ne comporte ni pagination ni limite de résultats. Le filtrage côté serveur réduit donc les données renvoyées pour chaque recherche, mais il ne suffirait pas, à lui seul, à garantir les performances d'un catalogue très volumineux.

> **Annexe A2 — Filtrage des menus** : script complet de `templates/menu/index.html.twig` (`filterMenus()`, `renderMenus()`, `createMenuCard()`), méthode `MenuController::filterApi()` et méthode `MenuRepository::findByFilters()`.

---

## c. Formulaire de commande et simulation dynamique du prix

Pour le formulaire de commande, j'ai voulu que le client connaisse le prix avant de confirmer, sans recharger la page et sans que le navigateur décide du montant enregistré.

J'ai d'abord prérempli le formulaire avec les coordonnées du compte connecté. Dans `OrderController::new()`, je copie ces informations dans un nouvel objet `CustomerOrder` ; le client peut ainsi les adapter pour la prestation sans modifier son compte. J'initialise aussi le nombre de personnes avec le minimum du menu choisi.

```php
$order = new CustomerOrder();
$order->setCustomerFirstName((string) $user->getFirstName());
$order->setCustomerLastName((string) $user->getLastName());
$order->setCustomerEmail((string) $user->getEmail());
```

J'ai ensuite développé le script `public/js/order-workflow.js`. Il surveille les quatre champs qui influencent le prix : adresse, code postal, ville et nombre de personnes. À chaque modification, il invalide le prix précédent et programme un nouveau calcul après 500 millisecondes. Comme le minuteur précédent est annulé, le calcul n'est lancé qu'une fois la saisie interrompue, et non à chaque caractère.

```javascript
function schedule() {
    if (submitting) return;
    const current = invalidate();
    timer = setTimeout(() => quote(current), 500);
}

inputNames.forEach(name => {
    const input = form.elements.namedItem(`customer_order[${name}]`);
    input.addEventListener('input', schedule);
    input.addEventListener('change', schedule);
});
```

Avant d'interroger le serveur, je vérifie dans `quote()` que l'adresse, le code postal et la ville sont renseignés, et que le nombre de personnes respecte les contraintes HTML du champ. Sinon, j'affiche une consigne et je ne lance pas la requête. Cette vérification porte sur la présence des informations, pas sur l'existence réelle de l'adresse.

```javascript
const addressComplete = inputNames.slice(0, 3).every(
    name => form.elements.namedItem(`customer_order[${name}]`).value.trim()
);
// …
if (!count.value || !count.validity.valid) {
```

Lorsque ces conditions sont remplies, j'envoie le formulaire en POST avec `FormData` vers la route de simulation, dont l'adresse est fournie par l'attribut `data-quote-url` du formulaire. Le jeton CSRF fait partie des champs envoyés.

```javascript
controller = new AbortController();
section.setAttribute('aria-busy', 'true');

const response = await fetch(form.dataset.quoteUrl, {
    method: 'POST',
    body: new FormData(form),
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' },
    signal: controller.signal,
});
```

Côté serveur, la route `OrderController::quote()` exige le rôle utilisateur, vérifie le jeton CSRF puis contrôle les données nécessaires au calcul avant d'appeler le service `OrderQuoteCalculator` (règles de prix détaillées en partie 6.3 b). Elle renvoie en JSON le prix des menus, la remise, les frais de livraison, le total et le nombre de personnes. Cette simulation ne crée aucune commande.

```php
$this->denyAccessUnlessGranted('ROLE_USER');

$data = $request->request->all('customer_order');
$token = $data['_token'] ?? null;

if (!is_string($token) || !$this->isCsrfTokenValid('customer_order', $token)) {
    return $this->json(
        ['message' => 'Votre session a expiré. Rechargez la page.'],
        403
    );
}
```

À la réception d'une réponse valide, j'actualise le récapitulatif, je formate les montants en euros avec `Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' })`, puis j'active le bouton de confirmation.

> **Figure 21 à insérer** — 21 a : récapitulatif avant calcul (« À calculer », bouton de confirmation désactivé) ; 21 b : récapitulatif après une simulation réussie ; 21 c (facultatif) : onglet Réseau montrant la requête `POST /commande/simulation/{id}` et sa réponse JSON.

> **Annexe A3 — Simulation et confirmation de commande** : fichier complet `public/js/order-workflow.js`, méthodes `OrderController::quote()` et `OrderController::new()`, service `OrderQuoteCalculator`.

---

## d. Gestion des états et des réponses devenues obsolètes

J'ai prévu les différents états des deux fonctionnalités, afin que l'interface indique toujours à l'utilisateur ce qui se passe.

**Pour le filtrage**, `renderMenus()` vide la liste, actualise le compteur, puis affiche soit les cartes, soit un message lorsqu'aucun menu ne correspond. Je réécris ce message à chaque fois, car la même zone sert aussi à afficher une erreur :

```javascript
menusList.innerHTML = '';
menusCount.textContent = menus.length;

if (menus.length === 0) {
    noResults.textContent = 'Aucun menu ne correspond aux filtres sélectionnés.';
    noResults.classList.remove('d-none');
    return;
}

noResults.classList.add('d-none');

menus.forEach(menu => {
    menusList.appendChild(createMenuCard(menu));
});
```

Je n'ai pas encore ajouté d'indicateur de chargement pour le filtrage : c'est une amélioration que j'ai identifiée.

**Pour la simulation du prix**, le récapitulatif distingue les états suivants : informations incomplètes, prix invalidé (« Mise à jour du prix… »), calcul en cours, prix disponible et erreur. Pendant le calcul, la section reçoit `aria-busy="true"`, et le message d'état est placé dans une zone `role="status"` avec `aria-live="polite"`, afin d'être annoncé par les lecteurs d'écran.

Dès qu'un champ change, `invalidate()` rend l'ancien prix inutilisable : elle annule la requête et le minuteur en cours, efface les erreurs, puis `resetPrices()` remet les montants à zéro et désactive la confirmation.

```javascript
// invalidate()
version++;
controller?.abort();
clearTimeout(timer);
clearErrors();
section.setAttribute('aria-busy', 'false');
resetPrices('Mise à jour du prix…');

// resetPrices()
quotedInputs = null;
submit.disabled = true;
```

J'ai aussi traité le cas où plusieurs requêtes se croisent. Pour qu'une réponse ancienne ne remplace pas un résultat plus récent, je combine deux mécanismes : `AbortController` interrompt la requête précédente, et un numéro de version permet d'ignorer une réponse qui ne correspond plus à l'état du formulaire.

```javascript
const data = await response.json();

if (currentVersion !== version) return;
```

Enfin, je mémorise les valeurs qui ont servi au calcul. Au moment de la confirmation, je les compare aux valeurs actuelles ; si elles ont changé, j'interromps la soumission et je relance une simulation. La fonction `fingerprint()` produit simplement une représentation des quatre valeurs avec `JSON.stringify()` : il s'agit d'un contrôle de cohérence côté interface, pas d'une protection de sécurité.

```javascript
const requestedInputs = fingerprint();   // avant l'appel
// …
quotedInputs = requestedInputs;          // après une réponse valide
submit.disabled = false;
// …
if (quotedInputs === null || fingerprint() !== quotedInputs) {   // à la soumission
    event.preventDefault();
    schedule();
    return;
}
```

> **Figure 22 à insérer** — 22 a : liste des menus avec le message « Aucun menu ne correspond aux filtres sélectionnés. » et compteur à 0 ; 22 b : récapitulatif de commande après modification du nombre de personnes (montants réinitialisés, message « Mise à jour du prix… », bouton désactivé).

---

## e. Traitement des erreurs et sécurité de l'affichage

**Erreurs du filtrage.** Lorsque `fetch()` échoue ou que le statut HTTP n'est pas valide, le bloc `catch` de `filterMenus()` vide la liste et remet le compteur à zéro, afin de ne pas laisser affichés des résultats correspondant à une recherche précédente. Il affiche ensuite un message à l'utilisateur et consigne l'erreur dans la console :

```javascript
menusList.innerHTML = '';
menusCount.textContent = '0';
noResults.textContent =
    'Une erreur est survenue lors du chargement des menus.';
noResults.classList.remove('d-none');

console.error(error);
```

**Erreurs de la simulation.** Je vérifie d'abord que la réponse est bien au format JSON : une page HTML reçue à la place (par exemple une redirection vers la connexion après expiration de la session) produit un message invitant à recharger la page. Je contrôle ensuite le statut HTTP. La route de simulation renvoie 403 si le jeton CSRF est invalide, 422 si les données nécessaires au prix sont invalides (avec une erreur par champ) et 503 si le calcul échoue. Dans tous ces cas, je réinitialise les montants et le bouton de confirmation reste désactivé. Après un échec, le client peut relancer le calcul avec le bouton « Recalculer le prix », que j'ai relié à la même fonction `schedule()` :

```javascript
form.querySelector('[data-price-retry]')?.addEventListener('click', schedule);
```

Lorsque le serveur renvoie des erreurs par champ, je les affiche sous la saisie concernée, j'ajoute `aria-invalid="true"` et je relie le message au champ avec `aria-describedby`. À la modification suivante, `clearErrors()` efface ces messages et restaure les attributs d'accessibilité d'origine. L'annulation volontaire d'une requête (`AbortError`) n'est pas présentée comme une erreur.

```javascript
node.textContent = message;
node.hidden = false;

input.setAttribute('aria-invalid', 'true');
input.setAttribute(
    'aria-describedby',
    `${input.getAttribute('aria-describedby') || ''} ${node.id}`.trim()
);
```

**Sécurité de l'affichage.** Dans les deux fonctionnalités, j'insère les données reçues du serveur comme du texte, avec `textContent` ou `document.createTextNode()`, et non en les concaténant dans du HTML. Un contenu malveillant enregistré dans un titre ou un message ne peut donc pas être interprété comme du code par le navigateur.

```javascript
// createMenuCard()
title.textContent = menu.title;
description.textContent = menu.description || '';
theme.append(document.createTextNode(menu.theme || 'Non renseigné'));
```

Dans `createMenuCard()`, j'utilise `innerHTML` uniquement pour des libellés fixes écrits dans le code, et dans `renderMenus()` pour vider la liste ; aucune donnée issue de la réponse n'y est insérée.

> **Figure 23 à insérer** — 23 a : erreur 422 affichée sous le champ « nombre de personnes » dans le formulaire ; 23 b (facultatif) : message d'erreur du filtrage, obtenu en simulant une coupure réseau (onglet Réseau → « Hors connexion »).

---

## f. Validation avant confirmation et recalcul serveur

J'ai organisé la validation sur deux niveaux, car un contrôle dans le navigateur améliore l'expérience mais peut être contourné.

**Dans le navigateur.** Dans `CustomerOrderType`, j'ai défini des champs obligatoires, des longueurs maximales, un type e-mail, ainsi qu'un minimum dépendant du menu et un pas de 1 pour le nombre de personnes. Symfony génère à partir de ces options les attributs HTML `required`, `maxlength`, `type="email"`, `min` et `step`.

J'ai retiré l'attribut `novalidate` de `templates/order/_form.html.twig` : le navigateur bloque désormais lui-même la soumission d'un formulaire contenant un champ invalide et affiche son message. Dans le gestionnaire de soumission, j'appelle également `form.reportValidity()` avant de passer à l'état « Enregistrement… ».

```javascript
if (!form.reportValidity()) {
    event.preventDefault();
    return;
}
// … contrôle de cohérence du prix (partie d)

submitting = true;
submit.disabled = true;
submit.textContent = 'Enregistrement…';
```

Une fois les contrôles réussis, je désactive le bouton pour empêcher une double soumission, puis je laisse partir la soumission classique du formulaire : seule la simulation utilise `fetch()`.

**Sur le serveur.** Les mêmes champs portent des contraintes Symfony (`NotBlank`, `Length`, `Email`, `GreaterThanOrEqual`), présentées en partie 6.3 a. Le champ `personCount` montre bien les deux niveaux réunis :

```php
'required' => true,
'attr' => [
    'min' => $minimum,      // contrôle navigateur
    'step' => 1,
    'inputmode' => 'numeric',
],
'constraints' => [          // contrôle serveur
    new Assert\NotBlank(message: 'Le nombre de personnes est obligatoire.'),
    new Assert\GreaterThanOrEqual(
        value: $minimum,
        message: 'Ce menu nécessite au minimum {{ compared_value }} personnes.'
    ),
],
```

Lors de la création définitive, je ne fais pas confiance au montant affiché dans le navigateur. Après `isSubmitted()` et `isValid()`, je recalcule le prix avec le même service `OrderQuoteCalculator`, et j'enregistre uniquement les montants issus de ce calcul. Si le calcul échoue, j'ajoute une erreur au formulaire et la commande n'est pas enregistrée.

```php
if ($form->isSubmitted() && $form->isValid()) {
    try {
        // Le navigateur ne fournit jamais les montants de confiance.
        $prices = $quoteCalculator->calculate($menu, $order);
        // …
        $order->setTotalPrice($prices['totalPrice']);
```

> **Figure 24 à insérer** — Trois captures distinctes de la validation navigateur : 24 a champ obligatoire vide, 24 b e-mail mal formé, 24 c nombre de personnes inférieur au minimum. Pour l'une d'elles, ajouter l'inspecteur montrant les attributs `required`, `type="email"` ou `min` rendus dans le HTML.

> **Annexe A4 — Validation du formulaire de commande** : `src/Form/CustomerOrderType.php` complet et `templates/order/_form.html.twig` (sans `novalidate`).

---

## g. Ouverture du formulaire dans une modale

Pour éviter de quitter la fiche du menu, j'ai ajouté l'ouverture du formulaire de commande dans une modale Bootstrap. Lors du clic, mon script demande au serveur une version partielle de la page avec le paramètre `fragment=1`, que `OrderController::new()` traite en renvoyant uniquement le template du formulaire. J'analyse le HTML reçu avec `DOMParser`, j'insère le bloc `.vg-order` dans la modale, puis j'initialise le formulaire pour activer la simulation du prix.

```javascript
const url = new URL(opener.href);
url.searchParams.set('fragment', '1');
const response = await fetch(url, { credentials: 'same-origin' });
const html = await response.text();
const documentFragment = new DOMParser().parseFromString(html, 'text/html');
const content = documentFragment.querySelector('.vg-order');

if (!response.ok || response.redirected || !content) {
    window.location.assign(opener.href);
    return;
}

body.replaceChildren(document.importNode(content, true));
initializeForm(body.querySelector('[data-order-form]'));
modal.show();
```

J'ai prévu une solution de repli : en cas d'erreur, de redirection ou de contenu absent, le navigateur ouvre la page complète de commande. Les clics avec Ctrl, Cmd, Maj ou Alt ne sont pas interceptés, ce qui permet toujours d'ouvrir le lien dans un nouvel onglet. Pour l'accessibilité au clavier, je place le focus sur le premier champ à l'ouverture de la modale et je le rends au bouton déclencheur à la fermeture.

> **Figure 25 à insérer (facultative)** — Formulaire de commande ouvert dans la modale depuis la fiche d'un menu.

---

## h. Tests des interactions dynamiques (sous-partie ajoutée)

> Cette sous-partie est ajoutée car le REAC demande de démontrer les tests. Ne conserver que les lignes réellement exécutées et compléter les résultats.

J'ai écrit des tests fonctionnels Symfony (`WebTestCase`) pour vérifier les réponses du serveur sur lesquelles reposent mes interactions :

- `MenuControllerTest` : l'API `/api/menus` renvoie du JSON, le filtre par thème ne renvoie que des menus du thème demandé, et un thème inexistant renvoie une liste vide ;
- `OrderWorkflowTest` : une simulation valide renvoie les montants attendus sans créer de commande, un nombre de personnes inférieur au minimum renvoie 422 avec une erreur sur `personCount`, un jeton CSRF invalide renvoie 403, l'attribut `min` est bien rendu dans le HTML, et une soumission définitive invalide affiche les erreurs sans créer de commande.

J'exécute l'ensemble des tests dans le conteneur PHP avec `docker compose exec -e APP_ENV=test php bin/phpunit` : les 52 tests (182 assertions) passent.

Ces tests vérifient le serveur et le HTML rendu ; ils n'exécutent pas le JavaScript. J'ai donc complété par des tests manuels dans le navigateur :

| Scénario manuel | Navigateur / écran | Résultat observé |
|---|---|---|
| Combinaison de filtres puis réinitialisation | à compléter | à compléter |
| Filtre sans résultat (compteur à 0, message) | à compléter | à compléter |
| Coupure réseau pendant le filtrage (« Hors connexion ») : liste vidée, compteur à 0, message d'erreur | à compléter | à compléter |
| Recherche sans résultat juste après une erreur : retour du message « Aucun menu… » | à compléter | à compléter |
| Échec de simulation puis clic sur « Recalculer le prix » | à compléter | à compléter |
| Modification d'un champ après simulation : prix invalidé, bouton désactivé | à compléter | à compléter |
| Saisie rapide : une seule requête après la pause (onglet Réseau) | à compléter | à compléter |
| Soumission avec champ vide, e-mail invalide, nombre insuffisant | à compléter | à compléter |
| Modale : ouverture, focus, fermeture au clavier | à compléter | à compléter |
| Liste des menus et formulaire en vue mobile | à compléter | à compléter |

> **Annexe A5 — Tests** : `tests/Controller/MenuControllerTest.php`, `tests/Controller/OrderWorkflowTest.php`, capture du résultat de `docker compose exec -e APP_ENV=test php bin/phpunit` (52 tests, 182 assertions) et tableau des tests manuels complété.

---

## Récapitulatif des figures et annexes

| Élément | Contenu | Sous-partie |
|---|---|---|
| Figure 17 | `filterMenus()` et écouteurs | b |
| Figure 18 | `MenuController::filterApi()` | b |
| Figure 19 | `MenuRepository::findByFilters()` | b |
| Figure 20 a/b | Onglet Réseau `GET /api/menus?theme=Pâques` | b |
| Figure 21 a/b/c | Récapitulatif avant/après simulation, requête POST | c |
| Figure 22 a/b | Absence de résultat ; prix invalidé | d |
| Figure 23 a/b | Erreur 422 par champ ; erreur réseau du filtrage (liste vidée) | e |
| Figure 24 a/b/c | Trois cas de validation navigateur | f |
| Figure 25 | Modale (facultative) | g |
| Annexe A2 | Filtrage complet | b |
| Annexe A3 | Simulation et confirmation | c |
| Annexe A4 | Validation du formulaire | f |
| Annexe A5 | Tests automatisés et manuels | h |

## Éléments retirés de ton brouillon

- Le titre « 2. Extraits de code à montrer et annotations » et ses lettres A à H : les extraits sont maintenant placés dans les sous-parties qu'ils prouvent.
- Les liens `[[src](…)]` et `[[templates](…)]` : ce sont des références de conversation, pas des sources utilisables dans le dossier.
- Les mentions « Diagramme de séquence », « Ancienne annexes » et l'ancienne consigne exigeant trois erreurs visibles simultanément.
- La redite sur la validation et le calcul du prix : le détail reste en 6.3 a et 6.3 b, et la partie 5.4 y renvoie.
