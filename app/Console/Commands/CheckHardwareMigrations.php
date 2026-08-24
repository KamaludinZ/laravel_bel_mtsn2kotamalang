<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckHardwareMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hardware:check-migrations
                            {--seed : Automatically seed missing data}
                            {--fix : Attempt to fix issues automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if all hardware system migrations and seeders have been run';

    /**
     * Required tables and their minimum row counts
     */
    protected array $requiredTables = [
        'speaker_zones' => 8,        // 8 Modbus channels
        'rooms' => 10,               // At least 10 rooms
        'hardware_configs' => 1,     // One primary config
        'hardware_command_queue' => 0,
        'hardware_logs' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking Hardware System Migrations...');
        $this->newLine();

        $allGood = true;
        $missingData = [];

        // Check each required table
        foreach ($this->requiredTables as $table => $minRows) {
            $status = $this->checkTable($table, $minRows);

            if ($status === 'missing') {
                $this->error("❌ Table '$table' does not exist!");
                $allGood = false;
            } elseif ($status === 'empty') {
                $this->warn("⚠️  Table '$table' exists but needs data (min $minRows rows)");
                $missingData[] = $table;
            } else {
                $this->info("✅ Table '$table' OK - $status rows");
            }
        }

        $this->newLine();

        // Handle missing data
        if (!empty($missingData) && ($this->option('seed') || $this->option('fix'))) {
            $this->info('🌱 Attempting to seed missing data...');

            foreach ($missingData as $table) {
                $this->seedTable($table);
            }

            $this->newLine();
            $this->info('✅ Seeding completed. Re-checking...');
            $this->newLine();

            // Re-check after seeding
            return $this->handle();
        }

        // Show summary
        if ($allGood && empty($missingData)) {
            $this->newLine();
            $this->info('========================================');
            $this->info('✅ All hardware migrations are complete!');
            $this->info('✅ All required data is present!');
            $this->info('========================================');
            return Command::SUCCESS;
        } else {
            $this->newLine();
            $this->error('========================================');
            $this->error('❌ Some migrations or data are missing!');

            if (!empty($missingData)) {
                $this->warn('');
                $this->warn('To seed missing data, run:');
                $this->warn('  php artisan hardware:check-migrations --seed');
            }

            if (!$allGood) {
                $this->warn('');
                $this->warn('To run missing migrations, run:');
                $this->warn('  php artisan migrate --force');
            }

            $this->error('========================================');
            return Command::FAILURE;
        }
    }

    /**
     * Check a table's existence and row count
     */
    protected function checkTable(string $table, int $minRows): string|int
    {
        try {
            if (!Schema::hasTable($table)) {
                return 'missing';
            }

            $count = DB::table($table)->count();

            if ($count < $minRows) {
                return 'empty';
            }

            return $count;
        } catch (\Exception $e) {
            $this->error("Error checking table '$table': " . $e->getMessage());
            return 'missing';
        }
    }

    /**
     * Seed a specific table
     */
    protected function seedTable(string $table): void
    {
        $seederMap = [
            'speaker_zones' => 'HardwareSeeder', // HardwareSeeder seeds both speaker_zones and hardware_configs
            'rooms' => 'RoomSeeder',
            'hardware_configs' => 'HardwareSeeder',
        ];

        if (!isset($seederMap[$table])) {
            $this->warn("No seeder defined for table '$table'");
            return;
        }

        $seederClass = $seederMap[$table];

        try {
            $this->info("  Seeding $table with $seederClass...");
            $this->call('db:seed', [
                '--class' => $seederClass,
                '--force' => true,
            ]);
            $this->info("  ✅ $seederClass completed");
        } catch (\Exception $e) {
            $this->error("  ❌ Failed to seed $table: " . $e->getMessage());
        }
    }
}
