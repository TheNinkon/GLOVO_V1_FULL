<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    use HasFactory;

    // Nombre de la tabla basado en tu migración
    protected $table = 'glovo_metrics';

    protected $fillable = [
        'courier_id',
        'transport',
        'fecha',
        'ciudad',
        'pedidos_entregados',
        'cancelados',
        'reasignaciones',
        'no_show',
        'horas',
        'ratio_entrega',
        'tiempo_promedio',
        'ineligible'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];
}
