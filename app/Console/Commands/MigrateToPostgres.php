<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateToPostgres extends Command
{
    protected $signature = 'db:migrate-to-postgres {--export : Export data from MySQL to JSON files}
                                                   {--import : Import data from JSON files to PostgreSQL}';

    protected $description = 'Migrate data from MySQL to PostgreSQL';

    protected $exportPath;

    protected $tables = [
        'users',
        'vacancies',
        'applications',
        'application_files',
        'ai_logs',
        'chat_rooms',
        'chat_messages',
        'chat_participants',
        'video_meetings',
        'video_meeting_participants',
        'webrtc_signals',
        'tests',
        'test_questions',
        'candidate_tests',
        'candidate_test_answers',
        'candidate_notifications',
        'settings',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->exportPath = storage_path('app/db_export');
    }

    public function handle()
    {
        if ($this->option('export')) {
            return $this->exportFromMysql();
        }

        if ($this->option('import')) {
            return $this->importToPostgres();
        }

        $this->error('Please specify --export or --import option');
        return 1;
    }

    protected function exportFromMysql()
    {
        $this->info('Exporting data from MySQL...');

        // Create export directory
        if (!is_dir($this->exportPath)) {
            mkdir($this->exportPath, 0755, true);
        }

        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->warn("Table {$table} does not exist, skipping...");
                continue;
            }

            $this->info("Exporting {$table}...");
            $data = DB::table($table)->get()->toArray();

            $jsonPath = $this->exportPath . "/{$table}.json";
            file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("  Exported " . count($data) . " records");
        }

        $this->info('');
        $this->info('Export completed! Files saved to: ' . $this->exportPath);
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Update .env to use PostgreSQL (DB_CONNECTION=pgsql)');
        $this->info('2. Create the database in PostgreSQL');
        $this->info('3. Run: php artisan migrate:fresh');
        $this->info('4. Run: php artisan db:migrate-to-postgres --import');

        return 0;
    }

    protected function importToPostgres()
    {
        $this->info('Importing data to PostgreSQL...');

        if (!is_dir($this->exportPath)) {
            $this->error('Export directory not found. Run --export first.');
            return 1;
        }

        // Disable foreign key checks
        DB::statement('SET session_replication_role = replica;');

        foreach ($this->tables as $table) {
            $jsonPath = $this->exportPath . "/{$table}.json";

            if (!file_exists($jsonPath)) {
                $this->warn("No export file for {$table}, skipping...");
                continue;
            }

            if (!Schema::hasTable($table)) {
                $this->warn("Table {$table} does not exist in PostgreSQL, skipping...");
                continue;
            }

            $this->info("Importing {$table}...");

            $data = json_decode(file_get_contents($jsonPath), true);

            if (empty($data)) {
                $this->info("  No data to import");
                continue;
            }

            // Clear existing data
            DB::table($table)->truncate();

            // Import in chunks
            $chunks = array_chunk($data, 100);
            foreach ($chunks as $chunk) {
                // Convert objects to arrays if needed
                $insertData = array_map(function ($row) {
                    return (array) $row;
                }, $chunk);

                DB::table($table)->insert($insertData);
            }

            // Reset sequence for PostgreSQL
            $this->resetSequence($table);

            $this->info("  Imported " . count($data) . " records");
        }

        // Re-enable foreign key checks
        DB::statement('SET session_replication_role = DEFAULT;');

        $this->info('');
        $this->info('Import completed!');

        return 0;
    }

    protected function resetSequence($table)
    {
        try {
            $maxId = DB::table($table)->max('id');
            if ($maxId) {
                $sequence = "{$table}_id_seq";
                DB::statement("SELECT setval('{$sequence}', {$maxId})");
            }
        } catch (\Exception $e) {
            // Table might not have an id column or sequence
        }
    }
}
