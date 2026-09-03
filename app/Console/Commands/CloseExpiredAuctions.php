<?php

namespace App\Console\Commands;

use App\Action\CloseAuctionAction;
use App\Models\Listing;
use Illuminate\Console\Command;

class CloseExpiredAuctions extends Command
{
    /**
     * Signature untuk dipanggil di terminal: php artisan auctions:close-expired
     */
    protected $signature = 'auctions:close-expired';

    /**
     * Deskripsi command
     */
    protected $description = 'Tutup lelang yang sudah melewati waktu berakhir dan tentukan pemenang';

    public function handle(CloseAuctionAction $closeAuction): void
    {
        $expired = Listing::where('status', 'active')
            ->where('auction_end', '<=', now())
            ->get();

        foreach ($expired as $listing) {
            $closeAuction->execute($listing);
            $this->info("Listing #{$listing->id} ditutup. Pemenang: " .
                ($listing->current_winner_id ? $listing->currentWinner->name : 'Tidak ada bid'));
        }

        $this->info("Total {$expired->count()} lelang ditutup.");
    }
}