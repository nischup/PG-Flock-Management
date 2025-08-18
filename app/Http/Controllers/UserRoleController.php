<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;

        $roles = Role::with('permissions')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate($perPage)
            ->withQueryString();

        $rolesWithMeta = [
            'data' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ];

        return Inertia::render('user/role/Role', [
            'roles' => $rolesWithMeta,
            'filters' => $request->only(['search', 'per_page', 'page']),
        ]);
    }

    /**
     * Show form to create a new role.
     */
    public function create()
    {
        return Inertia::render('user/role/Create', [
            'permissions' => Permission::all(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        try {

            DB::beginTransaction();
            $role = Role::create(['name' => $request->name]);

            if ($request->permissions) {
                $role->syncPermissions($request->permissions);
            }

            // Clear cached permissions
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
             DB::commit();

            return redirect()->route('user-role.index')
                ->with('success', 'Role created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Role creation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to create role.');
        }
    }

    /**
     * Show form to edit a role.
     */
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return Inertia::render('user/role/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
            ],
            'permissions' => Permission::all(),
            'rolePermissions' => $role->permissions->pluck('name')->toArray(),
        ]);
    }

    /**
     * Update a role.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'name' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        try {

            DB::beginTransaction();
            if (!empty($data['name']) && $data['name'] !== $role->name) {
                $role->update(['name' => $data['name']]);
            }

            $permissions = Permission::whereIn('name', $data['permissions'] ?? [])
                ->where('guard_name', $role->guard_name)
                ->get();

            $role->syncPermissions($permissions);

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            DB::commit();
            return redirect()->route('user-role.index')
                ->with('success', 'Role updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Role update failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update role.');
        }
    }

    /**
     * Delete a role.
     */
    public function destroy(Role $role,$id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            // Clear cached permissions
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role deleted successfully.',
                ]);
            }

            return redirect()->route('user-role.index')->with('success', 'Role deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Role delete failed', ['error' => $e->getMessage()]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete role.',
                ], 500);
            }

            return back()->withErrors(['general' => 'Failed to delete role.']);
        }
    }
}
