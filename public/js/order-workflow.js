(() => {
    'use strict';
    const money = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
    const priceFields = ['personCount', 'menuPrice', 'discountAmount', 'deliveryPrice', 'totalPrice'];
    const inputNames = ['deliveryAddress', 'deliveryPostalCode', 'deliveryCity', 'personCount'];

    function initializeForm(form) {
        if (form.dataset.initialized) return;
        form.dataset.initialized = '1';
        const submit = form.querySelector('[data-order-submit]');
        const status = form.querySelector('[data-price-status]');
        const section = form.querySelector('[data-price-section]');
        let timer, controller, version = 0, submitting = false;
        let quotedInputs = null;
        const fingerprint = () => JSON.stringify(inputNames.map(name => form.elements.namedItem(`customer_order[${name}]`).value));

        function clearErrors() {
            form.querySelectorAll('[data-quote-error]').forEach(node => {
                const input = form.elements.namedItem(`customer_order[${node.dataset.quoteError}]`);
                if (input && input.dataset.quoteInvalid) {
                    const ids = (input.getAttribute('aria-describedby') || '').split(' ').filter(id => id && id !== node.id);
                    if (ids.length) input.setAttribute('aria-describedby', ids.join(' '));
                    else input.removeAttribute('aria-describedby');
                    if (input.dataset.previousInvalid) input.setAttribute('aria-invalid', input.dataset.previousInvalid);
                    else input.removeAttribute('aria-invalid');
                    delete input.dataset.quoteInvalid;
                    delete input.dataset.previousInvalid;
                }
                node.hidden = true;
                node.textContent = '';
            });
        }

        function resetPrices(message) {
            quotedInputs = null;
            submit.disabled = true;
            priceFields.forEach(key => {
                form.querySelector(`[data-price="${key}"]`).textContent = key === 'totalPrice' || key === 'deliveryPrice' ? 'À calculer' : '—';
            });
            status.textContent = message;
        }

        function invalidate() {
            version++;
            controller?.abort();
            clearTimeout(timer);
            clearErrors();
            section.setAttribute('aria-busy', 'false');
            resetPrices('Mise à jour du prix…');
            return version;
        }

        async function quote(currentVersion) {
            const count = form.elements.namedItem('customer_order[personCount]');
            const addressComplete = inputNames.slice(0, 3).every(name => form.elements.namedItem(`customer_order[${name}]`).value.trim());
            if (!addressComplete) {
                resetPrices('Complétez l’adresse, le code postal et la ville pour calculer la livraison.');
                return;
            }
            if (!count.value || !count.validity.valid) {
                const message = `Indiquez un nombre entier d’au moins ${count.min} personnes.`;
                resetPrices(message);
                const error = form.querySelector('[data-quote-error="personCount"]');
                error.textContent = message;
                error.hidden = false;
                count.dataset.previousInvalid = count.getAttribute('aria-invalid') || '';
                count.dataset.quoteInvalid = '1';
                count.setAttribute('aria-invalid', 'true');
                count.setAttribute('aria-describedby', `${count.getAttribute('aria-describedby') || ''} ${error.id}`.trim());
                return;
            }
            const requestedInputs = fingerprint();
            controller = new AbortController();
            section.setAttribute('aria-busy', 'true');
            try {
                const response = await fetch(form.dataset.quoteUrl, {
                    method: 'POST', body: new FormData(form), credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }, signal: controller.signal,
                });
                const type = response.headers.get('content-type') || '';
                if (!type.includes('application/json')) throw new Error('Votre session a peut-être expiré. Rechargez la page.');
                const data = await response.json();
                if (currentVersion !== version) return;
                if (!response.ok) {
                    resetPrices(data.message || 'Le calcul du prix est indisponible.');
                    Object.entries(data.errors || {}).forEach(([name, message]) => {
                        const node = form.querySelector(`[data-quote-error="${name}"]`);
                        const input = form.elements.namedItem(`customer_order[${name}]`);
                        if (!node || !input) return;
                        node.textContent = message;
                        node.hidden = false;
                        input.dataset.previousInvalid = input.getAttribute('aria-invalid') || '';
                        input.dataset.quoteInvalid = '1';
                        input.setAttribute('aria-invalid', 'true');
                        input.setAttribute('aria-describedby', `${input.getAttribute('aria-describedby') || ''} ${node.id}`.trim());
                    });
                    return;
                }
                priceFields.forEach(key => {
                    form.querySelector(`[data-price="${key}"]`).textContent = key === 'personCount'
                        ? String(data[key])
                        : (key === 'discountAmount' ? '− ' : '') + money.format(Number(data[key]));
                });
                quotedInputs = requestedInputs;
                submit.disabled = false;
                status.textContent = 'Prix actualisé. Vérifiez vos informations avant de confirmer.';
            } catch (error) {
                if (error.name !== 'AbortError' && currentVersion === version) {
                    resetPrices(error.message || 'Connexion impossible. Réessayez dans un instant.');
                }
            } finally {
                if (currentVersion === version) section.setAttribute('aria-busy', 'false');
            }
        }

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
        form.addEventListener('submit', event => {
            // Empêcher une deuxième soumission.
            if (submitting) {
                event.preventDefault();
                return;
            }

            // Vérifier les champs avant de passer à « Enregistrement… ».
            if (!form.reportValidity()) {
                event.preventDefault();
                return;
            }

            // Vérifier que le prix correspond toujours aux données saisies.
            if (quotedInputs === null || fingerprint() !== quotedInputs) {
                event.preventDefault();
                schedule();
                return;
            }

            // La soumission classique peut maintenant avoir lieu.
            submitting = true;
            submit.disabled = true;
            submit.textContent = 'Enregistrement…';
        });
    }

    document.querySelectorAll('[data-order-form]').forEach(initializeForm);
    const opener = document.querySelector('[data-order-open]');
    const modalElement = document.getElementById('order-modal');
    if (!opener || !modalElement || !window.bootstrap?.Modal) return;
    const modal = new bootstrap.Modal(modalElement);
    const body = modalElement.querySelector('[data-order-modal-body]');
    let loading = false;
    opener.addEventListener('click', async event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        event.preventDefault();
        if (loading) return;
        if (body.querySelector('[data-order-form]')) {
            modal.show();
            return;
        }
        loading = true;
        opener.setAttribute('aria-busy', 'true');
        try {
            const url = new URL(opener.href);
            url.searchParams.set('fragment', '1');
            const response = await fetch(url, {credentials: 'same-origin'});
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
        } catch (_) {
            window.location.assign(opener.href);
        } finally {
            loading = false;
            opener.removeAttribute('aria-busy');
        }
    });
    modalElement.addEventListener('shown.bs.modal', () => {
        body.querySelector('input:not([type="hidden"]), button')?.focus();
    });
    modalElement.addEventListener('hidden.bs.modal', () => opener.focus());
})();
