<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\intern\DashboardController as InternDashboard;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin() || $user->isPengawas() || $user->isMentor()) {
            return app(AdminDashboard::class)->index();
        }

        return app(InternDashboard::class)->index();
    }
}
