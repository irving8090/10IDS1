<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ClienteController extends Controller
{
    /**
     * Muestra una lista de todos los clientes.
     */
    public function index()
    {
        // Usamos get() en lugar de all() para poder ordenar los resultados.
        return Cliente::orderBy('nombre', 'asc')->get();
    }

    /**
     * Guarda un nuevo cliente, formatea el número de teléfono y dispara el observador para WhatsApp.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'telefono' => 'nullable|string|max:20',
                'correo' => 'nullable|email|max:100|unique:clientes,correo',
                'estado_cuenta' => 'required|in:sin deuda,con deuda',
                'fecha_registro' => 'required|date',
            ]);

            // Formateamos el número de teléfono antes de guardarlo
            if (!empty($data['telefono'])) {
                $data['telefono'] = $this->formatPhoneNumberForWhatsApp($data['telefono']);
            }

            $cliente = Cliente::create($data);
            
            // NOTA: El ClienteObserver se encargará automáticamente de enviar el WhatsApp de bienvenida aquí.

            return response()->json($cliente, 201);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error interno del servidor al crear el cliente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Muestra un cliente específico.
     */
    public function show($id_cliente)
    {
        return Cliente::findOrFail($id_cliente);
    }

    /**
     * Actualiza un cliente existente y formatea el número de teléfono si cambia.
     */
    public function update(Request $request, $id_cliente)
    {
        $cliente = Cliente::findOrFail($id_cliente);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'telefono' => 'sometimes|nullable|string|max:20',
            'correo' => [
                'sometimes', 'nullable', 'email', 'max:100',
                Rule::unique('clientes', 'correo')->ignore($id_cliente, 'id_cliente')
            ],
            'estado_cuenta' => 'sometimes|required|in:sin deuda,con deuda',
            'fecha_registro' => 'sometimes|required|date',
        ]);

        // Formateamos el número de teléfono si se está actualizando
        if (isset($data['telefono']) && !empty($data['telefono'])) {
            $data['telefono'] = $this->formatPhoneNumberForWhatsApp($data['telefono']);
        }

        $cliente->update($data);

        return response()->json($cliente);
    }

    /**
     * Elimina un cliente.
     */
    public function destroy($id_cliente)
    {
        $cliente = Cliente::findOrFail($id_cliente);
        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado exitosamente.']);
    }
    
    /**
     * Función privada para formatear un número de teléfono para la API de WhatsApp en México.
     */
    private function formatPhoneNumberForWhatsApp($telefono)
    {
        // 1. Quitar todos los caracteres que no sean dígitos
        $limpio = preg_replace('/[^0-9]/', '', $telefono);

        // 2. Si el número tiene 10 dígitos (un celular o fijo de ciudad principal), le añadimos el código de país '52'
        if (strlen($limpio) == 10) {
            return '52' . $limpio;
        }

        // 3. Si el número ya empieza con '52' y tiene 12 dígitos, asumimos que es correcto
        if (substr($limpio, 0, 2) === '52' && strlen($limpio) == 12) {
            return $limpio;
        }

        // Si no cumple las reglas, devolvemos el número limpio para no perder el dato
        return $limpio;
    }
}