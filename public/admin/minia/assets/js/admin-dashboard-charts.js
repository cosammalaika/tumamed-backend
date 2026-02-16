(function () {
    var payloadElement = document.getElementById('dashboard-chart-data');
    if (!payloadElement || typeof window.ApexCharts === 'undefined') {
        return;
    }

    var payload;
    try {
        payload = JSON.parse(payloadElement.textContent || '{}');
    } catch (e) {
        return;
    }

    var requestsData = Array.isArray(payload.requestsByMonth) ? payload.requestsByMonth : [];
    var statusData = payload.statusCounts && typeof payload.statusCounts === 'object' ? payload.statusCounts : {};
    var pharmacyData = payload.pharmacyActivity && typeof payload.pharmacyActivity === 'object' ? payload.pharmacyActivity : {};

    function renderSparkline(id, value) {
        var node = document.getElementById(id);
        if (!node) return;
        node.innerHTML = '';
        new window.ApexCharts(node, {
            chart: { type: 'line', height: 60, sparkline: { enabled: true } },
            stroke: { width: 2, curve: 'smooth' },
            series: [{ data: [value, value + 1, value - 1, value + 2, value] }],
            colors: ['#556ee6'],
            tooltip: { enabled: false },
        }).render();
    }

    renderSparkline('spark-hospitals', Number((payload.hospitalCount || requestsData.length || 0)));
    renderSparkline('spark-pharmacies', Number((payload.pharmacyCount || Object.keys(statusData).length || 0)));
    renderSparkline('spark-active', Number((payload.activeRequests || 0)));
    renderSparkline('spark-delivered', Number((payload.deliveredLast30 || 0)));

    var requestsNode = document.getElementById('requestsChart');
    if (requestsNode) {
        requestsNode.innerHTML = '';
        new window.ApexCharts(requestsNode, {
            chart: { type: 'area', height: 320, toolbar: { show: false } },
            series: [{ name: 'Requests', data: requestsData.map(function (r) { return r.value; }) }],
            xaxis: { categories: requestsData.map(function (r) { return r.label; }) },
            colors: ['#556ee6'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
        }).render();
    }

    var statusNode = document.getElementById('statusChart');
    if (statusNode) {
        statusNode.innerHTML = '';
        new window.ApexCharts(statusNode, {
            chart: { type: 'donut', height: 320 },
            series: Object.values(statusData).map(function (v) { return Number(v); }),
            labels: Object.keys(statusData),
            legend: { position: 'bottom' },
            colors: ['#556ee6', '#34c38f', '#f1b44c', '#f46a6a', '#50a5f1', '#74788d', '#e83e8c'],
        }).render();
    }

    var pharmacyNode = document.getElementById('pharmacyChart');
    if (pharmacyNode) {
        pharmacyNode.innerHTML = '';
        new window.ApexCharts(pharmacyNode, {
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
            colors: ['#34c38f'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
            dataLabels: { enabled: false },
        }).render();
    }
})();
