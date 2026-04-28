<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->date_debut, fn($q) => $q->whereDate('created_at', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('created_at', '<=', $request->date_fin))
            ->when($request->id_user, fn($q) => $q->where('user_id', $request->id_user))
            ->when($request->method, fn($q) => $q->where('method', $request->method))
            ->when($request->route_name, fn($q) => $q->where('route_name', 'like', '%' . $request->route_name . '%'))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('Admin.Audit.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
        ]);
    }
}
