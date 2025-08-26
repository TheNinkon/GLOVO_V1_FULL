<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Metric extends Model
{
    use HasFactory;

    protected $table = 'glovo_metrics'; // Asegurar que apunta a la tabla correcta

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
    ];

    /**
     * Define la relación: Una métrica de Glovo pertenece a una cuenta.
     */
    public function account(): HasOne
    {
        return $this->hasOne(Account::class, 'courier_id', 'courier_id');
    }
}
