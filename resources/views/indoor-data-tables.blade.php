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
            <h6 class="m-0 font-weight-bold text-info">Monitor Dalam</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" style="display:none">
                    <thead>
                        <tr>
                            <th>Waktu Ukur</th>
                            <th>Seq</th>
                            <th>CPS</th>
                            <th>Laju Dosis (&#181;Sv/jam)</th>
                            <th>Suhu (&#8451;)</th>
                            <th>Kelembapan (%)</th>
                            <th>Relay</th>
                            <th>Jaringan</th>
                            <th>RSSI (dBm)</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Waktu Ukur</th>
                            <th>Seq</th>
                            <th>CPS</th>
                            <th>Laju Dosis</th>
                            <th>Suhu</th>
                            <th>Kelembapan</th>
                            <th>Relay</th>
                            <th>Jaringan</th>
                            <th>RSSI</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ $item->timestamp }}</td>
                                <td>{{ $item->seq }}</td>
                                <td>{{ $item->cps }}</td>
                                <td>{{ $item->usvh }}</td>
                                <td>{{ $item->suhu }}</td>
                                <td>{{ $item->kelembapan }}</td>
                                <td>
                                    <span class="badge badge-{{ $item->relay === 'ON' ? 'success' : 'secondary' }}">
                                        {{ $item->relay }}
                                    </span>
                                </td>
                                <td>{{ $item->jaringan }}</td>
                                <td>{{ $item->rssi ?? '-' }}</td>
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
