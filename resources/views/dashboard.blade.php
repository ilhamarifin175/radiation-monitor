@extends('layouts.main')
@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 ml-3 mb-1 text-gray-800">
                Dashboard
            </h1>
            <div class="d-none d-sm-inline-block shadow-sm">
                <p class="mr-3 ml-3 mb-2 mt-2" id="current-datetime">
                    {{ $currentDay }}, {{ $currentDate }}
                </p>
            </div>
        </div>

        <div class="row">
            {{-- Laju Dosis Monitor Dalam --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div id="card-indoor-dose" class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="font-weight-bold text-uppercase mb-1" style="font-size:1.1rem; color:#858796;">Laju Dosis<br>Monitor Dalam</div>
                                <div class="h1 mb-1 font-weight-bold text-gray-800">
                                    <span id="indoor-dose-rate">-</span>
                                    <sup class="font-weight-normal" style="font-size:.7rem;">&#181;Sv/jam</sup>
                                </div>
                                <div id="indoor-dose-status"></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-radiation fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Laju Dosis Monitor Luar --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div id="card-outdoor-dose" class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="font-weight-bold text-uppercase mb-1" style="font-size:1.1rem; color:#858796;">Laju Dosis<br>Monitor Luar</div>
                                <div class="h1 mb-1 font-weight-bold text-gray-800">
                                    <span id="outdoor-dose-rate">-</span>
                                    <sup class="font-weight-normal" style="font-size:.7rem;">&#181;Sv/jam</sup>
                                </div>
                                <div id="outdoor-dose-status"></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-radiation fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Tertinggi Hari Ini — Monitor Dalam --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div id="card-indoor-highest" class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="font-weight-bold text-uppercase mb-1" style="font-size:1.1rem; color:#858796;">Tertinggi Hari Ini &mdash; Monitor Dalam</div>
                                <div class="h1 mb-1 font-weight-bold text-gray-800">
                                    <span id="indoor-highest-dose-rate">-</span>
                                    <sup class="font-weight-normal" style="font-size:.7rem;">&#181;Sv/jam</sup>
                                </div>
                                <div id="indoor-highest-status"></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-arrow-up fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Tertinggi Hari Ini — Monitor Luar --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div id="card-outdoor-highest" class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="font-weight-bold text-uppercase mb-1" style="font-size:1.1rem; color:#858796;">Tertinggi Hari Ini &mdash; Monitor Luar</div>
                                <div class="h1 mb-1 font-weight-bold text-gray-800">
                                    <span id="outdoor-highest-dose-rate">-</span>
                                    <sup class="font-weight-normal" style="font-size:.7rem;">&#181;Sv/jam</sup>
                                </div>
                                <div id="outdoor-highest-status"></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-arrow-up fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-md-6 mb-4">
                <div id="card-relay-status" class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="font-weight-bold text-uppercase mb-1" style="font-size:1.1rem; color:#858796;">
                                    Status Alarm Monitor Dalam</div>
                                <div class="h4 mb-0 font-weight-bold">
                                    <span id="relay-status-badge"
                                        class="badge badge-secondary"
                                        style="font-size:2.5rem; padding:6px 14px;">
                                        -
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bell fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="font-weight-bold text-uppercase mb-1" style="font-size:1.1rem; color:#858796;">
                                    Data Terakhir Diterima</div>
                                <div class="h1 mb-0 font-weight-bold text-gray-800">
                                    <span id="last-data-time">-</span>
                                </div>
                                <div class="text-xs text-muted mt-1" id="last-data-relative"></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-info">
                            Laju Dosis
                            <span class="badge badge-success ml-2" style="font-size:10px;">&#9679; LIVE</span>
                        </h6>
                        <small class="text-muted">1 jam terakhir</small>
                    </div>
                    <div class="card-body">
                        <div id="dose-rate-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <audio id="alarm-sound" loop preload="auto">
        <source src="{{ asset('sounds/alarm.mp3') }}" type="audio/mpeg">
    </audio>

    @push('scripts')
    <script>
        function updateDatetime() {
            var now  = new Date();
            var hari = now.toLocaleDateString('id-ID', { weekday: 'long' });
            var tgl  = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('current-datetime').textContent = hari + ', ' + tgl;
        }
        updateDatetime();
        setInterval(updateDatetime, 60000);
    </script>
    @endpush
@endsection