<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;

class SpeedUpAllAuctions extends Command
{
    protected $signature = 'auctions:speed-up-all {seconds=30 : Sisa waktu dalam detik untuk semua listing aktif}';
    protected $description = 'Percepat SEMUA listing aktif agar tersisa N detik (default 30) — khusus testing/demo';

    public function handle(): void
    {
        $seconds = (int) $this->argument('seconds');

        $listings = Listing::where('status', 'active')->get();

        if ($listings->isEmpty()) {
            $this->warn('Tidak ada listing aktif saat ini.');
            return;
        }

        foreach ($listings as $listing) {
            $listing->update([
                'auction_end' => now()->addSeconds($seconds),
            ]);

            $this->info("Listing #{$listing->id} ({$listing->title}) → sisa {$seconds} detik.");
        }

        $this->info("Total {$listings->count()} listing dipercepat menjadi tersisa {$seconds} detik.");
    }
}