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
        Schema::create('egresos', function (Blueprint $table) {
            $table->id('id_egreso');
            $table->unsignedBigInteger('id_compra')->nullable();
            $table->date('fecha_egreso');
            $table->decimal('monto', 10, 2);
            $table->string('descripcion', 150)->nullable();

            // Clave Foránea
            $table->foreign('id_compra')->references('id_compra')->on('compras');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};