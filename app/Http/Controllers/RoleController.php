<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Controller methods will be defined here
    public function index()
    {
        return response()->json(['message' => 'RoleController index method']);
    }
}
