<?php

namespace App\Http\Controllers;

use App\Models\IntegrityStats;
use Illuminate\Http\Request;

class IntegrityStatsController extends Controller
{
    public function tables()
    {
        $data = IntegrityStats::latestFirst()->get();

        return view('integrity-data-tables', [
            'title' => 'Log Kualitas Jaringan',
            'data'  => $data,
        ]);
    }

    public function storeData(Request $request)
    {
        $data = new IntegrityStats();
        $data->timestamp    = $request->input('timestamp');
        $data->wifi_terima  = $request->input('wifi_terima');
        $data->wifi_hilang  = $request->input('wifi_hilang');
        $data->wifi_pdr     = $request->input('wifi_pdr');
        $data->lora_terima  = $request->input('lora_terima');
        $data->lora_hilang  = $request->input('lora_hilang');
        $data->lora_pdr     = $request->input('lora_pdr');
        $data->save();

        return response()->json(['message' => 'Data stored successfully.']);
    }
}
