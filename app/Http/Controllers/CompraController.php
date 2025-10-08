<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Egreso; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompraController extends Controller
{
    public function index() {
        return Compra::with('detalles.producto')->orderBy('fecha_compra', 'desc')->get();
    }

    public function store(Request $request) {
        $data = $request->validate([
            'proveedor' => 'nullable|string|max:100',
            'fecha_compra' => 'required|date',
            'estado' => 'required|in:pendiente,comprado,cancelado',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $totalCalculado = 0;
            // 1. Calculamos el total ANTES de guardar nada
            foreach ($data['detalles'] as $detalleData) {
                $producto = Producto::find($detalleData['id_producto']);
                $totalCalculado += $detalleData['cantidad'] * $producto->precio_costo;
            }

            // 2. Creamos la compra con el total correcto
            $compra = Compra::create([
                'proveedor' => $data['proveedor'],
                'fecha_compra' => $data['fecha_compra'],
                'total' => $totalCalculado, // Usamos el total calculado en el servidor
                'estado' => $data['estado'],
            ]);

            // 3. Guardamos los detalles y actualizamos el stock
            foreach ($data['detalles'] as $detalleData) {
                $producto = Producto::find($detalleData['id_producto']);
                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $detalleData['id_producto'],
                    'cantidad' => $detalleData['cantidad'],
                    'precio_unitario' => $producto->precio_costo, // Guardamos el precio de costo
                ]);

                if ($compra->estado === 'comprado') {
                    $producto->cantidad_stock += $detalleData['cantidad'];
                    $producto->save();
                }
            }

            DB::commit();
            return response()->json(Compra::with('detalles.producto')->find($compra->id_compra), 201);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear la compra.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id_compra) {
        return Compra::with('detalles.producto')->findOrFail($id_compra);
    }

    public function destroy($id_compra) {
        Compra::findOrFail($id_compra)->delete();
        return response()->json(['message' => 'Compra eliminada exitosamente.']);
    }


    public function confirmarCompra(Compra $compra)
    {
        //Verifica si el estado es 'pendiente'
        if ($compra->estado !== 'pendiente') {
            return response()->json(['message' => 'Solo se pueden confirmar compras pendientes.'], 409);
        }

        try {
            return DB::transaction(function () use ($compra) {
                // 1. Aumentar el stock de cada producto comprado
                foreach ($compra->detalles as $detalle) {
                    // ->producto carga la relación si no ha sido cargada
                    $detalle->producto->increment('cantidad_stock', $detalle->cantidad);
                }

                // 2. Crear el registro de EGRESO
                Egreso::create([
                    'id_compra' => $compra->id_compra,
                    'fecha_egreso' => now(),
                    'monto' => $compra->total,
                    'descripcion' => "Egreso por Compra #{$compra->id_compra} a proveedor.",
                ]);

                // 3. Actualiza el estado de la compra a 'comprado'
                $compra->update(['estado' => 'comprado']);

                return response()->json([
                    'message' => 'Compra confirmada, stock actualizado y egreso generado.', 
                    'compra' => $compra->fresh() // Devuelve la compra con el estado actualizado
                ]);
            });
        } catch (Throwable $e) {
            return response()->json([
               'message' => 'No se pudo confirmar la compra.',
               'error' => $e->getMessage(),
            ], 500);
        }
    }
}