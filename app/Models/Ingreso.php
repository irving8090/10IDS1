<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingreso extends Model
{
    use HasFactory;

    protected $table = 'ingresos';
    protected $primaryKey = 'id_ingreso';
    public $timestamps = false;

   
    protected $fillable = [
        'id_venta',
        'fecha_ingreso',
        'monto',
        'descripcion',
    ];

    /**
     * Define la relación inversa: un ingreso pertenece a una venta.
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }
}