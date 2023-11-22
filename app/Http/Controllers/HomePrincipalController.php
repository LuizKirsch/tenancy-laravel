<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomePrincipalController extends Controller
{
    public function index(){
        return view('admin-tenancy.home');
    }
}
