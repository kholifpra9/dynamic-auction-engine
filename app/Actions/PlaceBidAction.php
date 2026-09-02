<?php

namespace App\Actions;

use App\Events\NewBidPlaced;
use App\Models\Listing;
use App\Models\User;
use App\Models\Bid;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PlaceBidAction
{
    /**
     * Aturan kenaikan minimum bid: harus lebih tinggi minimal 5% dari harga saat ini.
     */
    private const MIN_INCREMENT_PERCENT = 5;

    public function execute(Listing $listing, User $bidder, float $amount): Bid
    {
        return DB::transaction(function () use ($listing, $bidder, $amount) {
            // Lock row supaya tidak ada race condition saat 2 bid masuk bersamaan
            $listing = Listing::where('id', $listing->id)->lockForUpdate()->first();

            $this->guardAuctionIsActive($listing);
            $this->guardNotOwnListing($listing, $bidder);
            $this->guardAmountIsValid($listing, $amount);

            $bid = Bid::create([
                'listing_id' => $listing->id,
                'user_id' => $bidder->id,
                'amount' => $amount,
            ]);

            $listing->update([
                'current_price' => $amount,
                'current_winner_id' => $bidder->id,
            ]);

            event(new NewBidPlaced($bid));

            return $bid;
        });
    }

    private function guardAuctionIsActive(Listing $listing): void
    {
        if ($listing->status !== 'active' || $listing->auction_end->isPast()) {
            throw new RuntimeException('Lelang sudah berakhir.');
        }
    }

    private function guardNotOwnListing(Listing $listing, User $bidder): void
    {
        if ($listing->user_id === $bidder->id) {
            throw new RuntimeException('Anda tidak dapat melakukan bid pada listing milik sendiri.');
        }
    }

    private function guardAmountIsValid(Listing $listing, float $amount): void
    {
        $minimumAllowed = $listing->current_price * (1 + self::MIN_INCREMENT_PERCENT / 100);

        if ($amount < $minimumAllowed) {
            throw new RuntimeException(
                'Bid harus lebih tinggi minimal ' . self::MIN_INCREMENT_PERCENT . '% dari harga saat ini (minimal Rp' . number_format($minimumAllowed, 0, ',', '.') . ').'
            );
        }
    }

    public function minimumNextBid(Listing $listing): float
    {
        return round($listing->current_price * (1 + self::MIN_INCREMENT_PERCENT / 100), 2);
    }
}