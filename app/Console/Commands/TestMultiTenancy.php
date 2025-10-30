<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Warga;
use App\Models\KetuaRt;

class TestMultiTenancy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:multi-tenancy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test multi-tenancy functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Multi-Tenancy Functionality');
        $this->newLine();

        // Test 1: Check tenants
        $this->info('1. Checking Tenants:');
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $this->line("   - {$tenant->name} (RT {$tenant->no_rt}, RW {$tenant->rw})");
        }
        $this->newLine();

        // Test 2: Check admin user
        $this->info('2. Checking Admin User:');
        $admin = User::where('email', 'admin@simawar.com')->with('tenants')->first();
        if ($admin) {
            $this->line("   - Name: {$admin->name}");
            $this->line("   - Email: {$admin->email}");
            $this->line("   - Can access tenants: " . $admin->tenants->count());
        }
        $this->newLine();

        // Test 3: Check RT users
        $this->info('3. Checking RT Users:');
        $rtUsers = User::whereHas('role', function($query) {
            $query->where('name', 'rt');
        })->with('tenants')->get();
        
        foreach ($rtUsers as $user) {
            $this->line("   - {$user->name} ({$user->email})");
            $this->line("     Tenants: " . $user->tenants->pluck('name')->join(', '));
        }
        $this->newLine();

        // Test 4: Test tenant filtering for Warga
        $this->info('4. Testing Warga filtering by tenant:');
        foreach ($tenants->take(2) as $tenant) {
            $wargaCount = Warga::where('id_rt', $tenant->no_rt)
                               ->where('rw', $tenant->rw)
                               ->count();
            $this->line("   - Tenant {$tenant->name}: {$wargaCount} warga");
        }
        $this->newLine();

        // Test 5: Test tenant filtering for KetuaRt
        $this->info('5. Testing KetuaRt filtering by tenant:');
        foreach ($tenants->take(2) as $tenant) {
            $ketuaCount = KetuaRt::where('no_rt', $tenant->no_rt)->count();
            $this->line("   - Tenant {$tenant->name}: {$ketuaCount} ketua RT");
        }

        $this->newLine();
        $this->info('Multi-tenancy test completed!');
    }
}
