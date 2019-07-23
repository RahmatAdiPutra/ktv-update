<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Ax\Neo\V1\Auth\AuthTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthTrait;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $rememberMe = true;

        try {
            // // login with username & password
            $this->loginWithUsernamePassword($username, $password, $rememberMe);
            return redirect('/');
        } catch (\Exception $e) {
            return redirect()->back()->with('loginError', $e->getMessage());
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

}
