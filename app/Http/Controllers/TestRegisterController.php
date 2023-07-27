<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestRegisterController extends Controller
{
    public function showRegister(){
        return view('register');
    }
}
