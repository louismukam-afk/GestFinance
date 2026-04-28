<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoutePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AccessControlController extends Controller
{
    public function index()
    {
        $this->syncRoutePermissions();

        return view('Admin.Access.index', [
            'roles' => Role::with('permissions')->orderBy('name')->get(),
            'permissions' => RoutePermission::orderBy('route_name')->get(),
            'users' => User::with('roles')->orderBy('name')->get(),
        ]);
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        Role::create($data);

        return back()->with('success', 'Role cree avec succes.');
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $permissionIds = collect($request->input('permissions', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $role->permissions()->sync($permissionIds);

        return back()->with('success', 'Permissions du role mises a jour.');
    }

    public function updateUserRoles(Request $request, User $user)
    {
        $roleIds = collect($request->input('roles', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $request->validate([
            'statut_utilisateur' => 'required|in:actif,non_actif',
        ]);

        $user->roles()->sync($roleIds);
        $user->is_super_admin = $request->boolean('is_super_admin');
        $user->statut_utilisateur = $request->statut_utilisateur;
        $user->save();

        return back()->with('success', 'Roles utilisateur mis a jour.');
    }

    public function syncRoutes()
    {
        $this->syncRoutePermissions();

        return back()->with('success', 'Routes synchronisees avec succes.');
    }

    private function syncRoutePermissions(): void
    {
        foreach (Route::getRoutes() as $route) {
            $method = implode('|', array_diff($route->methods(), ['HEAD']));
            $routeName = $route->getName() ?: $method . ' ' . $route->uri();

            if (!$routeName || $this->ignoredRoute($routeName)) {
                continue;
            }

            RoutePermission::updateOrCreate(
                ['route_name' => $routeName],
                [
                    'method' => $method,
                    'uri' => $route->uri(),
                    'label' => $this->makeLabel($routeName),
                    'is_active' => true,
                ]
            );
        }
    }

    private function ignoredRoute(string $routeName): bool
    {
        return str_starts_with($routeName, 'login')
            || str_starts_with($routeName, 'register')
            || str_starts_with($routeName, 'password.')
            || str_starts_with($routeName, 'verification.')
            || str_starts_with($routeName, 'access.')
            || str_starts_with($routeName, 'audit.')
            || in_array($routeName, ['home', 'logout'], true);
    }

    private function makeLabel(string $routeName): string
    {
        return ucfirst(str_replace(['.', '_'], [' / ', ' '], $routeName));
    }
}
