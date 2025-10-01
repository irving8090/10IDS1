<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Producto; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with('cliente:id_cliente,nombre')
            ->orderBy('fecha_venta', 'desc')
            ->paginate(15);
        return response()->json($ventas);
    }
    
    /**
     * Almacena una nueva venta en estado 'pendiente' SIN afectar el stock.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'fecha_venta' => 'required|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.subtotal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

       
        
        $venta = DB::transaction(function () use ($request) {
            $totalVenta = collect($request->detalles)->sum('subtotal');
            $venta = Venta::create([
                'id_cliente' => $request->id_cliente,
                'fecha_venta' => $request->fecha_venta,
                'total' => $totalVenta,
                'estado' => 'pendiente',
            ]);

            $venta->detalles()->createMany($request->detalles);

            return $venta;
        });

        return response()->json(['message' => 'Venta registrada como pendiente con éxito', 'venta' => $venta], 201);
    }

    /**
     * Confirma el pago, valida y descuenta el stock, y actualiza el estado de la venta.
     */
    public function confirmarPago(Venta $venta)
    {
        // No se puede pagar una venta que no esté pendiente
        if ($venta->estado !== 'pendiente') {
            return response()->json(['message' => 'Esta venta no puede ser marcada como pagada.'], 409);
        }

        try {
            DB::transaction(function () use ($venta) {
                // 1. Recorrer cada detalle de la venta para validar el stock
                foreach ($venta->detalles as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    if ($producto->cantidad_stock < $detalle->cantidad) {
                        // Si un producto no tiene stock, se lanza una excepción para revertir la transacción
                        throw new \Exception('Stock insuficiente para el producto: ' . $producto->nombre);
                    }
                }

                // 2. Si todos los productos tienen stock, se descuenta
                foreach ($venta->detalles as $detalle) {
                    Producto::find($detalle->id_producto)->decrement('cantidad_stock', $detalle->cantidad);
                }

                // 3. Finalmente, se actualiza el estado de la venta a 'pagado'
                $venta->update(['estado' => 'pagado']);
            });

            return response()->json(['message' => 'Pago confirmado y stock actualizado.', 'venta' => $venta]);

        } catch (Throwable $e) {
            // Si se lanzó la excepción por falta de stock, se envía el mensaje de error
            return response()->json([
               'message' => 'No se pudo confirmar el pago.',
               'error' => $e->getMessage(),
           ], 409);
       }
    }

    /**
     * Cancela una venta. No afecta el stock ya que nunca se descontó.
     */
    public function cancelarVenta(Venta $venta)
    {
        $venta->update(['estado' => 'cancelado']);
        return response()->json(['message' => 'Venta cancelada', 'venta' => $venta]);
    }
    
    /**
     * Elimina una venta. Si estaba pagada, devuelve los productos al stock.
     */
    public function destroy(Venta $venta)
    {
        DB::transaction(function () use ($venta) {
            // Solo devuelve el stock si la venta había sido pagada
            if ($venta->estado === 'pagado') {
                foreach ($venta->detalles as $detalle) {
                    Producto::find($detalle->id_producto)->increment('cantidad_stock', $detalle->cantidad);
                }
            }
            
            $venta->detalles()->delete();
            $venta->delete();
        });

        return response()->json(['message' => 'Venta eliminada con éxito']);
    }

    public function show(Venta $venta)
    {
        $venta->load('cliente', 'detalles.producto');
        return response()->json($venta);
    }
    
    public function update(Request $request, Venta $venta)
    {
        
        if ($venta->estado === 'pagado') {
            return response()->json(['message' => 'No se puede editar una venta que ya ha sido pagada.'], 409);
        }
        
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'fecha_venta' => 'required|date',
            'detalles' => 'required|array|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        return DB::transaction(function () use ($request, $venta) {
            $totalVenta = collect($request->detalles)->sum('subtotal');
            $venta->update([
                'id_cliente' => $request->id_cliente,
                'fecha_venta' => $request->fecha_venta,
                'total' => $totalVenta,
            ]);
            $venta->detalles()->delete();
            $venta->detalles()->createMany($request->detalles);

            return response()->json(['message' => 'Venta actualizada con éxito', 'venta' => $venta->fresh()->load('cliente')]);
        });
    }
}