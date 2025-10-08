<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Producto; 
use App\Models\Ingreso;
use App\Notifications\VentaConfirmadaWhatsApp;
use Illuminate\Support\Facades\Log;
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

  

public function confirmarPago(Venta $venta)
    {
        // 1. Validar que la venta esté pendiente
        if ($venta->estado !== 'pendiente') {
            return response()->json(['message' => 'Esta venta no puede ser marcada como pagada.'], 409);
        }

        try {
            // 2. Usar una transacción para asegurar la integridad de los datos
            DB::transaction(function () use ($venta) {
                // 2a. Recorrer y descontar el stock de cada producto
                foreach ($venta->detalles as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    if ($producto->cantidad_stock < $detalle->cantidad) {
                        throw new \Exception('Stock insuficiente para el producto: ' . $producto->nombre);
                    }
                    $producto->decrement('cantidad_stock', $detalle->cantidad);
                }

                // 2b. Crear el registro de INGRESO
                Ingreso::create([
                    'id_venta' => $venta->id_venta,
                    'fecha_ingreso' => now(),
                    'monto' => $venta->total,
                    'descripcion' => "Ingreso por Venta #{$venta->id_venta} a cliente: {$venta->load('cliente')->cliente->nombre}",
                ]);

                // 2c. Actualizar el estado de la venta a 'pagado'
                $venta->update(['estado' => 'pagado']);
            });

            // 3. Si la transacción fue exitosa, enviar la notificación de WhatsApp
            try {
                $venta->load('cliente');
                (new VentaConfirmadaWhatsApp($venta))->sendToWhatsApp($venta->cliente);
            } catch(\Exception $e) {
                // Si falla el envío, solo lo registramos pero no afectamos la respuesta al usuario
                Log::error("Falló el envío de WhatsApp para la venta {$venta->id_venta}: " . $e->getMessage());
            }

            return response()->json(['message' => 'Pago confirmado, stock actualizado, ingreso generado y notificación enviada.', 'venta' => $venta->fresh()]);

        } catch (Throwable $e) {
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