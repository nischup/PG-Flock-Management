<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            // Parent Stock (PS)
            'order-plans.view',
            'order-plans.create',
            'order-plans.edit',
            'order-plans.delete',
            'ps-receive.view',
            'ps-receive.create',
            'ps-receive.edit',
            'ps-receive.delete',
            'ps-firm-receive.view',
            'ps-firm-receive.create',
            'ps-firm-receive.edit',
            'ps-firm-receive.delete',

            // Shed
            'shed.view',
            'shed.create',
            'shed.edit',
            'shed.delete',
            'shed-receive.view',
            'shed-receive.create',
            'shed-receive.edit',
            'shed-receive.delete',
            'batch-assign.view',
            'batch-assign.create',
            'batch-assign.edit',
            'batch-assign.delete',
            'batch-config.view',
            'batch-config.create',
            'batch-config.edit',
            'batch-config.delete',

            // Farm Operation
            'brooding.view',
            'brooding.create',
            'brooding.edit',
            'brooding.delete',
            'growing.view',
            'growing.create',
            'growing.edit',
            'growing.delete',
            'bird-transfer.view',
            'bird-transfer.create',
            'bird-transfer.edit',
            'bird-transfer.delete',

            // Production Farm
            'production-farm-receive.view',
            'production-farm-receive.create',
            'production-farm-receive.edit',
            'production-farm-receive.delete',
            'production-shed-receive.view',
            'production-shed-receive.create',
            'production-shed-receive.edit',
            'production-shed-receive.delete',
            'batch-assign-production.view',
            'batch-assign-production.create',
            'batch-assign-production.edit',
            'batch-assign-production.delete',
            'laying.view',
            'laying.create',
            'laying.edit',
            'laying.delete',

            //Daily operation
            'daily-operation.Bording.create',
            'daily-operation.bording.edit',

            // Egg
            'egg-classification.view',
            'egg-classification.create',
            'egg-classification.edit',
            'egg-classification.delete',
            // 'egg-classification-grades.view',
            // 'egg-classification-grades.create',
            // 'egg-classification-grades.edit',
            // 'egg-classification-grades.delete',

            // Egg grade
            'egg-grade.view',
            'egg-grade.create',
            'egg-grade.edit',
            'egg-grade.delete',
            // Lab Test
            'ps-lab-test.view',
            'ps-lab-test.create',
            'ps-lab-test.edit',
            'ps-lab-test.delete',
            'firm-lab-tests.view',
            'firm-lab-tests.create',
            'firm-lab-tests.edit',
            'firm-lab-tests.delete',

            // Vaccine
            'vaccine-schedule.view',
            'vaccine-schedule.create',
            'vaccine-schedule.edit',
            'vaccine-schedule.delete',
            'vaccine-routing.view',
            'vaccine-routing.create',
            'vaccine-routing.edit',
            'vaccine-routing.delete',
            'upcomming-vaccine.view',
            'upcomming-vaccine.create',
            'upcomming-vaccine.edit',
            'upcomming-vaccine.delete',

            // Reports
            'daily-flock-report.view',
            'bird-transfer-receive-report.view',

            // Audit Log
            'audit-log.view',

            // Master Setup
            'unit.view',
            'unit.create',
            'unit.edit',
            'unit.delete',
            'feed.view',
            'feed.create',
            'feed.edit',
            'feed.delete',
            'feed-type.view',
            'feed-type.create',
            'feed-type.edit',
            'feed-type.delete',
            'shed.view', // already included above
            'disease.view',
            'disease.create',
            'disease.edit',
            'disease.delete',
            'medicine.view',
            'medicine.create',
            'medicine.edit',
            'medicine.delete',
            'vaccine.view',
            'vaccine.create',
            'vaccine.edit',
            'vaccine.delete',
            'vaccine-type.view',
            'vaccine-type.create',
            'vaccine-type.edit',
            'vaccine-type.delete',
            'company.view',
            'company.create',
            'company.edit',
            'company.delete',
            'project.view',
            'project.create',
            'project.edit',
            'project.delete',
            'supplier.view',
            'supplier.create',
            'supplier.edit',
            'supplier.delete',
            'chick-type.view',
            'chick-type.create',
            'chick-type.edit',
            'chick-type.delete',
            'breed-type.view',
            'breed-type.create',
            'breed-type.edit',
            'breed-type.delete',
            'approval-matrix-config.view',
            'approval-matrix-config.create',
            'approval-matrix-config.edit',
            'approval-matrix-config.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create SuperAdmin role
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);

        // Assign all permissions to SuperAdmin
        $superAdminRole->syncPermissions(Permission::all());

        $userExists = User::role('superadmin')->exists();

        if (! $userExists) {

            // Create default SuperAdmin user if none exists
            $user = User::create([
                'name' => 'PG Admin',
                'email' => 'provita@mail.com',
                'password' => Hash::make('12345678'), // ⚠️ change to secure password
                'company_id' => 0,
                'shed_id' => 0,
            ]);

            $user->assignRole($superAdminRole);
        }
    }
}
