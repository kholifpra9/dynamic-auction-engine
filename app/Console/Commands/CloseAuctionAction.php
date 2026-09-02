<?php

namespace App\Console\Commands;

use App\Models\Listing;

class CloseAuctionAction
{
    /**
     * Execute the console command.
     */
    public function execute(Listing $listing): void
    {
        if ($listing->status !== 'active' || $listing->auction_end->isFuture()) {
            return;
        }

        $listing->update(['status' => 'ended']);
    }
}
