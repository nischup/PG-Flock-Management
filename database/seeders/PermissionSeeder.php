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
            

            'report.view',
            'report.generate',
            'doc-receive.view',
            'doc-receive.create',
            'doc-receive.edit',
            'doc-receive.delete', 
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create SuperAdmin role
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);

        // Assign all permissions to SuperAdmin
        $superAdminRole->syncPermissions(Permission::all());

        // 🔹 Create default SuperAdmin user with company_id=1 and shed_id=1
        $user = User::firstOrCreate(
            ['email' => 'provita@mail.com'], // unique field
            [
                'name'       => 'PG Admin',
                'password'   => Hash::make('password'), // change this to secure password
                'company_id' => 1,
                'shed_id'    => 1,
            ]
        );

        // Assign role to the user
        if ($user) {
            $user->assignRole($superAdminRole);
        }
        
    }
}

