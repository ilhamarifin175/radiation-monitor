@extends('layouts.main')
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 ml-3 mb-1 text-gray-800">Monitor Luar</h1>
        <div class="d-none d-sm-inline-block shadow-sm">
            <p class="mr-3 ml-3 mb-2 mt-2">{{ $currentDay }}, {{ $currentDate }}</p>
        </div>
    </div>

    {{-- Kartu Nilai Terkini --}}
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text font-weight-bold text-info text-uppercase mb-2">Laju Dosis</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <span id="outdoor-dose-rate">{{ trim(json_encode($latestData->original['usvh']),'"') }}</span>
                                <sup class="font-weight-normal">&#181;Sv/jam</sup>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text font-weight-bold text-success text-uppercase mb-2">Suhu</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <span id="outdoor-temperature">{{ trim(json_encode($latestData->original['suhu']),'"') }}</span>
                                <sup class="font-weight-normal">&#8451;</sup>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text font-weight-bold text-primary text-uppercase mb-2">Kelembapan</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <span id="outdoor-humidity">{{ trim(json_encode($latestData->original['kelembapan']),'"') }}</span>
                                <sup class="font-weight-normal">%</sup>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Rentang Waktu --}}
    <div class="row mb-2">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="font-weight-bold text-gray-700 mr-3">
                            <i class="fas fa-calendar-alt mr-1"></i> Rentang Waktu:
                        </span>
                        <select id="outdoor-time-range" class="form-control form-control-sm mr-2" style="width:200px;">
                            <option value="today">Hari Ini</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="7days">7 Hari Terakhir</option>
                            <option value="30days">30 Hari Terakhir</option>
                            <option value="custom">Pilih Tanggal Tertentu</option>
                        </select>
                        <input type="date" id="outdoor-custom-date"
                               class="form-control form-control-sm mr-2 d-none" style="width:160px;">
                        <span id="outdoor-live-indicator" class="badge badge-success">
                            <i class="fas fa-circle mr-1" style="font-size:.6rem;"></i> LIVE
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Laju Dosis</h6>
                    <small class="text-muted">Geser &#8596; untuk riwayat &bull; Scroll untuk zoom</small>
                </div>
                <div class="card-body">
                    <div id="outdoor-dose-rate-chart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">Suhu</h6>
                    <small class="text-muted">Geser &#8596; untuk riwayat &bull; Scroll untuk zoom</small>
                </div>
                <div class="card-body">
                    <div id="outdoor-temperature-chart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Kelembapan</h6>
                    <small class="text-muted">Geser &#8596; untuk riwayat &bull; Scroll untuk zoom</small>
                </div>
                <div class="card-body">
                    <div id="outdoor-humidity-chart"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
