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

    public function editRolePermissions(Role $role)
    {
        $this->syncRoutePermissions();

        $role->load('permissions');
        $permissions = RoutePermission::orderBy('route_name')->get();

        return view('Admin.Access.permissions', [
            'role' => $role,
            'sections' => $this->buildPermissionSections($permissions),
        ]);
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

    private function buildPermissionSections($permissions): array
    {
        $sections = [];

        foreach ($permissions as $permission) {
            $section = $this->sectionName($permission);
            $sections[$section][] = [
                'permission' => $permission,
                'action' => $this->actionName($permission),
                'context' => $this->contextName($permission),
                'danger' => $this->isDangerousAction($permission),
            ];
        }

        ksort($sections);

        return $sections;
    }

    private function sectionName(RoutePermission $permission): string
    {
        $name = $permission->route_name;
        $uri = $permission->uri;

        $map = [
            'Administration' => ['dashboard', 'admin/', 'entite', 'cycle', 'filiere', 'niveau', 'specialite', 'personnel', 'fonction', 'annee'],
            'Budgets' => ['budget', 'ligne_budgetaire', 'element_ligne', 'donnee_entrees', 'donnee_sorties', 'donnee_ligne'],
            'Bons de commande' => ['bon_commande', 'bons', 'element_bon', 'mes_bons', 'etat_bons'],
            'Caisses' => ['caisse', 'banque', 'retour_caisses', 'decaissements'],
            'Etudiants et factures' => ['etudiant', 'facture', 'reglement', 'tranche', 'scolarite'],
            'Entrees speciales' => ['entrees_speciales'],
            'Reductions de factures' => ['reductions_factures'],
            'Etats et rapports' => ['etat', 'reporting', 'audit'],
        ];

        foreach ($map as $section => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($name, $needle) || str_contains($uri, $needle)) {
                    return $section;
                }
            }
        }

        return 'Autres actions';
    }

    private function actionName(RoutePermission $permission): string
    {
        $name = $permission->route_name;
        $uri = $permission->uri;
        $method = strtoupper($permission->method);

        if (str_contains($name, 'destroy') || str_contains($name, 'delete') || str_contains($uri, 'delete') || str_contains($method, 'DELETE')) {
            return 'Supprimer';
        }

        if (str_contains($name, 'update') || str_contains($name, 'edit') || str_contains($uri, 'edit') || str_contains($method, 'PUT') || str_contains($method, 'PATCH')) {
            return 'Modifier';
        }

        if (str_contains($name, 'store') || str_contains($name, 'create') || str_contains($uri, 'create') || str_contains($method, 'POST')) {
            return 'Creer';
        }

        if (str_contains($name, 'pdf')) {
            return 'Exporter PDF';
        }

        if (str_contains($name, 'excel')) {
            return 'Exporter Excel';
        }

        if (str_contains($name, 'export')) {
            return 'Exporter';
        }

        if (str_contains($name, 'show') || str_contains($name, 'detail')) {
            return 'Voir details';
        }

        if (str_contains($name, 'document')) {
            return 'Voir document';
        }

        if (str_contains($name, 'valider')) {
            return 'Valider';
        }

        if (str_contains($name, 'payer') || str_contains($name, 'remboursement')) {
            return 'Payer / rembourser';
        }

        return 'Afficher';
    }

    private function contextName(RoutePermission $permission): string
    {
        return ucfirst(str_replace(['.', '_', '-'], [' / ', ' ', ' '], $permission->route_name));
    }

    private function isDangerousAction(RoutePermission $permission): bool
    {
        return in_array($this->actionName($permission), ['Supprimer', 'Modifier'], true);
    }
}
