/* ── ApexCharts — Dashboard (live 1 jam terakhir) ────────────── */
var dashboardChart = null;

function initDashboardChart() {
    var el = document.getElementById('dose-rate-chart');
    if (!el) return;

    dashboardChart = new ApexCharts(el, {
        chart: {
            id: 'dose-rate-chart',
            type: 'area',
            height: 320,
            animations: { enabled: false },
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        series: [
            { name: 'Monitor Luar', data: [] },
            { name: 'Monitor Dalam', data: [] }
        ],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.0 }
        },
        colors: ['#4e73df', '#f6c23e'],
        xaxis: {
            type: 'datetime',
            range: 3600 * 1000,
            labels: { format: 'HH:mm', datetimeUTC: false }
        },
        yaxis: {
            labels: {
                formatter: function (v) { return parseFloat(v).toFixed(2); }
            }
        },
        tooltip: {
            x: { format: 'HH:mm:ss' },
            theme: 'light',
            shared: true
        },
        legend: { show: false },
        grid: { borderColor: '#e0e0e0', strokeDashArray: 3 },
        markers: { size: 0 },
        noData: { text: 'Memuat data...' }
    });

    dashboardChart.render();
}

function setDashboardSeries(outdoorData, indoorData) {
    if (!dashboardChart) return;

    function toSeries(arr) {
        return arr.map(function (d) {
            return [new Date(d.timestamp).getTime(), parseFloat(d.usvh)];
        });
    }

    dashboardChart.updateSeries([
        { name: 'Monitor Luar', data: toSeries(outdoorData) },
        { name: 'Monitor Dalam', data: toSeries(indoorData) }
    ], false);
}

function setDashboardAnnotations() {
    if (!dashboardChart) return;
    dashboardChart.updateOptions({
        annotations: {
            yaxis: [
                {
                    y: 0.5,
                    borderColor: '#4e73df',
                    borderWidth: 2,
                    strokeDashArray: 6,
                    label: {
                        text: 'Ambang Luar 0.5 µSv/jam',
                        borderColor: '#4e73df',
                        position: 'right',
                        style: {
                            color: '#fff',
                            background: '#4e73df',
                            fontSize: '11px',
                            padding: { top: 2, bottom: 2, left: 6, right: 6 }
                        }
                    }
                },
                {
                    y: 10,
                    borderColor: '#e74a3b',
                    borderWidth: 2,
                    strokeDashArray: 6,
                    label: {
                        text: 'Ambang Dalam 10 µSv/jam',
                        borderColor: '#e74a3b',
                        position: 'right',
                        style: {
                            color: '#fff',
                            background: '#e74a3b',
                            fontSize: '11px',
                            padding: { top: 2, bottom: 2, left: 6, right: 6 }
                        }
                    }
                }
            ]
        }
    }, false, false);
}
