<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        //get all tables
        $tables = $this->getAllTables();

        foreach ($tables as $tableName) {
            //ignore default tables
            if (in_array($tableName, ['migrations', 'password_resets', 'failed_jobs', 'oauth_personal_access_clients'])) {
                continue;
            }

            if (!Schema::hasTable($tableName)) continue;

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'created_by_id')) {
                    $table->nullableMorphs('created_by');
                }
                if (!Schema::hasColumn($tableName, 'updated_by_id')) {
                    $table->nullableMorphs('updated_by');
                }
                if (!Schema::hasColumn($tableName, 'deleted_by_id')) {
                    $table->nullableMorphs('deleted_by');
                }
                if (!Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        $tables = $this->getAllTables();

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) continue;

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'created_by_id')) {
                    $table->dropMorphs('created_by');
                }
                if (Schema::hasColumn($tableName, 'updated_by_id')) {
                    $table->dropMorphs('updated_by');
                }
                if (Schema::hasColumn($tableName, 'deleted_by_id')) {
                    $table->dropMorphs('deleted_by');
                }
                if (Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }

    private function getAllTables(): array
    {
        $dbName = DB::getDatabaseName();
       // $dbName = config('database.connections.mysql.database');
        $tables = DB::select("SHOW TABLES");

        //col. name dynamicaly"Tables_in_{db_name}"
        $columnName = "Tables_in_{$dbName}";
        return array_map(fn($table) => $table->$columnName, $tables);
    }
};
