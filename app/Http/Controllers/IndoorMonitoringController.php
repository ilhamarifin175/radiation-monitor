<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MonitorDalam;

class IndoorMonitoringController extends Controller
{
    public function index() {
        $currentDate = Carbon::now()->isoFormat('D MMMM YYYY');
        $currentDay  = Carbon::now()->isoFormat('dddd');
        $latestData  = $this->getLatestData();

        return view('indoor-monitoring', [
            "title"       => "Monitor Dalam",
            "currentDate" => $currentDate,
            "currentDay"  => $currentDay,
            "latestData"  => $latestData
        ]);
    }

    public function tables() {
        $data = $this->getTablesData();

        return view('indoor-data-tables', [
            "title" => "Monitor Dalam Data Tables",
            "data"  => $data
        ]);
    }

    public function storeData(Request $request) {
        $input = $request->all();
        $rows  = \array_key_exists(0, $input) ? $input : [$input];

        foreach ($rows as $row) {
            $data = new MonitorDalam();
            $data->timestamp  = $row['timestamp'];
            $data->seq        = $row['seq'];
            $data->cps        = $row['cps'];
            $data->usvh       = $row['usvh'];
            $data->suhu       = $row['suhu'];
            $data->kelembapan = $row['kelembapan'];
            $data->relay      = $row['relay'];
            $data->jaringan   = $row['jaringan'];
            $data->rssi       = $row['rssi'];
            $data->save();
        }

        return response()->json(['message' => 'OK', 'count' => \count($rows)]);
    }

    public function getLatestData(?Request $request = null) {
        $after = $request?->query('after');

        if ($after) {
            $rows = MonitorDalam::where('timestamp', '>', $after)
                        ->where('timestamp', '>=', now()->subMinute())
                        ->orderBy('timestamp', 'asc')
                        ->get();
            return response()->json($rows);
        }

        return response()->json(
            MonitorDalam::latestFirst()->where('timestamp', '>=', now()->subMinute())->first()
        );
    }

    public function getTablesData() {
        return MonitorDalam::latestFirst()->get();
    }

    public function getDataHistory(Request $request) {
        $from = $request->query('from');
        $to   = $request->query('to');

        $query = MonitorDalam::orderBy('timestamp', 'asc');

        if ($from && $to) {
            $query->where('timestamp', '>=', $from)
                  ->where('timestamp', '<=', $to);
        } else {
            $query->where('timestamp', '>=', Carbon::now()->subHour());
        }

        return response()->json($query->get());
    }

    public function getDataChart() {
        $data = MonitorDalam::latestFirst()
                ->take(30)
                ->get()
                ->reverse()
                ->values();

        return response()->json($data);
    }
}
