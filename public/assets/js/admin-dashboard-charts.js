(function () {
    const payloadElement = document.getElementById('dashboard-chart-data');
    if (!payloadElement || typeof window.Chart === 'undefined') {
        return;
    }

    let payload = null;
    try {
        payload = JSON.parse(payloadElement.textContent || '{}');
    } catch (e) {
        return;
    }

    const requestsData = Array.isArray(payload.requestsByMonth) ? payload.requestsByMonth : [];
    const statusData = payload.statusCounts && typeof payload.statusCounts === 'object' ? payload.statusCounts : {};
    const pharmacyData = payload.pharmacyActivity && typeof payload.pharmacyActivity === 'object' ? payload.pharmacyActivity : {};

    const palette = {
        blue: '#2563eb',
        teal: '#14b8a6',
        indigo: '#4f46e5',
        gray: '#e5e7eb',
        green: '#22c55e',
    };

    const reqCtx = document.getElementById('requestsChart');
    if (reqCtx) {
        new window.Chart(reqCtx, {
            type: 'line',
            data: {
                labels: requestsData.map((r) => r.label),
                datasets: [{
                    label: 'Requests',
                    data: requestsData.map((r) => r.value),
                    borderColor: palette.blue,
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusLabels = Object.keys(statusData);
        const statusValues = Object.values(statusData);
        const statusColors = [
            palette.blue, palette.teal, palette.indigo, '#f97316', palette.green, '#ef4444', '#94a3b8', '#0ea5e9', '#c084fc',
        ];
        new window.Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusColors.slice(0, statusValues.length),
                    borderWidth: 1,
                }],
            },
            options: {
                plugins: { legend: { position: 'bottom' } },
                cutout: '60%',
            },
        });
    }

    const pharmacyCtx = document.getElementById('pharmacyChart');
    if (pharmacyCtx) {
        new window.Chart(pharmacyCtx, {
            type: 'bar',
            data: {
                labels: ['Open', 'Closed', 'Verified', 'Unverified'],
                datasets: [{
                    label: 'Pharmacies',
                    data: [
                        Number(pharmacyData.open || 0),
                        Number(pharmacyData.closed || 0),
                        Number(pharmacyData.verified || 0),
                        Number(pharmacyData.unverified || 0),
                    ],
                    backgroundColor: [palette.teal, palette.gray, palette.blue, '#a5b4fc'],
                    borderRadius: 6,
                }],
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } },
                },
            },
        });
    }
})();
