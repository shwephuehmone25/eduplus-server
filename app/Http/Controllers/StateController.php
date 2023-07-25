<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StateController extends Controller
{
   /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        $states = State::all();

        return response()->json([
            'data' => $states
        ]);
    }
}
