<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model {
    use HasFactory;
    protected $primaryKey = 'id_compra';
    public $timestamps = false;
    protected $table = 'compras';
    protected $fillable = [ 'proveedor', 'fecha_compra', 'total', 'estado' ];

    public function detalles() {
        return $this->hasMany(DetalleCompra::class, 'id_compra', 'id_compra');
    }
}