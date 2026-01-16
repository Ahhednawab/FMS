<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CustomLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // your custom Blade view
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);


        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'is_active' => 1,
        ], $request->filled('remember'))) {

            $user = Auth::user();


            return redirect("/dashboard");
            // if ($user->role_id === 1) {
            //     return redirect('/admin/dashboard');
            // } elseif ($user->role === 'manager') {
            //     return redirect('/manager/dashboard');
            // } else {
            //     return redirect('/user/dashboard');
            // }
        }

        return back()->withErrors([
            'email' => 'Invalid login credentials or account is inactive.',
        ])->withInput($request->only('email', 'remember'));
    }
}
