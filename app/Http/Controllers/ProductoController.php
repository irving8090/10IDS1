<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Throwable;

class ProductoController extends Controller
{
    /**
     * Muestra una lista de todos los productos.
     */
    public function index()
    {
        return Producto::all();
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:200',
                'cantidad_stock' => 'required|integer|min:0',
                'precio_costo' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0',
                'fecha_registro' => 'required|date',
                'estado' => 'required|in:activo,inactivo',
            ]);

            $producto = Producto::create($data);

            return response()->json($producto, 201);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error interno del servidor al crear el producto.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Muestra un producto específico.
     */
    public function show($id_producto)
    {
        return Producto::findOrFail($id_producto);
    }

    /**
     * Actualiza un producto específico en la base de datos.
     */
    public function update(Request $request, $id_producto)
    {
        try {
            $producto = Producto::findOrFail($id_producto);

            $data = $request->validate([
                'nombre' => 'sometimes|required|string|max:100',
                'descripcion' => 'sometimes|nullable|string|max:200',
                'cantidad_stock' => 'sometimes|required|integer|min:0',
                'precio_costo' => 'sometimes|required|numeric|min:0',
                'precio_venta' => 'sometimes|required|numeric|min:0',
                'fecha_registro' => 'sometimes|required|date',
                'estado' => 'sometimes|required|in:activo,inactivo',
            ]);

            $producto->update($data);

            return response()->json($producto);

        } catch (Throwable $e) {
             return response()->json([
                'message' => 'Error interno del servidor al actualizar el producto.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina un producto específico.
     */
    public function destroy($id_producto)
    {
        $producto = Producto::findOrFail($id_producto);
        $producto->delete();

        return response()->json(['message' => 'Producto eliminado exitosamente.']);
    }

    // --- MÉTODO NUEVO AÑADIDO ---
    /**
     * Reduce el stock de un producto específico, validando la disponibilidad.
     */
    public function reducirStock(Request $request, $id_producto)
    {
        try {
            // 1. Validar que la cantidad sea un número requerido y positivo
            $data = $request->validate([
                'cantidad' => 'required|integer|min:1',
            ]);

            $producto = Producto::findOrFail($id_producto);
            $cantidadAReducir = $data['cantidad'];
            $stockActual = $producto->cantidad_stock;

            // 2. Comprobar si hay suficiente stock
            if ($stockActual < $cantidadAReducir) {
                // 3. Si no hay suficiente, devolver un error con el stock disponible
                return response()->json([
                    'message' => 'Stock insuficiente.',
                    'stock_disponible' => $stockActual,
                ], 409); // 409 Conflict es un buen código de estado para esto
            }

            // 4. Si hay suficiente, reducir el stock y guardar
            $producto->cantidad_stock -= $cantidadAReducir;
            $producto->save();

            return response()->json([
                'message' => 'Stock actualizado exitosamente.',
                'producto' => $producto,
            ]);

        } catch (Throwable $e) {
            return response()->json([
               'message' => 'Error interno del servidor al reducir el stock.',
               'error' => $e->getMessage(),
           ], 500);
       }
    }
}