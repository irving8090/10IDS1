<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable; 

class ClienteController extends Controller
{
  
    public function index()
    {
        return Cliente::all();
    }

    
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'telefono' => 'nullable|string|max:15',
                'correo' => 'nullable|email|max:100|unique:clientes,correo',
                'estado_cuenta' => 'required|in:sin deuda,con deuda',
                'fecha_registro' => 'required|date',
            ]);

            $cliente = Cliente::create($data);

            return response()->json($cliente, 201);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error interno del servidor al crear el cliente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id_cliente)
    {
        
        return Cliente::findOrFail($id_cliente);
    }

   
    public function update(Request $request, $id_cliente)
    {
        $cliente = Cliente::findOrFail($id_cliente);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'telefono' => 'sometimes|nullable|string|max:15',
            'correo' => [
                'sometimes',
                'required',
                'email',
                'max:100',
                Rule::unique('clientes', 'correo')->ignore($id_cliente, 'id_cliente')
            ],
            'estado_cuenta' => 'sometimes|required|in:sin deuda,con deuda',
            'fecha_registro' => 'sometimes|required|date',
        ]);

        $cliente->update($data);

        return response()->json($cliente);
    }

    
    public function destroy($id_cliente)
    {
        $cliente = Cliente::findOrFail($id_cliente);
        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado exitosamente.']);
    }
}
