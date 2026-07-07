$(document).ready(function () {
    var apiKey = document.querySelector('meta[name="api-key"]').getAttribute('content');

    if (window.location.pathname !== '/') return;

    var MAX_HISTORY_MS      = 3600 * 1000;
    var allOutdoorData      = [];
    var allIndoorData       = [];
    var lastReceivedTs      = 0;
    var lastOutdoorServerTs = '';
    var lastIndoorServerTs  = '';

    /* ── Format datetime untuk query server ────────────────── */
    function toDatetimeStr(d) {
        return d.getFullYear() + '-' +
            String(d.getMonth() + 1).padStart(2, '0') + '-' +
            String(d.getDate()).padStart(2, '0') + ' ' +
            String(d.getHours()).padStart(2, '0') + ':' +
            String(d.getMinutes()).padStart(2, '0') + ':' +
            String(d.getSeconds()).padStart(2, '0');
    }

    /* ── Warna border & label status kartu ─────────────────── */
    function setCardStyle(cardId, statusId, value, threshold) {
        var card   = $('#' + cardId);
        var status = $('#' + statusId);
        card.removeClass('border-left-success border-left-danger border-left-info');
        if (parseFloat(value) >= threshold) {
            card.addClass('border-left-danger');
            status.html('<small class="text-danger font-weight-bold">&#9888; Melebihi Ambang</small>');
        } else {
            card.addClass('border-left-success');
            status.html('<small class="text-success">Normal</small>');
        }
    }

    /* ── Kartu nilai terkini ───────────────────────────────── */
    function updateCards(outdoorLatest, indoorLatest) {
        if (outdoorLatest && outdoorLatest.usvh !== undefined) {
            var val = parseFloat(outdoorLatest.usvh);
            $('#outdoor-dose-rate').html(val.toFixed(2));
            setCardStyle('card-outdoor-dose', 'outdoor-dose-status', val, 0.5);
        }
        if (indoorLatest && indoorLatest.usvh !== undefined) {
            var val = parseFloat(indoorLatest.usvh);
            $('#indoor-dose-rate').html(val.toFixed(2));
            setCardStyle('card-indoor-dose', 'indoor-dose-status', val, 10);
        }
    }

    function updateHighestCard(outdoorData, indoorData) {
        var maxLuar = 0, maxDalam = 0;
        outdoorData.forEach(function (d) { if (parseFloat(d.usvh) > maxLuar)  maxLuar  = parseFloat(d.usvh); });
        indoorData.forEach(function  (d) { if (parseFloat(d.usvh) > maxDalam) maxDalam = parseFloat(d.usvh); });

        $('#outdoor-highest-dose-rate').html(maxLuar  > 0 ? maxLuar.toFixed(2)  : '-');
        $('#indoor-highest-dose-rate').html(maxDalam > 0 ? maxDalam.toFixed(2) : '-');

        if (maxLuar  > 0) setCardStyle('card-outdoor-highest', 'outdoor-highest-status', maxLuar,  0.5);
        if (maxDalam > 0) setCardStyle('card-indoor-highest',  'indoor-highest-status',  maxDalam, 10);
    }

    /* ── Buzzer alarm (MP3) ─────────────────────────────────── */
    var alarmSound = document.getElementById('alarm-sound');

    if (alarmSound) {
        alarmSound.muted = true;
        alarmSound.play().catch(function () {});
    }

    function startAlarm() {
        if (!alarmSound) return;
        alarmSound.muted = false;
        if (alarmSound.paused) alarmSound.play().catch(function () {});
    }

    function stopAlarm() {
        if (!alarmSound) return;
        alarmSound.muted = true;
        alarmSound.currentTime = 0;
    }

    /* ── Status relay & waktu data terakhir ────────────────── */
    function updateRelayStatus(relayVal) {
        var badge = $('#relay-status-badge');
        var card  = $('#card-relay-status');
        card.removeClass('border-left-success border-left-danger');
        if (relayVal === 'OFF') {
            badge.removeClass('badge-success').addClass('badge-danger').html('⚠ AKTIF');
            card.addClass('border-left-danger');
            startAlarm();
        } else {
            badge.removeClass('badge-danger').addClass('badge-success').html('✓ AMAN');
            card.addClass('border-left-success');
            stopAlarm();
        }
    }

    function updateLastDataTime(timestamp) {
        var ts = new Date(timestamp).getTime();
        if (ts <= lastReceivedTs) return;
        lastReceivedTs = ts;

        var d   = new Date(ts);
        var hh  = String(d.getHours()).padStart(2, '0');
        var mm  = String(d.getMinutes()).padStart(2, '0');
        var ss  = String(d.getSeconds()).padStart(2, '0');
        $('#last-data-time').html(hh + ':' + mm + ':' + ss);

        var diffSec = Math.floor((Date.now() - ts) / 1000);
        var rel = diffSec < 60
            ? diffSec + ' detik lalu'
            : Math.floor(diffSec / 60) + ' menit lalu';
        $('#last-data-relative').html(rel);
    }

    function checkToast(outdoorLatest, indoorLatest) {
        var over = (outdoorLatest && parseFloat(outdoorLatest.usvh) > 0.5)
                || (indoorLatest  && parseFloat(indoorLatest.usvh)  > 10);
        if (over) { $('.toast').toast('show'); }
        else      { $('.toast').toast('hide'); }
    }

    /* ── Refresh tampilan "X detik/menit lalu" setiap detik ─── */
    function refreshRelativeTime() {
        if (!lastReceivedTs) return;
        var diffSec = Math.floor((Date.now() - lastReceivedTs) / 1000);
        var rel = diffSec < 60
            ? diffSec + ' detik lalu'
            : Math.floor(diffSec / 60) + ' menit lalu';
        $('#last-data-relative').html(rel);
    }

    /* ── Bantu trim data lama dari array ───────────────────────── */
    function trimOldData(arr, latestTs) {
        var cutoff = latestTs - MAX_HISTORY_MS;
        while (arr.length > 0 && new Date(arr[0].created_at).getTime() < cutoff) {
            arr.shift();
        }
    }

    /* ── Poll server tiap 2 detik — ambil 1 record terbaru ────── */
    function pollLatest() {
        $.ajax({
            url: 'latest-outdoor-cps',
            headers: { 'Api-Key': apiKey },
            method: 'GET',
            cache: false,
            success: function (latest) {
                if (!latest || Array.isArray(latest) || !latest.created_at) return;
                if (latest.is_backfill != 0) return;
                var ts    = new Date(latest.created_at).getTime();
                var prevTs = allOutdoorData.length > 0
                    ? new Date(allOutdoorData[allOutdoorData.length - 1].created_at).getTime()
                    : 0;
                if (ts <= prevTs) return;
                allOutdoorData.push(latest);
                trimOldData(allOutdoorData, ts);
                updateCards(latest, null);
                updateLastDataTime(latest.created_at);
                var inLatest = allIndoorData.length > 0 ? allIndoorData[allIndoorData.length - 1] : null;
                checkToast(latest, inLatest);
                setDashboardSeries(allOutdoorData, allIndoorData);
                updateHighestCard(allOutdoorData, allIndoorData);
            },
            error: function (xhr) { console.warn('outdoor poll error:', xhr.status); }
        });

        $.ajax({
            url: 'latest-indoor-cps',
            headers: { 'Api-Key': apiKey },
            method: 'GET',
            cache: false,
            success: function (latest) {
                if (!latest || Array.isArray(latest) || !latest.created_at) return;
                if (latest.is_backfill != 0) return;
                var ts    = new Date(latest.created_at).getTime();
                var prevTs = allIndoorData.length > 0
                    ? new Date(allIndoorData[allIndoorData.length - 1].created_at).getTime()
                    : 0;
                if (ts <= prevTs) return;
                allIndoorData.push(latest);
                trimOldData(allIndoorData, ts);
                updateCards(null, latest);
                updateRelayStatus(latest.relay);
                updateLastDataTime(latest.created_at);
                var outLatest = allOutdoorData.length > 0 ? allOutdoorData[allOutdoorData.length - 1] : null;
                checkToast(outLatest, latest);
                setDashboardSeries(allOutdoorData, allIndoorData);
                updateHighestCard(allOutdoorData, allIndoorData);
            },
            error: function (xhr) { console.warn('indoor poll error:', xhr.status); }
        });
    }

    /* ── Load 1 jam terakhir saat init ─────────────────────── */
    function loadInitial() {
        var now  = new Date();
        var from = new Date(now.getTime() - MAX_HISTORY_MS);
        var params = { from: toDatetimeStr(from), to: toDatetimeStr(now) };

        var outdoorDone = false, indoorDone = false;
        var tempOutdoor = [], tempIndoor = [];

        function checkBothDone() {
            if (!outdoorDone || !indoorDone) return;
            allOutdoorData = tempOutdoor;
            allIndoorData  = tempIndoor;

            var outdoorLatest = allOutdoorData.length > 0 ? allOutdoorData[allOutdoorData.length - 1] : null;
            var indoorLatest  = allIndoorData.length  > 0 ? allIndoorData[allIndoorData.length  - 1] : null;

            updateCards(outdoorLatest, indoorLatest);
            updateHighestCard(allOutdoorData, allIndoorData);
            if (indoorLatest) updateRelayStatus(indoorLatest.relay);
            if (indoorLatest) updateLastDataTime(indoorLatest.created_at);
            else if (outdoorLatest) updateLastDataTime(outdoorLatest.created_at);
            setDashboardSeries(allOutdoorData, allIndoorData);
            setDashboardAnnotations();

            var fallback = toDatetimeStr(new Date(Date.now() - 15000)); // 15 detik lalu

            lastOutdoorServerTs = tempOutdoor.length > 0
                ? tempOutdoor[tempOutdoor.length - 1].created_at
                : fallback;

            lastIndoorServerTs = tempIndoor.length > 0
                ? tempIndoor[tempIndoor.length - 1].created_at
                : fallback;
        }

        $.ajax({
            url: 'outdoor-history',
            headers: { 'Api-Key': apiKey },
            data: params,
            method: 'GET',
            success: function (data) {
                tempOutdoor = Array.isArray(data)
                    ? data.filter(function (r) { return r.is_backfill == 0; })
                    : [];
                outdoorDone = true;
                checkBothDone();
            },
            error: function () { outdoorDone = true; checkBothDone(); }
        });

        $.ajax({
            url: 'indoor-history',
            headers: { 'Api-Key': apiKey },
            data: params,
            method: 'GET',
            success: function (data) {
                tempIndoor = Array.isArray(data)
                    ? data.filter(function (r) { return r.is_backfill == 0; })
                    : [];
                indoorDone = true;
                checkBothDone();
            },
            error: function () { indoorDone = true; checkBothDone(); }
        });
    }

    /* ── Init ──────────────────────────────────────────────── */
    initDashboardChart();
    loadInitial();
    setInterval(pollLatest,          2000);
    setInterval(refreshRelativeTime, 1000);
});
