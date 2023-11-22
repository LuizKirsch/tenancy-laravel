<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index(Request $request){
        if (Auth::check()) {
            return redirect(route('home'));
        }
        return view('admin-tenancy.login');
    }
    public function login_action(Request $request){

        $validator = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:4'
        ]);

        if (Auth::attempt($validator)) {
            return redirect(route('home'));
        }
        $user = User::where('email', $validator['email'])->first();

        if ($user && $user->verifyCredentials($validator['password'])) {
            Auth::login($user);
            return redirect(route('home'));
        }
        else {
            return redirect()->back()->withErrors(['message' => 'Credenciais inválidas.'])->withInput();
        }
    }

    public function logout(){
        Auth::logout();
        return redirect(route('login'));
    }
}
