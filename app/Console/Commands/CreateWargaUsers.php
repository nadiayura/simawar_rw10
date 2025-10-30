<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Warga;
use App\Models\User;
use App\Models\Role;

class CreateWargaUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warga:create-users {--force : Force creation even if some emails are duplicated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user accounts for existing warga records that don\'t have user accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating user accounts for existing warga records...');

        // Get all warga that have email but no user account
        $wargaWithoutUsers = Warga::whereNotNull('email')
            ->where('email', '!=', '')
            ->whereDoesntHave('user')
            ->get();

        if ($wargaWithoutUsers->isEmpty()) {
            $this->info('No warga records found that need user accounts.');
            return;
        }

        $this->info("Found {$wargaWithoutUsers->count()} warga records that need user accounts.");

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($wargaWithoutUsers as $warga) {
            try {
                $user = $warga->createUserAccount();
                
                if ($user) {
                    $this->line("✓ Created user for: {$warga->nama} ({$warga->email})");
                    $created++;
                } else {
                    $this->warn("⚠ Skipped {$warga->nama} ({$warga->email}) - Email might already be taken");
                    $skipped++;
                }
            } catch (\Exception $e) {
                $this->error("✗ Error creating user for {$warga->nama}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Created: {$created} user accounts");
        $this->info("- Skipped: {$skipped} records");
        $this->info("- Errors: {$errors} records");

        if ($created > 0) {
            $this->newLine();
            $this->info("Default password for new users is 'password123'.");
            $this->info("Users should change their password after first login.");
        }
    }
}