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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRegisterController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['roles', 'permissions', 'company', 'shed'])
            ->visibleFor()
            ->when($request->search, function($q) use ($request) {
                $q->where(function($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%")
                          ->orWhereHas('company', function($companyQuery) use ($request) {
                              $companyQuery->where('name', 'like', "%{$request->search}%");
                          })
                          ->orWhereHas('shed', function($shedQuery) use ($request) {
                              $shedQuery->where('name', 'like', "%{$request->search}%");
                          });
                });
            })
            ->when($request->company_id, fn($q) => $q->where('company_id', $request->company_id))
            ->when($request->shed_id, fn($q) => $q->where('shed_id', $request->shed_id))
            ->when($request->role_id, function($q) use ($request) {
                $q->whereHas('roles', function($roleQuery) use ($request) {
                    $roleQuery->where('id', $request->role_id);
                });
            })
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('user/register/Register', [
            'users' => $users,
            'filters' => $request->only(['search', 'per_page', 'company_id', 'shed_id', 'role_id', 'date_from', 'date_to']),
            'companies' => Company::all(['id', 'name']),
            'sheds' => Shed::all(['id', 'name']),
            'roles' => Role::all(['id', 'name']),
        ]);
    }

    public function create()
    {
        return Inertia::render('user/register/Create', [
            'roles' => Role::all(),
            'permissions' => Permission::all(),
            'companies' => Company::all(),
            'sheds' => Shed::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'role'        => 'required|string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
            'password'    => 'required|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'company_id' => $request->company_id,
                'shed_id'    => $request->shed_id,
            ]);

            $user->syncRoles([$request->role]);
            $user->syncPermissions($request->permissions ?? []);

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('User create failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['general' => 'Failed to create user. Please try again.']);
        }
    }

    public function edit(User $user)
    {
        return Inertia::render('user/register/Edit', [
            'user' => $user,
            'roles' => Role::all(),
            'permissions' => Permission::all(),
            'userPermissions' => $user->getDirectPermissions()->pluck('name'),
            'userRole' => $user->roles->pluck('name')->first(),
            'companies' => Company::all(['id', 'name']),
            'sheds' => Shed::all(['id', 'name']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
            'password' => 'nullable|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'company_id' => $request->company_id,
                'shed_id'    => $request->shed_id,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);
            $user->syncRoles([$request->role]);
            $user->syncPermissions($request->permissions ?? []);

            DB::commit();

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('User update failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['general' => 'Failed to update user. Please try again.']);
        }
    }

    public function destroy(User $user)
    {
        try {
        $user->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    } catch (\Throwable $e) {
        Log::error('User delete failed', ['error' => $e->getMessage()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Failed to delete user.'], 500);
        }

        return back()->withErrors(['general' => 'Failed to delete user.']);
     }
    }
}
