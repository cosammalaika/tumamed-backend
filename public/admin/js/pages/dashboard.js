(function () {
    if (window.__tmDashboardChartsBooted) return;
    window.__tmDashboardChartsBooted = true;

    var chartRegistry = {};

    function parsePayload() {
        var payloadElement = document.getElementById('dashboard-chart-data');
        if (!payloadElement) return null;

        try {
            return JSON.parse(payloadElement.textContent || '{}');
        } catch (error) {
            console.warn('Invalid dashboard chart payload', error);
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

    function renderDashboardCharts() {
        var payload = parsePayload();
        if (!payload || typeof window.ApexCharts === 'undefined') return;

        var requestsData = Array.isArray(payload.requestsByMonth) ? payload.requestsByMonth : [];
        var statusData = payload.statusCounts && typeof payload.statusCounts === 'object' ? payload.statusCounts : {};
        var pharmacyData = payload.pharmacyActivity && typeof payload.pharmacyActivity === 'object' ? payload.pharmacyActivity : {};

        function sparkSeries(value) {
            var seed = Number(value || 0);
            return [seed, seed + 1, Math.max(seed - 1, 0), seed + 2, seed];
        }

        renderChart('spark-hospitals', {
            chart: { type: 'line', height: 60, sparkline: { enabled: true } },
            stroke: { width: 2, curve: 'smooth' },
            series: [{ data: sparkSeries(payload.hospitalCount || requestsData.length || 0) }],
            colors: ['#13AE9C'],
            tooltip: { enabled: false },
        });

        renderChart('spark-pharmacies', {
            chart: { type: 'line', height: 60, sparkline: { enabled: true } },
            stroke: { width: 2, curve: 'smooth' },
            series: [{ data: sparkSeries(payload.pharmacyCount || Object.keys(statusData).length || 0) }],
            colors: ['#13AE9C'],
            tooltip: { enabled: false },
        });

        renderChart('spark-active', {
            chart: { type: 'line', height: 60, sparkline: { enabled: true } },
            stroke: { width: 2, curve: 'smooth' },
            series: [{ data: sparkSeries(payload.activeRequests || 0) }],
            colors: ['#13AE9C'],
            tooltip: { enabled: false },
        });

        renderChart('spark-delivered', {
            chart: { type: 'line', height: 60, sparkline: { enabled: true } },
            stroke: { width: 2, curve: 'smooth' },
            series: [{ data: sparkSeries(payload.deliveredLast30 || 0) }],
            colors: ['#13AE9C'],
            tooltip: { enabled: false },
        });

        renderChart('requestsChart', {
            chart: { type: 'area', height: 320, toolbar: { show: false } },
            series: [{ name: 'Requests', data: requestsData.map(function (r) { return Number(r.value || 0); }) }],
            xaxis: { categories: requestsData.map(function (r) { return r.label; }) },
            noData: { text: 'No request data' },
            colors: ['#13AE9C'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
        });

        renderChart('statusChart', {
            chart: { type: 'donut', height: 320 },
            series: Object.values(statusData).map(function (v) { return Number(v || 0); }),
            labels: Object.keys(statusData),
            noData: { text: 'No status data' },
            legend: { position: 'bottom' },
            colors: ['#13AE9C', '#34c38f', '#f1b44c', '#f46a6a', '#50a5f1', '#74788d', '#e83e8c'],
        });

        renderChart('pharmacyChart', {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{
                name: 'Pharmacies',
                data: [
                    Number(pharmacyData.open || 0),
                    Number(pharmacyData.closed || 0),
                    Number(pharmacyData.verified || 0),
                    Number(pharmacyData.unverified || 0),
                ],
            }],
            xaxis: { categories: ['Open', 'Closed', 'Verified', 'Unverified'] },
            noData: { text: 'No pharmacy data' },
            colors: ['#13AE9C'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
            dataLabels: { enabled: false },
        });
    }

    function bootDashboardCharts() {
        if (!document.getElementById('dashboard-chart-data')) return;
        renderDashboardCharts();
    }

    document.addEventListener('DOMContentLoaded', bootDashboardCharts);
    document.addEventListener('livewire:navigated', bootDashboardCharts);

    if (window.Livewire && typeof window.Livewire.hook === 'function') {
        window.Livewire.hook('message.processed', function () {
            bootDashboardCharts();
        });
    }
})();
