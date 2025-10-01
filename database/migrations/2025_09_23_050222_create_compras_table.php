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
        Schema::create('compras', function (Blueprint $table) {
            $table->id('id_compra');
            $table->string('proveedor', 100)->nullable();
            $table->date('fecha_compra');
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['pendiente', 'comprado', 'cancelado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};