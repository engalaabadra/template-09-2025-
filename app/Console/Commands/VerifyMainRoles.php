<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

/**
 * This command verifies that all main roles defined in the config
 * exist in the database, and optionally puts the site into maintenance
 * mode in production if there is a mismatch.
 */
class VerifyMainRoles extends Command
{
    /** @var string The name and signature of the console command */
    protected $signature = 'verify:main-roles';

    /** @var string The console command description */
    protected $description = 'Ensure main roles in config match roles in database.';

    /**
     * Execute the console command.
     *
     * @return int Command exit code (SUCCESS or FAILURE)
     */
    public function handle()
    {
        // Log that the command has started executing
        \Log::info('✅ verify:main-roles executed');

        // Get role names from the roles structure config
        $configRoles = array_keys(config('spatie_seeder.roles_structure'));

        // Get all role names from the database
        $dbRoles = \App\Models\Role::pluck('name')->toArray();

        // Track if there's any mismatch
        $hasMismatch = false;

        // Loop through each config-defined role
        foreach ($configRoles as $roleName) {
            // Check if the role is missing from the DB
            if (!in_array($roleName, $dbRoles)) {
                $this->error("❌ Main role [{$roleName}] is missing or renamed in the database!");
                $hasMismatch = true;
            }
        }

        // If there is a mismatch
        if ($hasMismatch) {
            // Log an error
            logger()->error('Main role mismatch detected during verify:main-roles');

            // If the app is running in production, put it into maintenance
            if (App::environment('production')) {
                File::put(storage_path('framework/down'), json_encode([
                    'time' => now()->timestamp,
                    'message' => 'Main roles mismatch - site is in maintenance.',
                    'retry' => null,
                    'allowed' => [],
                ]));

                $this->error("🚨 Site put into maintenance mode due to role mismatch.");
            }

            // Return failure status
            return Command::FAILURE;
        }

        // All roles are valid, print success message
        $this->info("✅ All main roles are present and match the config.");

        // Return success status
        return Command::SUCCESS;
    }
}
