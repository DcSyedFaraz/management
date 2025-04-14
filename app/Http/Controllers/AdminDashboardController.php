<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
   public function index(){
    // dd(Auth::user()->getRoleNames());
    return view('dashboard');
   }
}
