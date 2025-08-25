<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Metric;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MetricSyncController extends Controller
{
    private $csvUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vRpDC257bNuYrD8WQCcM3Swg-P2hAbx1m1OsAMTyh3kFh5MbgTNOl2z6hqf_qpUCEwI9aQ4bHwX3w0l/pub?gid=2008014102&single=true&output=csv';

    public function sync()
    {
        Log::info("🚀 Iniciando sincronización de métricas...");
        $result = ['processed' => 0, 'imported' => 0, 'errors' => []];

        try {
            $response = Http::get($this->csvUrl);
            if (!$response->ok()) {
                return response()->json(['error' => '❌ No se pudo obtener el CSV.'], 500);
            }

            $lines = array_filter(array_map('trim', explode("\n", $response->body())));
            $headers = str_getcsv(strtolower(array_shift($lines)));
            $lastDate = Metric::max('fecha');

            foreach ($lines as $i => $line) {
                $row = str_getcsv($line);
                if (count($row) < count($headers)) continue;

                $data = @array_combine($headers, $row);
                if (!$data || empty($data['courier id'])) continue;

                try {
                    $date = Carbon::createFromFormat('d/m/Y', trim($data['start date']));
                } catch (\Exception $e) { continue; }

                if ($lastDate && $date->lte(Carbon::parse($lastDate))) continue;

                $result['processed']++;
                $toFloat = fn($v) => floatval(str_replace(['_','%', ','], ['', '', '.'], $v));

                Metric::create([
                    'courier_id' => trim($data['courier id']),
                    'transport' => $data['transport'] ?? null,
                    'fecha' => $date->format('Y-m-d'),
                    'ciudad' => $data['city code'] ?? null,
                    'pedidos_entregados' => $toFloat($data['delivered orders'] ?? 0),
                    'cancelados' => $toFloat($data['% canceled orders'] ?? 0),
                    'reasignaciones' => $toFloat($data['% reassignments'] ?? 0),
                    'no_show' => $toFloat($data['%no show'] ?? 0),
                    'horas' => $toFloat($data['h/ras'] ?? 0),
                    'ratio_entrega' => $toFloat($data['ratio de entrga'] ?? 0),
                    'tiempo_promedio' => $toFloat($data['cdt (min)=< 20min'] ?? 0),
                ]);
                $result['imported']++;
            }
            return response()->json(['success' => '✅ Sincronización completada.', 'nuevos' => $result['imported']]);

        } catch (\Exception $e) {
            Log::error("Error en sincronización: " . $e->getMessage());
            return response()->json(['error' => 'Error en sincronización: ' . $e->getMessage()], 500);
        }
    }
}
