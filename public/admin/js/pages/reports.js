(function () {
    if (window.__tmReportsChartsBooted) return;
    window.__tmReportsChartsBooted = true;

    var chartRegistry = {};

    function parsePayload() {
        var payloadElement = document.getElementById('reports-chart-data');
        if (!payloadElement) return null;
        try {
            return JSON.parse(payloadElement.textContent || '{}');
        } catch (error) {
            console.warn('Invalid reports payload', error);
            return null;
        }
    }

    function destroyChart(id) {
        if (chartRegistry[id]) {
            try {
                chartRegistry[id].destroy();
            } catch (_) {}
            delete chartRegistry[id];
        }
    }

    function renderChart(id, options) {
        var node = document.getElementById(id);
        if (!node || typeof window.ApexCharts === 'undefined') return;
        destroyChart(id);
        node.innerHTML = '';
        chartRegistry[id] = new window.ApexCharts(node, options);
        chartRegistry[id].render();
    }

    function boot() {
        if (!document.getElementById('reports-chart-data') || typeof window.ApexCharts === 'undefined') return;

        var payload = parsePayload();
        if (!payload) return;

        var perDay = Array.isArray(payload.requestsPerDay) ? payload.requestsPerDay : [];
        var statusBreakdown = payload.statusBreakdown && typeof payload.statusBreakdown === 'object'
            ? payload.statusBreakdown
            : {};

        renderChart('reportsRequestsChart', {
            chart: { type: 'line', height: 320, toolbar: { show: false } },
            series: [{ name: 'Requests', data: perDay.map(function (item) { return Number(item.value || 0); }) }],
            xaxis: { categories: perDay.map(function (item) { return item.label; }) },
            colors: ['#13AE9C'],
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            noData: { text: 'No data in selected period' },
        });

        renderChart('reportsStatusChart', {
            chart: { type: 'donut', height: 320 },
            series: Object.values(statusBreakdown).map(function (item) { return Number(item || 0); }),
            labels: Object.keys(statusBreakdown),
            colors: ['#13AE9C', '#34c38f', '#f1b44c', '#f46a6a', '#50a5f1', '#74788d', '#e83e8c', '#6f42c1'],
            legend: { position: 'bottom' },
            noData: { text: 'No status data' },
        });
    }

    document.addEventListener('DOMContentLoaded', boot);
    document.addEventListener('livewire:navigated', boot);

    if (window.Livewire && typeof window.Livewire.hook === 'function') {
        window.Livewire.hook('message.processed', function () {
            boot();
        });
    }
})();

