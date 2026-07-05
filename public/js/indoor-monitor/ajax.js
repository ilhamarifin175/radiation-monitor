$(document).ready(function () {
    var apiKey = document.querySelector('meta[name="api-key"]').getAttribute('content');

    if (window.location.pathname !== '/indoor-monitor') return;

    var MAX_HISTORY_MS = 3600 * 1000;
    var allData        = [];
    var pollInterval   = null;

    /* ── Format datetime untuk query server ────────────────── */
    function toDatetimeStr(d) {
        return d.getFullYear() + '-' +
            String(d.getMonth() + 1).padStart(2, '0') + '-' +
            String(d.getDate()).padStart(2, '0') + ' ' +
            String(d.getHours()).padStart(2, '0') + ':' +
            String(d.getMinutes()).padStart(2, '0') + ':' +
            String(d.getSeconds()).padStart(2, '0');
    }

    /* ── Hitung from / to dari range key ───────────────────── */
    function getRangeParams(rangeKey, customDate) {
        var now = new Date();
        var from, to;

        switch (rangeKey) {
            case 'today':
                from = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);
                to   = now;
                break;
            case 'yesterday':
                var y = new Date(now);
                y.setDate(y.getDate() - 1);
                from = new Date(y.getFullYear(), y.getMonth(), y.getDate(), 0, 0, 0);
                to   = new Date(y.getFullYear(), y.getMonth(), y.getDate(), 23, 59, 59);
                break;
            case '7days':
                from = new Date(now.getTime() - 7 * 24 * 3600 * 1000);
                to   = now;
                break;
            case '30days':
                from = new Date(now.getTime() - 30 * 24 * 3600 * 1000);
                to   = now;
                break;
            case 'custom':
                var p = customDate.split('-');
                from  = new Date(+p[0], +p[1] - 1, +p[2], 0, 0, 0);
                to    = new Date(+p[0], +p[1] - 1, +p[2], 23, 59, 59);
                break;
            default:
                from = new Date(now.getTime() - MAX_HISTORY_MS);
                to   = now;
        }

        return { from: toDatetimeStr(from), to: toDatetimeStr(to) };
    }

    /* ── Kartu nilai terkini ───────────────────────────────── */
    function updateCards(d) {
        if (!d) return;
        $('#indoor-dose-rate').html(d.usvh         !== null ? parseFloat(d.usvh).toFixed(2)       : '-');
        $('#indoor-temperature').html(d.suhu        !== null ? parseFloat(d.suhu).toFixed(1)       : '-');
        $('#indoor-humidity').html(d.kelembapan     !== null ? parseFloat(d.kelembapan).toFixed(1) : '-');
    }

    /* ── Polling (hanya mode Hari Ini) ─────────────────────── */
    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(pollLatest, 1000);
        $('#indoor-live-indicator').removeClass('d-none');
    }

    function stopPolling() {
        if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
        $('#indoor-live-indicator').addClass('d-none');
    }

    function pollLatest() {
        $.ajax({
            url: 'latest-indoor-cps',
            headers: { 'Api-Key': apiKey },
            method: 'GET',
            success: function (latest) {
                if (!latest || !latest.timestamp) return;

                var newTs  = new Date(latest.timestamp).getTime();
                var lastTs = allData.length > 0
                    ? new Date(allData[allData.length - 1].timestamp).getTime()
                    : 0;

                if (newTs > lastTs) {
                    allData.push(latest);

                    var prevLen = allData.length;
                    var cutoff  = newTs - MAX_HISTORY_MS;
                    while (allData.length > 0 && new Date(allData[0].timestamp).getTime() < cutoff) {
                        allData.shift();
                    }

                    updateCards(latest);

                    if (allData.length < prevLen) {
                        setIndoorSeries(allData);
                        setIndoorAnnotations(allData);
                    } else {
                        appendIndoorPoint(latest);
                        if (latest.relay === 'OFF') {
                            setIndoorAnnotations(allData);
                        }
                    }
                }
            },
            error: function (xhr) { console.log('pollLatest error:', xhr.responseText); }
        });
    }

    /* ── Load data sesuai rentang ───────────────────────────── */
    function loadData(rangeKey, customDate) {
        var params = getRangeParams(rangeKey, customDate);

        $.ajax({
            url: 'indoor-history',
            headers: { 'Api-Key': apiKey },
            data: { from: params.from, to: params.to },
            method: 'GET',
            success: function (data) {
                allData = Array.isArray(data) ? data : [];
                if (allData.length === 0) {
                    setIndoorNoData('Data Kosong');
                    return;
                }
                updateCards(allData[allData.length - 1]);
                setIndoorSeries(allData);
                setIndoorXRange(rangeKey, allData);
                setIndoorAnnotations(allData);
            },
            error: function (xhr) { console.log('loadData error:', xhr.responseText); }
        });
    }

    /* ── Terapkan filter ────────────────────────────────────── */
    function applyFilter(rangeKey, customDate) {
        stopPolling();
        loadData(rangeKey, customDate);
        if (rangeKey === 'today') startPolling();
    }

    /* ── Event dropdown & date picker ──────────────────────── */
    $('#indoor-time-range').on('change', function () {
        var val = $(this).val();
        if (val === 'custom') {
            $('#indoor-custom-date').removeClass('d-none');
            return;
        }
        $('#indoor-custom-date').addClass('d-none');
        applyFilter(val);
    });

    $('#indoor-custom-date').on('change', function () {
        var dateVal = $(this).val();
        if (dateVal) applyFilter('custom', dateVal);
    });

    /* ── Init ──────────────────────────────────────────────── */
    initIndoorCharts();
    applyFilter('today');
});
