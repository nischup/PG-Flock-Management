<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Master\Company;
use App\Models\Master\Shed;
use Illuminate\Support\Facades\Hash;


class UserRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       


        $users = User::query()
            ->with(['roles', 'permissions', 'company', 'shed'])
             ->visibleFor()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate($request->per_page ?? 10)
            ->withQueryString();


        return Inertia::render('user/register/Register', [
            'users' => $users,
            'filters' => $request->only(['search', 'per_page']),
        ]);


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('user/register/Create', [
            'roles' => Role::all(),
            'permissions' => Permission::all(),
            'companies' => Company::all(),  // new
            'sheds' => Shed::all(),         // new
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        


        $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|string|exists:roles,name',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string|exists:permissions,name',
                'password' => 'required|string|min:8|confirmed',
            ]);

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    // You need to set a password or generate one here
                    'password' => Hash::make($request->password), // Replace with actual password handling
                ]);

                // Assign role
                $user->syncRoles($request->role ? [$request->role] : []);

                // Assign direct permissions if any
                if ($request->has('permissions')) {
                    $user->syncPermissions($request->permissions ?? []);
                }

                return redirect()->route('users.index')->with('success', 'User created successfully.');
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
    public function edit(User $user)
    {
        return Inertia::render('user/register/Edit', [
            'user' => $user,
            'roles' => Role::all(),
            'permissions' => Permission::all(),
            'userPermissions' => $user->getDirectPermissions()->pluck('name'),
            'userRole' => $user->roles->pluck('name')->first(), // 👈 this line is key
            'companies' => Company::all(['id', 'name']),
            'sheds' => Shed::all(['id', 'name']),        // <-- send sheds
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name', 
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'company_id' => $request->company_id,
            'shed_id' => $request->shed_id,
        ];

        // Only update password if a new one is provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        $user->syncRoles($request->role ? [$request->role] : []);
        $user->syncPermissions($request->permissions ?? []);

        return redirect()->route('users.index')->with('success', 'User updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
