<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\EgresoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('login',[LoginController::class,'login']);


// Obtener todos los clientes
Route::get('/clientes', [ClienteController::class, 'index']);

// Crear un nuevo cliente
Route::post('/clientes', [ClienteController::class, 'store']);

// Mostrar un cliente por su ID
Route::get('/clientes/{id_cliente}', [ClienteController::class, 'show']);

// Actualizar un cliente por su ID
Route::put('/clientes/{id_cliente}', [ClienteController::class, 'update']);

// Eliminar un cliente por su ID
Route::delete('/clientes/{id_cliente}', [ClienteController::class, 'destroy']);


// --- Rutas para Productos  ---

// 2. Obtener todos los productos
Route::get('/productos', [ProductoController::class, 'index']);

// 3. Crear un nuevo producto
Route::post('/productos', [ProductoController::class, 'store']);

// 4. Mostrar un producto por su ID
Route::get('/productos/{id_producto}', [ProductoController::class, 'show']);

// 5. Actualizar un producto por su ID
Route::put('/productos/{id_producto}', [ProductoController::class, 'update']);

// 6. Eliminar un producto por su ID
Route::delete('/productos/{id_producto}', [ProductoController::class, 'destroy']);

Route::post('productos/{id_producto}/reducir-stock', [ProductoController::class, 'reducirStock']);


// --- Rutas para Compras 
Route::get('/compras', [CompraController::class, 'index']);
Route::post('/compras', [CompraController::class, 'store']);
Route::get('/compras/{id_compra}', [CompraController::class, 'show']);
Route::delete('/compras/{id_compra}', [CompraController::class, 'destroy']);
Route::patch('/compras/{compra}/confirmar', [CompraController::class, 'confirmarCompra']);

//Egresos
Route::get('/egresos', [EgresoController::class, 'index']);

//Ingresos
Route::get('/ingresos', [IngresoController::class, 'index']);

// Rutas para ventas
Route::controller(VentaController::class)->prefix('ventas')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{venta}', 'show');
    Route::delete('/{venta}', 'destroy');
    Route::patch('/{venta}/confirmar-pago', 'confirmarPago');
    Route::patch('/{venta}/cancelar', 'cancelarVenta');
    Route::put('/{venta}', 'update'); 

});
