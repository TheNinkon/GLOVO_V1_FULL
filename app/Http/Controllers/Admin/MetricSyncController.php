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
        $result = ['processed' => 0, 'updated' => 0, 'created' => 0, 'errors' => []];

        try {
            $response = Http::get($this->csvUrl);
            if (!$response->ok()) {
                Log::error("❌ No se pudo obtener el CSV. Código de estado: " . $response->status());
                return response()->json(['error' => '❌ No se pudo obtener el CSV.'], 500);
            }

            $lines = array_filter(array_map('trim', explode("\n", $response->body())));
            $headers = str_getcsv(strtolower(array_shift($lines)));

            foreach ($lines as $i => $line) {
                $row = str_getcsv($line);
                if (count($row) < count($headers)) continue;

                $data = @array_combine($headers, $row);
                if (!$data || empty($data['courier id'])) continue;

                // CORRECCIÓN: Manejo de formato de fecha
                try {
                    $date = Carbon::createFromFormat('d/m/Y', trim($data['start date']));
                } catch (\Exception $e) {
                    $result['errors'][] = "Error de formato de fecha en la línea " . ($i + 2) . ": " . $e->getMessage();
                    continue;
                }

                $toFloat = fn($v) => floatval(str_replace(['_','%', ','], ['', '', '.'], $v));

                // Mapeo dinámico de campos para la base de datos
                $metricData = [
                    'courier_id' => trim($data['courier id']),
                    'transport' => $data['transport'] ?? null,
                    'fecha' => $date->format('Y-m-d'),
                    'ciudad' => $data['city code'] ?? null,
                    'pedidos_entregados' => $toFloat($data['delivered orders'] ?? 0),
                    'cancelados' => $toFloat($data['% canceled orders'] ?? 0),
                    'reasignaciones' => $toFloat($data['% reassignments'] ?? 0),
                    'no_show' => $toFloat($data['%no show'] ?? 0),
                    // CORRECCIÓN: Nombre de campo en el CSV
                    'horas' => $toFloat($data['h/ras'] ?? 0),
                    'ratio_entrega' => $toFloat($data['ratio de entrga'] ?? 0),
                    'tiempo_promedio' => $toFloat($data['cdt (min)=< 20min'] ?? 0),
                    // Si agregas nuevos campos en el CSV, inclúyelos aquí
                    // 'nuevo_campo' => $data['nombre del campo en el csv'] ?? null,
                ];

                $result['processed']++;

                // LÓGICA CLAVE: Usar updateOrCreate para idempotencia
                $metric = Metric::updateOrCreate(
                    ['courier_id' => $metricData['courier_id'], 'fecha' => $metricData['fecha']],
                    $metricData
                );

                if ($metric->wasRecentlyCreated) {
                    $result['created']++;
                } else {
                    $result['updated']++;
                }
            }

            Log::info("✅ Sincronización completada. Nuevos: {$result['created']}, Actualizados: {$result['updated']}, Errores: " . count($result['errors']));
            return response()->json([
                'success' => '✅ Sincronización completada.',
                'nuevos' => $result['created'],
                'actualizados' => $result['updated'],
                'errores' => $result['errors']
            ]);

        } catch (\Exception $e) {
            Log::error("Error crítico en sincronización: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
            return response()->json(['error' => '❌ Error crítico en sincronización. ' . $e->getMessage()], 500);
        }
    }
}
