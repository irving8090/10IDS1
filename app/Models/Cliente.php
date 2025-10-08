<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable; 

use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory,Notifiable;

    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente'; 

    public $timestamps = false; 

    protected $fillable = [
        'nombre', 
        'telefono', 
        'correo', 
        'estado_cuenta', 
        'fecha_registro', 
    ];

    /**
     * Define la relación: Un Cliente puede tener muchas Ventas.
     */
   /*
    public function ventas(): HasMany
    {
        // Se especifica la clave foránea y la clave local
        return $this->hasMany(Venta::class, 'id_cliente', 'id_cliente'); //
    }

    /**
     * Obtiene el nombre de la clave para la vinculación de modelos de ruta.
     * Esto es para que el Resource Controller funcione con 'id_cliente'.
     */
    public function getRouteKeyName(): string
    {
        return 'id_cliente'; 
    }
}