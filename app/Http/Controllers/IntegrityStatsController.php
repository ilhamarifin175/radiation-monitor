<?php

namespace App\Http\Controllers;

use App\Models\IntegrityStats;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IntegrityStatsController extends Controller
{
    public function tables()
    {
        $data = IntegrityStats::latestFirst()->take(1000)->get();

        return view('integrity-data-tables', [
            'title' => 'Log Kualitas Jaringan',
            'data'  => $data,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $query = IntegrityStats::orderBy('created_at', 'asc');

        if ($from && $to) {
            $query->where('created_at', '>=', Carbon::parse($from))
                  ->where('created_at', '<=', Carbon::parse($to));
        }

        $data     = $query->get();
        $filename = 'log_jaringan_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF");
            fputcsv($f, [
                'Waktu Dibuat', 'Timestamp ESP',
                'WiFi Terima', 'WiFi Hilang', 'WiFi PDR (%)',
                'LoRa Terima', 'LoRa Hilang', 'LoRa PDR (%)',
                'ESP Reset Luar', 'ESP Reset Dalam',
                'Backfill Luar', 'Backfill Dalam', 'Backfill Stats',
            ]);
            foreach ($data as $row) {
                fputcsv($f, [
                    $row->created_at,          $row->timestamp,
                    $row->wifi_terima,         $row->wifi_hilang,         $row->wifi_pdr,
                    $row->lora_terima,         $row->lora_hilang,         $row->lora_pdr,
                    $row->esp_reset_count_luar,$row->esp_reset_count_dalam,
                    $row->backfill_luar,       $row->backfill_dalam,      $row->backfill_stats,
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function storeData(Request $request)
    {
        $data = new IntegrityStats();
        $data->timestamp        = $request->input('timestamp');
        $data->wifi_terima      = $request->input('wifi_terima');
        $data->wifi_hilang      = $request->input('wifi_hilang');
        $data->wifi_pdr         = $request->input('wifi_pdr');
        $data->lora_terima      = $request->input('lora_terima');
        $data->lora_hilang      = $request->input('lora_hilang');
        $data->lora_pdr         = $request->input('lora_pdr');
        $data->esp_reset_count_luar  = $request->input('esp_reset_count_luar', 0);
        $data->esp_reset_count_dalam = $request->input('esp_reset_count_dalam', 0);
        $data->backfill_luar    = $request->input('backfill_luar', 0);
        $data->backfill_dalam   = $request->input('backfill_dalam', 0);
        $data->backfill_stats   = $request->input('backfill_stats', 0);
        $data->save();

        return response()->json(['message' => 'Data stored successfully.']);
    }
}
