<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            'ps.receive.view',
            'ps.receive.create',
            'ps.receive.edit',
            'ps.receive.delete',

            'shed.view',
            'shed.create',
            'shed.edit',
            'shed.delete',

            'vaccine.view',
            'vaccine.create',
            'vaccine.edit',
            'vaccine.delete',

        ];



        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create SuperAdmin role
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);

        // Assign all permissions to SuperAdmin
        $superAdminRole->syncPermissions(Permission::all());

        $userExists = User::role('superadmin')->exists();

        if (!$userExists) {
            // Create default SuperAdmin user if none exists
            $user = User::create([
                'name'       => 'PG Admin',
                'email'      => 'provita@mail.com',
                'password'   => Hash::make('12345678'), // ⚠️ change to secure password
                'company_id' => 1,
                'shed_id'    => 1,
            ]);

            $user->assignRole($superAdminRole);
        }


    }
}

