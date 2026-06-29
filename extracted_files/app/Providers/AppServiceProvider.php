<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->autoSetTahunAjaran();
    }

    /**
     * Auto-set the active Tahun Ajaran based on the current date.
     *
     * Semester Ganjil (1) = September – January
     * Semester Genap  (2) = March – June
     * Gap months (July, August, February) = keep current active unchanged.
     */
    private function autoSetTahunAjaran(): void
    {
        // Only run when the tahun_ajaran table exists (skip during migrations)
        try {
            if (!Schema::hasTable('tahun_ajaran')) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        $month = (int) now()->format('m');
        $year  = (int) now()->format('Y');

        // Determine semester from current month
        // Ganjil: Jul(7), Aug(8), Sep(9), Oct(10), Nov(11), Dec(12)
        // Genap:  Jan(1), Feb(2), Mar(3), Apr(4), May(5), Jun(6)
        if (in_array($month, [7, 8, 9, 10, 11, 12])) {
            $semester = '1'; // Ganjil
        } else {
            $semester = '2'; // Genap
        }

        // Calculate academic year name (e.g. "2025/2026")
        if ($semester === '1') {
            $startYear = $year;
        } else {
            $startYear = $year - 1;
        }

        $nama = $startYear . '/' . ($startYear + 1);

        // Check the currently active tahun_ajaran
        $active = DB::table('tahun_ajaran')->where('is_active', 1)->first();

        if ($active) {
            if ($active->nama === $nama) {
                // Already correct — nothing to do
                return;
            }

            // Active row is a different academic year — leave it alone.
            // The coordinator can manually switch via Manage Tahun Ajaran.
        }

        // If NO active tahun_ajaran exists at all, auto-activate the correct one
        if (!$active) {
            DB::transaction(function () use ($nama) {
                $existing = DB::table('tahun_ajaran')->where('nama', $nama)->first();

                if ($existing) {
                    DB::table('tahun_ajaran')->where('id', $existing->id)->update([
                        'is_active' => 1,
                    ]);
                } else {
                    DB::table('tahun_ajaran')->insert([
                        'nama'      => $nama,
                        'is_active' => 1,
                    ]);
                }
            });
        }
    }
}
