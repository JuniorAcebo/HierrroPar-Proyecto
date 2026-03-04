<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Protege todo el controlador
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        return view('admin.panel.index', [
            'user' => $user
        ]);
    }
}