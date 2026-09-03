<?php

namespace App\Actions;

use App\Models\Listing;

class CloseAuctionAction
{
    public function execute(Listing $listing): void
    {
        if ($listing->status !== 'active' || $listing->auction_end->isFuture()) {
            return;
        }

        $listing->update(['status' => 'ended']);
    }
}
