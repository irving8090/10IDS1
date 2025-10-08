<?php

namespace App\Http\Controllers;

use App\Models\Egreso;
use Illuminate\Http\Request;

class EgresoController extends Controller
{
    public function index(Request $request)
    {
        $query = Egreso::query()
            // Carga la compra, sus detalles y el producto de cada detalle
            ->with(['compra.detalles.producto']);

        // Filtro de búsqueda por descripción
        if ($request->has('buscar')) {
            $query->where('descripcion', 'like', '%' . $request->buscar . '%');
        }

        // Filtro por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha_egreso', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $egresos = $query->orderBy('fecha_egreso', 'desc')->get();

        return response()->json($egresos);
    }
}