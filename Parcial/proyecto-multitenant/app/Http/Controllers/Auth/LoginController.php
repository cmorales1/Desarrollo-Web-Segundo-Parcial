<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller {
    public function login(Request $request) {
        $cred = $request->validate([ 'email'=>['required','email'], 'password'=>['required'] ]);
        if (!Auth::attempt($cred)) { return response()->json(['message'=>'Credenciales inválidas'], 401); }
        $user = $request->user();
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token'=>$token,'user'=>$user]);
    }
}