<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('app')->plainTextToken;
    
            $arr = [
                'acceso' => "Ok",
                'error' => "",
                'token' => $token,
                'idUsuario' => $user->id,
                'nombreUsuario' => $user->name,
                'email' => $user->email,
            ];
    
            return response()->json($arr);
        } else {
            $arr = [
                'acceso' => "",
                'token' => "",
                'error' => "No existe el usuario y/o contraseña",
                'idUsuario' => 0,
                'nombreUsuario' => ''
            ];
    
            return response()->json($arr, 401);
        }
    }
}
    