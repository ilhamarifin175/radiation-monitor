$(document).ready(function () {
    var apiKey = document.querySelector('meta[name="api-key"]').getAttribute('content');

    if (window.location.pathname !== '/') return;

    var MAX_HISTORY_MS      = 3600 * 1000;
    var allOutdoorData      = [];
    var allIndoorData       = [];
    var lastReceivedTs      = 0;
    var outdoorBuffer       = [];
    var indoorBuffer        = [];
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

    /* ── Poll server tiap 10 detik — ambil semua baris baru ─── */
    function pollLatest() {
        if (!lastOutdoorServerTs) return;
        $.ajax({
            url: 'latest-outdoor-cps',
            headers: { 'Api-Key': apiKey },
            data: { after: lastOutdoorServerTs },
            method: 'GET',
            success: function (rows) {
                if (!Array.isArray(rows) || rows.length === 0) return;
                // Cursor maju dari SEMUA baris (termasuk backfill) agar tidak terjebak
                lastOutdoorServerTs = rows[rows.length - 1].timestamp;
                // Hanya masukkan ke buffer baris yang segar (< 5 menit lalu)
                var cutoff = new Date(Date.now() - 5 * 60 * 1000);
                rows.filter(function(r) { return new Date(r.timestamp) >= cutoff; })
                    .forEach(function(r) { outdoorBuffer.push(r); });
            },
            error: function (xhr) { console.log('outdoor poll error:', xhr.responseText); }
        });

        if (!lastIndoorServerTs) return;
        $.ajax({
            url: 'latest-indoor-cps',
            headers: { 'Api-Key': apiKey },
            data: { after: lastIndoorServerTs },
            method: 'GET',
            success: function (rows) {
                if (!Array.isArray(rows) || rows.length === 0) return;
                lastIndoorServerTs = rows[rows.length - 1].timestamp;
                var cutoff = new Date(Date.now() - 5 * 60 * 1000);
                rows.filter(function(r) { return new Date(r.timestamp) >= cutoff; })
                    .forEach(function(r) { indoorBuffer.push(r); });
            },
            error: function (xhr) { console.log('indoor poll error:', xhr.responseText); }
        });
    }

    /* ── Drain buffer tiap 1 detik — tampilkan 1 baris ke grafik */
    function drainBuffer() {
        var outdoorItem = outdoorBuffer.length > 0 ? outdoorBuffer.shift() : null;
        var indoorItem  = indoorBuffer.length  > 0 ? indoorBuffer.shift()  : null;
        var changed = false;

        if (outdoorItem) {
            allOutdoorData.push(outdoorItem);
            var outTs = new Date(outdoorItem.timestamp).getTime();
            while (allOutdoorData.length > 0 &&
                   new Date(allOutdoorData[0].timestamp).getTime() < outTs - MAX_HISTORY_MS) {
                allOutdoorData.shift();
            }
            updateCards(outdoorItem, null);
            updateLastDataTime(outdoorItem.timestamp);
            changed = true;
        }

        if (indoorItem) {
            allIndoorData.push(indoorItem);
            var inTs = new Date(indoorItem.timestamp).getTime();
            while (allIndoorData.length > 0 &&
                   new Date(allIndoorData[0].timestamp).getTime() < inTs - MAX_HISTORY_MS) {
                allIndoorData.shift();
            }
            updateCards(null, indoorItem);
            updateRelayStatus(indoorItem.relay);
            updateLastDataTime(indoorItem.timestamp);
            changed = true;
        }

        if (changed) {
            var outdoorLatest = allOutdoorData.length > 0 ? allOutdoorData[allOutdoorData.length - 1] : null;
            var indoorLatest  = allIndoorData.length  > 0 ? allIndoorData[allIndoorData.length  - 1] : null;
            checkToast(outdoorLatest, indoorLatest);
            setDashboardSeries(allOutdoorData, allIndoorData);
            updateHighestCard(allOutdoorData, allIndoorData);
        }
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
            if (indoorLatest) updateLastDataTime(indoorLatest.timestamp);
            else if (outdoorLatest) updateLastDataTime(outdoorLatest.timestamp);
            setDashboardSeries(allOutdoorData, allIndoorData);
            setDashboardAnnotations();

            // Selalu mulai polling dari SEKARANG, bukan dari timestamp data terakhir.
            // Jika lastTs diambil dari DB dan kebetulan isinya data backfill (jam lama),
            // poll berikutnya akan mengembalikan semua baris backfill dan membanjiri chart.
            lastOutdoorServerTs = toDatetimeStr(new Date());
            lastIndoorServerTs  = toDatetimeStr(new Date());
        }

        $.ajax({
            url: 'outdoor-history',
            headers: { 'Api-Key': apiKey },
            data: params,
            method: 'GET',
            success: function (data) {
                tempOutdoor = Array.isArray(data) ? data : [];
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
                tempIndoor = Array.isArray(data) ? data : [];
                indoorDone = true;
                checkBothDone();
            },
            error: function () { indoorDone = true; checkBothDone(); }
        });
    }

    /* ── Init ──────────────────────────────────────────────── */
    initDashboardChart();
    loadInitial();
    setInterval(pollLatest,        10000);
    setInterval(drainBuffer,        1000);
    setInterval(refreshRelativeTime, 1000);
});
