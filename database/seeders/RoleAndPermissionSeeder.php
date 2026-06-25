<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Profile;


class RoleAndPermissionSeeder extends Seeder
{

    public function run(): void
    {
        // Clear all related role/permission tables before seeding
        $this->truncateTables();

        // Load roles and permissions configuration from config/spatie_seeder.php
        $config = Config::get('spatie_seeder.roles_structure');

        // If configuration is missing, show error and stop execution
        if ($config === null) {
            $this->command->error("The configuration has not been published or is missing.");
            return;
        }

        // Loop through each role defined in the configuration
        foreach ($config as $roleName => $modules) {

            // Create or fetch the role with the given name and guard
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                [
                    'display_name' => ucwords(str_replace('_', ' ', $roleName)),
                    'is_protected' => true, ////  because main role 
                ]
            );

            // Initialize an array to hold permission IDs
            $permissions = [];

            // Loop through each module and its allowed actions
            foreach ($modules as $module => $actions) {

                // Split actions by comma and iterate through them
                foreach (explode(',', $actions) as $action) {

                    // Build permission name in the format "module_action"
                    $permissionName = trim("{$module}_{$action}");

                    // Create or fetch the permission and store its ID
                    $permissions[] = Permission::firstOrCreate(
                        ['name' => $permissionName, 'guard_name' => 'web'],
                        ['display_name' => ucwords(str_replace('_', ' ', $permissionName))]
                    )->id;

                    // Output to console that the permission was created
                    $this->command->info("Creating Permission: {$permissionName}");
                }
            }

            // Sync all collected permissions with the current role
            $role->permissions()->sync($permissions);

            // Output to console that the role was created
            $this->command->info("Created Role: " . strtoupper($roleName));
        }

        // After creating roles and permissions, create default users for each role
        if (Config::get('spatie_seeder.create_users', true)) {
            // Loop through role names only (keys of the config)
            foreach (array_keys($config) as $roleName) {
                // Create a default user for each role
                $this->createDefaultUsers($roleName);
            }
        }
    }

    /**
     * Create default users for each role.
     */
    private function createDefaultUsers($roleName): void
    {
        // Generate a default email for the user based on role name
        $email = $roleName . '@gmail.com';

        // Create the user with email, hashed password, and username
       $user = User::firstOrCreate(
            ['email' => $email],
            [
                'password' => bcrypt('password'),
                'is_protected' => true, //  because connect with main role
            ]
        );

        // Create a related profile record for the user
        Profile::create([
            'user_id' => $user->id,
            'full_name' => $roleName,
            'username' => $roleName,

        ]);

        // Find the role by name and assign it to the user
        $role = Role::findByName($roleName, 'web');
        $user->assignRole($role);

        // Output to console that the role was assigned to the user
        $this->command->info("Assigned Role: {$roleName} to User: {$email}");
    }

    /**
     * Truncate all related tables.
     */
    private function truncateTables(): void
    {
        $this->command->info('Truncating roles, permissions, and related tables');
        Schema::disableForeignKeyConstraints();

        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        Role::truncate();
        Permission::truncate();

        Schema::enableForeignKeyConstraints();
    }

}
