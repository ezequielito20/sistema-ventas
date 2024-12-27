<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index(){
        $company = Auth::user()->company;
        return view('admin.index', compact('company'));
    }
}
