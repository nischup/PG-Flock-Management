<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $perPage = $request->per_page ?? 10;

    $roles = Role::with('permissions')
        ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
        ->paginate($perPage)
        ->withQueryString();

    // Map paginator meta to include total
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('user/role/Create', [
            'permissions' => Permission::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }


        

        return redirect()->route('user-role.index')->with('success', 'Role created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
        public function edit($id)
        {
            $role = Role::with('permissions')->findOrFail($id); // manually fetch with permissions

            return Inertia::render('user/role/Edit', [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
                'permissions' => Permission::all(),
                'rolePermissions' => $role->permissions->pluck('name')->toArray(), // selected ones
            ]);
        }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the role by ID
        $role = Role::findOrFail($id);

        // Validate input
        $data = $request->validate([
            'name' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name', // each permission must exist
        ]);

        // Update role name only if it has changed
        if (!empty($data['name']) && $data['name'] !== $role->name) {
            $role->update(['name' => $data['name']]);
        }

        // Sync permissions with matching guard
        $permissions = Permission::whereIn('name', $data['permissions'] ?? [])
            ->where('guard_name', $role->guard_name)
            ->get();

        $role->syncPermissions($permissions);

        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('user-role.index')->with('success', 'Role updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('user-role.index')->with('success', 'Role deleted.');
    }
}
