@extends('layouts.main')
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 ml-3 mb-1 text-gray-800">Data Table</h1>
        <div class="d-none d-sm-inline-block shadow-sm">
            <p class="mr-3 ml-3 mb-2 mt-2" id="current-datetime"></p>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">Log Kualitas Jaringan</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" style="display:none">
                    <thead>
                        <tr>
                            <th>Waktu Ukur</th>
                            <th>WiFi Diterima (paket)</th>
                            <th>WiFi Hilang (paket)</th>
                            <th>PDR WiFi (%)</th>
                            <th>LoRa Diterima (paket)</th>
                            <th>LoRa Hilang (paket)</th>
                            <th>PDR LoRa (%)</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Waktu Ukur</th>
                            <th>WiFi Diterima</th>
                            <th>WiFi Hilang</th>
                            <th>PDR WiFi</th>
                            <th>LoRa Diterima</th>
                            <th>LoRa Hilang</th>
                            <th>PDR LoRa</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ $item->timestamp }}</td>
                                <td>{{ $item->wifi_terima }}</td>
                                <td>{{ $item->wifi_hilang }}</td>
                                <td>{{ number_format($item->wifi_pdr, 2) }}</td>
                                <td>{{ $item->lora_terima }}</td>
                                <td>{{ $item->lora_hilang }}</td>
                                <td>{{ number_format($item->lora_pdr, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        function updateDatetime() {
            var now  = new Date();
            var hari = now.toLocaleDateString('id-ID', { weekday: 'long' });
            var tgl  = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('current-datetime').textContent = hari + ', ' + tgl;
        }
        updateDatetime();
        setInterval(updateDatetime, 60000);
    })();
</script>
@endpush
@endsection
