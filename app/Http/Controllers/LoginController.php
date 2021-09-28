<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function attempt(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string']
        ]);

        $credentials = $request->only('username', 'password');
        $credentials['is_active'] = true;

        if(auth()->attempt($credentials)) {
            return redirect()->intended('netlify');
        }
        return redirect('login')->with('error', 'You have entered invalid credentials');
    }

    public function logout() {
        auth()->logout();
        return redirect('login');
    }
}
