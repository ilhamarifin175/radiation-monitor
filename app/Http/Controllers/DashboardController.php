<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\MonitorDalam;
use App\Models\MonitorLuar;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index() {
        $currentDate = Carbon::now()->isoFormat('D MMMM YYYY');
        $currentDay  = Carbon::now()->isoFormat('dddd');

        return view('dashboard', [
            "title"       => "Dashboard",
            "currentDate" => $currentDate,
            "currentDay"  => $currentDay,
        ]);
    }

    public function getDoseRateChart() {
        $doseRateDalam = MonitorDalam::select('timestamp', 'usvh')
                            ->latestFirst()
                            ->take(30)
                            ->get()
                            ->reverse()
                            ->values();

        $doseRateLuar = MonitorLuar::select('timestamp', 'usvh')
                            ->latestFirst()
                            ->take(30)
                            ->get()
                            ->reverse()
                            ->values();

        return response()->json([
            'doseRateOutdoor' => $doseRateLuar,
            'doseRateIndoor'  => $doseRateDalam
        ]);
    }

    public function getLatestDoseRate() {
        $doseRateLuar  = MonitorLuar::latestFirst()->first();
        $doseRateDalam = MonitorDalam::latestFirst()->first();

        return response()->json([
            'doseRateOutdoor' => $doseRateLuar,
            'doseRateIndoor'  => $doseRateDalam
        ]);
    }

    public function getHighestDoseRate() {
        $time = Carbon::now()->format('Y-m-d');

        $highestLuar  = MonitorLuar::whereDate('timestamp', $time)->max('usvh') ?? 0;
        $highestDalam = MonitorDalam::whereDate('timestamp', $time)->max('usvh') ?? 0;

        return response()->json([
            'highest_luar'  => $highestLuar,
            'highest_dalam' => $highestDalam,
        ]);
    }
}
