<?php

namespace Roui\ModelFileManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ModelFileManager extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * This command adds a new JSON 'files' field to the specified model's table.
     * Example: php artisan modelfilemanager:add-file-field {model}
     *
     * @var string
     */
    protected $signature = 'modelfilemanager:add-file-field {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a migration to add a JSON file field to the specified model table';

    /**
     * Handle the command execution.
     * 
     * Generates a migration and modifies it to include a JSON 'files' field for the given model.
     *
     * @return void
     */
    public function handle(): void
    {
        // Retrieve the model name passed as an argument
        $modelName = $this->argument('model');

        // Generate the corresponding table name by pluralizing the model name
        $tableName = Str::plural(strtolower($modelName));

        // Generate a new migration file using Artisan's 'make:migration' command
        Artisan::call('make:migration', [
            'name' => "add_files_field_to_{$tableName}_table",
            '--table' => $tableName,
        ]);

        // Get the most recent migration file created
        $migrationPath = $this->getLatestMigrationFile();

        if ($migrationPath) {
            // Modify the migration file to include the 'files' JSON field
            $this->addJsonFieldToMigration($migrationPath, $tableName);
            $this->info("Migration to add 'files' field to {$tableName} table has been created and updated.");
        } else {
            $this->error("Failed to find the migration file.");
        }
    }

    /**
     * Get the latest migration file created in the database/migrations directory.
     * 
     * This function returns the last migration file based on modification time.
     *
     * @return string|null The path to the latest migration file or null if no files exist.
     */
    protected function getLatestMigrationFile(): ?string
    {
        // Get all migration files from the migrations directory
        $migrationFiles = File::glob(database_path('migrations') . '/*.php');
        
        // Return the most recently created migration file, or null if there are no files
        return $migrationFiles ? end($migrationFiles) : null;
    }

    /**
     * Modify the given migration file to include a JSON 'files' field.
     * 
     * This function inserts the code to add a 'files' JSON field into the migration file.
     *
     * @param string $migrationPath The path to the migration file.
     * @param string $tableName The name of the table to modify.
     * @return void
     */
    protected function addJsonFieldToMigration(string $migrationPath, string $tableName): void
    {
        // Read the content of the migration file
        $migrationContent = file_get_contents($migrationPath);

        // Inject the 'files' JSON field definition into the table schema
        $newField = "\n\t\t\t\$table->json('files')->nullable();";
        $migrationContent = str_replace(
            "Schema::table('{$tableName}', function (Blueprint \$table) {",
            "Schema::table('{$tableName}', function (Blueprint \$table) {{$newField}",
            $migrationContent
        );

        // Write the modified content back into the migration file
        file_put_contents($migrationPath, $migrationContent);
    }
}
