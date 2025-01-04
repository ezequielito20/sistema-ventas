<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        // Obtener conteos básicos
        $usersCount = User::where('company_id', Auth::user()->company_id)->count();
        $rolesCount = Role::count();

        // Usuarios por rol
        $usersByRole = Role::withCount(['users' => function($query) {
            $query->where('company_id', Auth::user()->company_id);
        }])->get()->map(function($role) {
            return [
                'name' => ucfirst($role->name),
                'count' => $role->users_count
            ];
        });

        // Usuarios por mes (últimos 6 meses)
        $usersPerMonth = User::where('company_id', Auth::user()->company_id)
            ->select(DB::raw('DATE_FORMAT(created_at, "%M %Y") as month'), DB::raw('count(*) as count'))
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('created_at')
            ->get();

        return view('admin.index', compact(
            'usersCount',
            'rolesCount',
            'usersByRole',
            'usersPerMonth'
        ));
    }
}
