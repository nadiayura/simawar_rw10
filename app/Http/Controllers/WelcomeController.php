<?php

namespace App\Http\Controllers;

use App\Models\Struktural;
use App\Models\KetuaRt;
use App\Models\Kegiatan;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Get all structural data ordered by urutan with warga relationship
        $strukturals = Struktural::with('warga')->ordered()->active()->get();
        
        // Get all active Ketua RT data with their warga information
        $ketuaRts = KetuaRt::with('warga')->where('is_active', true)->get();
        
        // Organize RW level structure
        $rwStructure = [
            'ketua' => $strukturals->filter(function($s) { return str_contains($s->jabatan, 'Ketua RW'); })->first(),
            'sekretaris' => $strukturals->filter(function($s) { return str_contains($s->jabatan, 'Sekretaris RW'); })->first(),
            'bendahara' => $strukturals->filter(function($s) { return str_contains($s->jabatan, 'Bendahara RW'); })->first(),
        ];
        
        // Organize RT level structure - combine data from both tables
        $rtStructures = [];
        
        // First, get RT data from KetuaRt table (registered RT leaders and staff)
        foreach ($ketuaRts as $ketuaRt) {
            $rtNumber = str_pad($ketuaRt->no_rt, 2, '0', STR_PAD_LEFT); // Format as 01, 02, etc.
            
            // Initialize RT structure if not exists
            if (!isset($rtStructures[$rtNumber])) {
                $rtStructures[$rtNumber] = [
                    'ketua' => null,
                    'sekretaris' => null,
                    'bendahara' => null,
                ];
            }
            
            // Create a pseudo-struktural object for consistency with the view
            $personData = (object) [
                'nama' => $ketuaRt->warga->nama ?? 'Nama tidak tersedia',
                'jabatan' => $ketuaRt->jabatan === 'Ketua RT' ? "Ketua RT {$rtNumber}" : $ketuaRt->jabatan,
                'no_rt' => $rtNumber,
                'periode_mulai' => $ketuaRt->periode_mulai ? date('Y', strtotime($ketuaRt->periode_mulai)) : '',
                'periode_selesai' => $ketuaRt->periode_selesai ? date('Y', strtotime($ketuaRt->periode_selesai)) : '',
                'foto' => $ketuaRt->warga->foto ?? null,
                'deskripsi' => "{$ketuaRt->jabatan} {$rtNumber} yang terdaftar dalam sistem",
                'is_active' => $ketuaRt->is_active,
            ];
            
            // Assign to appropriate position based on jabatan
            switch ($ketuaRt->jabatan) {
                case 'Ketua RT':
                    $rtStructures[$rtNumber]['ketua'] = $personData;
                    break;
                case 'Sekretaris RT':
                    $rtStructures[$rtNumber]['sekretaris'] = $personData;
                    break;
                case 'Bendahara RT':
                    $rtStructures[$rtNumber]['bendahara'] = $personData;
                    break;
            }
        }
        
        // Then, add any RT data from Struktural table that's not already covered
        $strukturalRtData = $strukturals->filter(function($item) {
            return str_contains(strtolower($item->jabatan), 'rt');
        });
        
        foreach ($strukturalRtData as $strukturalRt) {
            $rtNumber = $strukturalRt->no_rt;
            
            // Initialize RT structure if not exists
            if (!isset($rtStructures[$rtNumber])) {
                $rtStructures[$rtNumber] = [
                    'ketua' => null,
                    'sekretaris' => null,
                    'bendahara' => null,
                ];
            }
            
            // Only fill empty positions from Struktural table
            if (str_contains(strtolower($strukturalRt->jabatan), 'ketua rt') && !$rtStructures[$rtNumber]['ketua']) {
                $rtStructures[$rtNumber]['ketua'] = $strukturalRt;
            } elseif (str_contains(strtolower($strukturalRt->jabatan), 'sekretaris') && !$rtStructures[$rtNumber]['sekretaris']) {
                $rtStructures[$rtNumber]['sekretaris'] = $strukturalRt;
            } elseif (str_contains(strtolower($strukturalRt->jabatan), 'bendahara') && !$rtStructures[$rtNumber]['bendahara']) {
                $rtStructures[$rtNumber]['bendahara'] = $strukturalRt;
            }
        }
        
        // Sort RT structures by RT number
        ksort($rtStructures);
        
        // Get recent activities for gallery section (limit to 3 most recent)
        $kegiatans = Kegiatan::with('tenant')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        
        return view('welcome', compact('rwStructure', 'rtStructures', 'kegiatans'));
    }
}