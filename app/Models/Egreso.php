<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Egreso extends Model
{
    use HasFactory;

    protected $table = 'egresos';
    protected $primaryKey = 'id_egreso';
    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'fecha_egreso',
        'monto',
        'descripcion',
    ];

    protected $casts = [
        'fecha_egreso' => 'date',
        'monto' => 'decimal:2',
    ];

    /**
     * Un egreso pertenece a una compra.
     */
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }
}