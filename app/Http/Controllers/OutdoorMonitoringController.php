<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MonitorDalam;
use App\Models\MonitorLuar;

class OutdoorMonitoringController extends Controller
{
    public function index() {
        $currentDate = Carbon::now()->isoFormat('D MMMM YYYY');
        $currentDay  = Carbon::now()->isoFormat('dddd');
        $latestData  = $this->getLatestData();

        return view('outdoor-monitoring', [
            "title"       => "Monitor Luar",
            "currentDate" => $currentDate,
            "currentDay"  => $currentDay,
            "latestData"  => $latestData
        ]);
    }

    public function tables() {
        $data = $this->getTablesData();

        return view('outdoor-data-tables', [
            "title" => "Monitor Luar Data Tables",
            "data"  => $data
        ]);
    }

    public function storeData(Request $request) {
        $input = $request->all();
        $rows  = \array_key_exists(0, $input) ? $input : [$input];

        foreach ($rows as $row) {
            $data = new MonitorLuar();
            $data->timestamp  = $row['timestamp'];
            $data->cps        = $row['cps'];
            $data->usvh       = $row['usvh'];
            $data->suhu       = $row['suhu'];
            $data->kelembapan = $row['kelembapan'];
            $data->save();
        }

        return response()->json(['message' => 'OK', 'count' => \count($rows)]);
    }

    public function fetchDataIndoorMonitor() {
        $data = MonitorDalam::latestFirst()->first();
        return response()->json($data);
    }

    public function getLatestData(?Request $request = null) {
        $after = $request?->query('after');

        if ($after) {
            $rows = MonitorLuar::where('timestamp', '>', $after)
                        ->where('timestamp', '>=', now()->subMinute())
                        ->orderBy('timestamp', 'asc')
                        ->get();
            return response()->json($rows);
        }

        return response()->json(
            MonitorLuar::latestFirst()->where('timestamp', '>=', now()->subMinute())->first()
        );
    }

    public function getTablesData() {
        return MonitorLuar::latestFirst()->get();
    }

    public function getDataHistory(Request $request) {
        $from = $request->query('from');
        $to   = $request->query('to');

        $query = MonitorLuar::orderBy('timestamp', 'asc');

        if ($from && $to) {
            $query->where('timestamp', '>=', $from)
                  ->where('timestamp', '<=', $to);
        } else {
            $query->where('timestamp', '>=', Carbon::now()->subHour());
        }

        return response()->json($query->get());
    }

    public function getDataChart() {
        $data = MonitorLuar::latestFirst()
                ->take(30)
                ->get()
                ->reverse()
                ->values();

        return response()->json($data);
    }
}
