<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->string('nombre', 100);
            $table->string('descripcion', 200)->nullable();
            $table->integer('cantidad_stock');
            $table->decimal('precio_costo', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->date('fecha_registro');
            $table->enum('estado', ['activo', 'inactivo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};