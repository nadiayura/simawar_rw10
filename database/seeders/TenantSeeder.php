<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warga;
use App\Models\KetuaRt;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Clear existing tenant-user relationships
        \DB::table('tenant_user')->delete();
        
        // Get unique RT numbers from existing data
        $rtData = Warga::select('id_rt', 'rw')->distinct()->get();
        
        foreach ($rtData as $data) {
            $idRt = $data->id_rt;
            $rw = $data->rw;
            
            // Create or update tenant for each RT
            $tenant = Tenant::updateOrCreate(
                ['slug' => "rt-{$idRt}"],
                [
                    'name' => "RT {$idRt}",
                    'no_rt' => $idRt,
                    'rw' => $rw,
                    'description' => "Rukun Tetangga {$idRt} RW {$rw}",
                    'is_active' => true,
                ]
            );

            // Find RT leader for this RT
            $ketuaRt = KetuaRt::where('no_rt', $idRt)->where('is_active', true)->first();
            if ($ketuaRt) {
                $warga = Warga::find($ketuaRt->id_warga);
                if ($warga) {
                    $user = User::where('warga_id', $warga->id)->first();
                    if ($user) {
                        // Attach RT user to their tenant
                        $tenant->users()->syncWithoutDetaching([$user->id]);
                    }
                }
            }
        }

        // Attach RW users to all tenants in their RW
        $rwUsers = User::whereHas('role', function($query) {
            $query->where('name', 'rw');
        })->get();

        foreach ($rwUsers as $rwUser) {
            if ($rwUser->warga_id) {
                $warga = $rwUser->warga;
                if ($warga) {
                    // Get all tenants in the same RW
                    $tenants = Tenant::where('rw', $warga->rw)->get();
                    foreach ($tenants as $tenant) {
                        $tenant->users()->syncWithoutDetaching([$rwUser->id]);
                    }
                }
            }
        }
    }
}
