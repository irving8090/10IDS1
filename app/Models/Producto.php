<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

   
    protected $primaryKey = 'id_producto';

    public $timestamps = false;

   
    protected $fillable = [
        'nombre',
        'descripcion',
        'cantidad_stock',
        'precio_costo',
        'precio_venta',
        'fecha_registro',
        'estado',
    ];
}
