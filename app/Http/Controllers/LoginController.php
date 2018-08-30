<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //
    protected $redirectTo = '/login';
    public function username(){
        return 'username';
    }
    public function authenticate(Request $request){
        $authResult= Auth::attempt(['username'=>$request['username'],'password'=>$request['password']]);
        if($authResult){
            return redirect()->intended();
        }else{
            return redirect()->to(route('login'));
        }
    }
    public function logout(){
        Auth::logout();
       return redirect()->to(route('login'));
    }
}
