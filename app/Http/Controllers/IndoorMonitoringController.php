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
            $data->measured_at = $row['measured_at'] ?? null;
            $data->seq         = $row['seq'];
            $data->cps         = $row['cps'];
            $data->usvh        = $row['usvh'];
            $data->suhu        = $row['suhu'];
            $data->kelembapan  = $row['kelembapan'];
            $data->relay       = $row['relay'];
            $data->jaringan    = $row['jaringan'];
            $data->rssi        = $row['rssi'];
            $data->latency_ms  = $row['latency_ms'] ?? 0;
            $data->is_backfill = $row['is_backfill'] ?? 0;
            $data->save();
        }

        return response()->json(['message' => 'OK', 'count' => \count($rows)]);
    }

    public function exportCsv(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $query = MonitorDalam::orderBy('created_at', 'asc');

        if ($from && $to) {
            $query->where('created_at', '>=', Carbon::parse($from))
                  ->where('created_at', '<=', Carbon::parse($to));
        }

        $data    = $query->get();
        $filename = 'monitor_dalam_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF"); // BOM agar Excel baca UTF-8 dengan benar
            fputcsv($f, [
                'Waktu Dibuat', 'Waktu Diukur', 'Seq', 'CPS',
                'Laju Dosis (uSv/jam)', 'Suhu (C)', 'Kelembapan (%)',
                'Relay', 'Jaringan', 'RSSI (dBm)', 'Latency (ms)', 'Is Backfill',
            ]);
            foreach ($data as $row) {
                fputcsv($f, [
                    $row->created_at, $row->measured_at, $row->seq,    $row->cps,
                    $row->usvh,       $row->suhu,        $row->kelembapan,
                    $row->relay,      $row->jaringan,    $row->rssi,
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
            $rows = MonitorDalam::where('is_backfill', 0)
                        ->where('created_at', '>', Carbon::parse($after))
                        ->orderBy('created_at', 'asc')
                        ->get();
            return response()->json($rows);
        }

        return response()->json(
            MonitorDalam::where('is_backfill', 0)->latestFirst()->first()
        );
    }

    public function getTablesData() {
        return MonitorDalam::latestFirst()->take(1000)->get();
    }

    public function getDataHistory(Request $request) {
        $from = $request->query('from');
        $to   = $request->query('to');

        $query = MonitorDalam::where('is_backfill', 0)->orderBy('created_at', 'asc');

        if ($from && $to) {
            $query->where('created_at', '>=', Carbon::parse($from))
                  ->where('created_at', '<=', Carbon::parse($to));
        } else {
            $query->where('created_at', '>=', Carbon::now()->subHour());
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
