<?php

namespace App\Http\Controllers;

use App\Models\Ingreso; 
use Illuminate\Http\Request;

class IngresoController extends Controller
{
 public function index(Request $request)
    {
        $query = Ingreso::query()
            ->with(['venta.detalles.producto']);

        if ($request->has('buscar')) {
            $query->where('descripcion', 'like', '%' . $request->buscar . '%');
        }

        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha_ingreso', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $ingresos = $query->orderBy('fecha_ingreso', 'desc')->get();

        return response()->json($ingresos);
    }
}