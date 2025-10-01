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
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id('id_ingreso');
            $table->unsignedBigInteger('id_venta')->nullable();
            $table->date('fecha_ingreso');
            $table->decimal('monto', 10, 2);
            $table->string('descripcion', 150)->nullable();

            // Clave Foránea
            $table->foreign('id_venta')->references('id_venta')->on('ventas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};