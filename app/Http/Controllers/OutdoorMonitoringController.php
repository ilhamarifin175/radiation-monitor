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
            $data->measured_at = $row['measured_at'] ?? null;
            $data->cps         = $row['cps'];
            $data->usvh        = $row['usvh'];
            $data->suhu        = $row['suhu'];
            $data->kelembapan  = $row['kelembapan'];
            $data->latency_ms  = $row['latency_ms'] ?? 0;
            $data->is_backfill = $row['is_backfill'] ?? 0;
            $data->save();
        }

        return response()->json(['message' => 'OK', 'count' => \count($rows)]);
    }

    public function fetchDataIndoorMonitor() {
        $data = MonitorDalam::latestFirst()->first();
        return response()->json($data);
    }

    public function exportCsv(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $query = MonitorLuar::orderBy('created_at', 'asc');

        if ($from && $to) {
            $query->where('created_at', '>=', Carbon::parse($from))
                  ->where('created_at', '<=', Carbon::parse($to));
        }

        $data     = $query->get();
        $filename = 'monitor_luar_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF");
            fputcsv($f, [
                'Waktu Dibuat', 'Waktu Diukur', 'CPS',
                'Laju Dosis (uSv/jam)', 'Suhu (C)', 'Kelembapan (%)',
                'Latency (ms)', 'Is Backfill',
            ]);
            foreach ($data as $row) {
                fputcsv($f, [
                    $row->created_at, $row->measured_at, $row->cps,
                    $row->usvh,       $row->suhu,        $row->kelembapan,
                    $row->latency_ms, $row->is_backfill,
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getLatestData(?Request $request = null) {
        $after = $request?->query('after');

        if ($after) {
            $rows = MonitorLuar::where('is_backfill', 0)
                        ->where('created_at', '>', Carbon::parse($after))
                        ->orderBy('created_at', 'asc')
                        ->get();
            return response()->json($rows);
        }

        return response()->json(
            MonitorLuar::where('is_backfill', 0)->latestFirst()->first()
        );
    }

    public function getTablesData() {
        return MonitorLuar::latestFirst()->take(1000)->get();
    }

    public function getDataHistory(Request $request) {
        $from = $request->query('from');
        $to   = $request->query('to');

        $query = MonitorLuar::where('is_backfill', 0)->orderBy('created_at', 'asc');

        if ($from && $to) {
            $query->where('created_at', '>=', Carbon::parse($from))
                  ->where('created_at', '<=', Carbon::parse($to));
        } else {
            $query->where('created_at', '>=', Carbon::now()->subHour());
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
