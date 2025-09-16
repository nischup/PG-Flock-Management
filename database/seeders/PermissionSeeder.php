<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

            'ps-receive.view',
            'ps-receive.create',
            'ps-receive.edit',
            'ps-receive.delete',

            'shed.view',
            'shed.create',
            'shed.edit',
            'shed.delete',

            'vaccine.view',
            'vaccine.create',
            'vaccine.edit',
            'vaccine.delete',

            'vaccine-type.view',
            'vaccine-type.create',
            'vaccine-type.edit',
            'vaccine-type.delete',

            'disease.view',
            'disease.create',
            'disease.edit',
            'disease.delete',

            'medicine.view',
            'medicine.create',
            'medicine.edit',
            'medicine.delete',

            'chick-type.view',
            'chick-type.create',
            'chick-type.edit',
            'chick-type.delete',

            'company.view',
            'company.create',
            'company.edit',
            'company.delete',

            'project.view',
            'project.create',
            'project.edit',
            'project.delete',

            'feed-type.view',
            'feed-type.create',
            'feed-type.edit',
            'feed-type.delete',

            'feed.view',
            'feed.create',
            'feed.edit',
            'feed.delete',

            'unit.view',
            'unit.create',
            'unit.edit',
            'unit.delete',

            'supplier.view',
            'supplier.create',
            'supplier.edit',
            'supplier.delete',

            'ps-lab-test.view',
            'ps-lab-test.create',
            'ps-lab-test.edit',
            'ps-lab-test.delete',

            'breed-type.view',
            'breed-type.create',
            'breed-type.edit',
            'breed-type.delete',


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
            // Get the first company and shed IDs
            $firstCompanyId = DB::table('companies')->orderBy('id')->value('id');
            $firstShedId = DB::table('sheds')->orderBy('id')->value('id');

            // Only create user if we have valid company and shed IDs
            if ($firstCompanyId && $firstShedId) {
                // Create default SuperAdmin user if none exists
                $user = User::create([
                    'name'       => 'PG Admin',
                    'email'      => 'provita@mail.com',
                    'password'   => Hash::make('12345678'), // ⚠️ change to secure password
                    'company_id' => $firstCompanyId,
                    'shed_id'    => $firstShedId,
                ]);

                $user->assignRole($superAdminRole);
            }
        }


    }
}

