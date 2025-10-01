<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model {
    use HasFactory;
    protected $primaryKey = 'id_detalle_compra';
    public $timestamps = false;
    protected $table = 'detalle_compras';
    protected $fillable = [ 'id_compra', 'id_producto', 'cantidad', 'precio_unitario' ];

    public function producto() {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}