<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'detalle_ventas';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'subtotal',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}