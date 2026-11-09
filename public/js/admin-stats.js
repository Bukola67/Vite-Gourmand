(() => {
    'use strict';

    const canvas = document.getElementById('ordersChart');
    const table = document.getElementById('orders-stats-table');
    if (!canvas || !table) return;

    const status = document.getElementById('orders-chart-status');
    if (typeof window.Chart !== 'function') {
        status.textContent = 'Graphique indisponible. Toutes les valeurs restent consultables dans le tableau.';
        return;
    }

    // Le tableau HTML est la source du graphique : aucun JSON dupliqué dans Twig.
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const labels = rows.map(row => row.querySelector('[data-stat-title]').textContent.trim());
    const values = rows.map(row => Number(row.querySelector('[data-stat-orders]').textContent.trim()));

    new window.Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{label: 'Commandes créées', data: values, backgroundColor: '#0d6efd'}],
        },
        options: {
            responsive: true,
            plugins: {legend: {display: false}},
            scales: {y: {beginAtZero: true, ticks: {precision: 0}}},
        },
    });
    status.textContent = 'Le tableau ci-dessous présente les valeurs exactes du graphique.';
})();
