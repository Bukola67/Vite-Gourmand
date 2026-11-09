(() => {
    'use strict';

    const catalog = document.getElementById('menu-catalog');
    if (!catalog) return;

    const fields = {
        minPrice: document.getElementById('filter-min-price'),
        maxPrice: document.getElementById('filter-max-price'),
        theme: document.getElementById('filter-theme'),
        diet: document.getElementById('filter-diet'),
        minPersons: document.getElementById('filter-persons'),
    };
    const list = document.getElementById('menus-list');
    const count = document.getElementById('menus-count');
    const status = document.getElementById('menus-status');
    const empty = document.getElementById('no-results');
    const template = document.getElementById('menu-card-template');
    const money = new Intl.NumberFormat('fr-FR', {style: 'currency', currency: 'EUR'});
    const imageByTheme = {'Noël': 'menu-noel.jpg', 'Pâques': 'menu-paques.jpg'};
    let timer;
    let controller;

    function renderMenus(menus) {
        const cards = document.createDocumentFragment();
        menus.forEach(menu => {
            const card = template.content.cloneNode(true);
            const field = name => card.querySelector(`[data-field="${name}"]`);
            field('title').textContent = menu.title;
            field('description').textContent = menu.description || '';
            field('theme').textContent = menu.theme || 'Non renseigné';
            field('diet').textContent = menu.diet || 'Non renseigné';
            field('price').textContent = money.format(Number(menu.basePrice));
            field('persons').textContent = `${menu.minimumPersons} personnes`;
            field('image').src = catalog.dataset.imageBase + (imageByTheme[menu.theme] || 'menu-evenement.jpg');
            field('image').alt = menu.title;
            field('link').href = menu.detailUrl;
            cards.append(card);
        });
        list.replaceChildren(cards);
        count.textContent = String(menus.length);
        empty.classList.toggle('d-none', menus.length !== 0);
        status.textContent = `${menus.length} menu(s) trouvé(s).`;
    }

    async function loadMenus(activeController) {
        try {
            const params = new URLSearchParams();
            Object.entries(fields).forEach(([name, input]) => {
                if (input.value !== '') params.set(name, input.value);
            });
            const url = new URL(catalog.dataset.apiUrl, window.location.origin);
            url.search = params.toString();
            const response = await fetch(url, {
                headers: {'Accept': 'application/json'},
                signal: activeController.signal,
            });
            if (!(response.headers.get('content-type') || '').includes('application/json')) {
                throw new Error('Le serveur a renvoyé une réponse inattendue.');
            }
            const data = await response.json();
            // Une ancienne réponse ne doit jamais remplacer la recherche courante.
            if (activeController.signal.aborted) return;
            if (!response.ok) {
                const details = Object.values(data.errors || {}).join(' ');
                throw new Error(details || data.message || `Erreur serveur (${response.status}).`);
            }
            if (!Array.isArray(data)) throw new Error('Le format des menus est invalide.');
            renderMenus(data);
        } catch (error) {
            if (activeController.signal.aborted || error.name === 'AbortError') return;
            status.textContent = `${error.message || 'Chargement impossible.'} Les derniers résultats sont conservés.`;
        } finally {
            if (controller === activeController) list.setAttribute('aria-busy', 'false');
        }
    }

    function scheduleSearch(delay = 0) {
        clearTimeout(timer);
        // Annuler dès la saisie, pas seulement après la temporisation.
        controller?.abort();
        controller = new AbortController();
        const activeController = controller;
        const invalid = Object.values(fields).find(input => !input.validity.valid);
        if (invalid) {
            list.setAttribute('aria-busy', 'false');
            status.textContent = `${invalid.validationMessage} Les derniers résultats sont conservés.`;
            return;
        }
        list.setAttribute('aria-busy', 'true');
        status.textContent = 'Recherche des menus…';
        timer = setTimeout(() => loadMenus(activeController), delay);
    }

    [fields.minPrice, fields.maxPrice, fields.minPersons].forEach(input => {
        input.addEventListener('input', () => scheduleSearch(300));
    });
    [fields.theme, fields.diet].forEach(select => {
        select.addEventListener('change', () => scheduleSearch());
    });
    document.getElementById('reset-filters').addEventListener('click', () => {
        Object.values(fields).forEach(input => { input.value = ''; });
        scheduleSearch();
    });
})();
