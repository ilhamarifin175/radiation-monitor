/* ── ApexCharts — Outdoor Monitor ───────────────────────────── */
var outdoorCharts = {};

function buildApexChart(divId, label, color) {
    var el = document.getElementById(divId);
    if (!el) return null;

    var chart = new ApexCharts(el, {
        chart: {
            id: divId,
            type: 'area',
            height: 250,
            animations: { enabled: false },
            toolbar: {
                show: false,
                autoSelected: 'zoom',
                tools: {
                    download: false,
                    selection: false,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                }
            },
            zoom: { enabled: true, type: 'x' }
        },
        series: [{ name: label, data: [] }],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.0 }
        },
        colors: [color],
        xaxis: {
            type: 'datetime',
            labels: { format: 'HH:mm:ss', datetimeUTC: false }
        },
        yaxis: {
            labels: {
                formatter: function (v) { return parseFloat(v).toFixed(2); }
            }
        },
        tooltip: {
            x: { format: 'HH:mm:ss' },
            theme: 'light'
        },
        grid: { borderColor: '#e0e0e0', strokeDashArray: 3 },
        markers: { size: 0 },
        noData: { text: 'Memuat data...' }
    });

    chart.render();
    return chart;
}

function initOutdoorCharts() {
    outdoorCharts.usvh       = buildApexChart('outdoor-dose-rate-chart',   'Laju Dosis', '#f6c23e');
    outdoorCharts.suhu       = buildApexChart('outdoor-temperature-chart', 'Suhu',       '#1cc88a');
    outdoorCharts.kelembapan = buildApexChart('outdoor-humidity-chart',    'Kelembapan', '#4e73df');
}

function setOutdoorSeries(allData) {
    function toSeries(field) {
        return allData.map(function (d) {
            return [new Date(d.timestamp).getTime(), parseFloat(d[field])];
        });
    }

    if (outdoorCharts.usvh)       outdoorCharts.usvh.updateSeries([{ name: 'Laju Dosis', data: toSeries('usvh') }], false);
    if (outdoorCharts.suhu)       outdoorCharts.suhu.updateSeries([{ name: 'Suhu', data: toSeries('suhu') }], false);
    if (outdoorCharts.kelembapan) outdoorCharts.kelembapan.updateSeries([{ name: 'Kelembapan', data: toSeries('kelembapan') }], false);
}

function setOutdoorXRange(rangeKey, data) {
    var charts = [outdoorCharts.usvh, outdoorCharts.suhu, outdoorCharts.kelembapan];

    var fmt;
    switch (rangeKey) {
        case 'today':      fmt = 'HH:mm:ss'; break;
        case 'yesterday':  fmt = 'HH:mm';    break;
        case '7days':      fmt = 'dd MMM HH:mm'; break;
        case '30days':     fmt = 'dd MMM';   break;
        case 'custom':     fmt = 'HH:mm';    break;
        default:           fmt = 'HH:mm:ss';
    }

    if (rangeKey === 'today') {
        charts.forEach(function (c) {
            if (c) c.updateOptions({
                chart: { toolbar: { show: false } },
                xaxis: {
                    range: 3600 * 1000,
                    min: null,
                    max: null,
                    labels: { format: fmt, datetimeUTC: false }
                }
            }, false, false);
        });
    } else if (data && data.length > 0) {
        var minTs = new Date(data[0].timestamp).getTime();
        var maxTs = new Date(data[data.length - 1].timestamp).getTime();
        charts.forEach(function (c) {
            if (!c) return;
            c.updateOptions({
                chart: { toolbar: { show: true, autoSelected: 'zoom' } },
                xaxis: {
                    range: null,
                    min: minTs,
                    max: maxTs,
                    labels: { format: fmt, datetimeUTC: false }
                }
            }, false, false);
        });
    }
}

/* ── Garis ambang Laju Dosis (Monitor Luar = 0.5 µSv/jam) ─── */
function setOutdoorNoData(message) {
    [outdoorCharts.usvh, outdoorCharts.suhu, outdoorCharts.kelembapan].forEach(function (c) {
        if (!c) return;
        c.updateSeries([{ data: [] }], false);
        c.updateOptions({ noData: { text: message } }, false, false);
    });
}

function setOutdoorAnnotations() {
    if (!outdoorCharts.usvh) return;
    outdoorCharts.usvh.updateOptions({
        annotations: {
            yaxis: [{
                y: 0.5,
                borderColor: '#e74a3b',
                borderWidth: 2,
                strokeDashArray: 6,
                label: {
                    text: 'Ambang 0.5 µSv/jam',
                    borderColor: '#e74a3b',
                    position: 'right',
                    style: {
                        color: '#fff',
                        background: '#e74a3b',
                        fontSize: '11px',
                        padding: { top: 2, bottom: 2, left: 6, right: 6 }
                    }
                }
            }]
        }
    }, false, false);
}

function appendOutdoorPoint(d) {
    var ts = new Date(d.timestamp).getTime();

    function ap(chart, field) {
        if (chart) chart.appendData([{ data: [[ts, parseFloat(d[field])]] }]);
    }

    ap(outdoorCharts.usvh,       'usvh');
    ap(outdoorCharts.suhu,       'suhu');
    ap(outdoorCharts.kelembapan, 'kelembapan');
}
