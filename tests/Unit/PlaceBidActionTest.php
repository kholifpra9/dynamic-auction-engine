<?php

use App\Actions\PlaceBidAction;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function makeActiveListing(User $seller, float $currentPrice = 100000): Listing
{
    $category = Category::factory()->create();

    return Listing::factory()->create([
        'user_id' => $seller->id,
        'category_id' => $category->id,
        'starting_price' => $currentPrice,
        'current_price' => $currentPrice,
        'auction_start' => now(),
        'auction_end' => now()->addMinutes(10),
        'status' => 'active',
    ]);
}

test('bid valid berhasil diterima dan update harga listing', function () {
    $seller = User::factory()->create();
    $bidder = User::factory()->create();
    $listing = makeActiveListing($seller, currentPrice: 100000);

    $bid = app(PlaceBidAction::class)->execute($listing, $bidder, 110000);

    expect($bid->amount)->toEqual(110000)
        ->and($bid->user_id)->toBe($bidder->id)
        ->and($listing->fresh()->current_price)->toEqual(110000)
        ->and($listing->fresh()->current_winner_id)->toBe($bidder->id);
});

test('bid ditolak jika lebih rendah dari kenaikan minimum', function () {
    $seller = User::factory()->create();
    $bidder = User::factory()->create();
    $listing = makeActiveListing($seller, currentPrice: 100000);

    // Kenaikan minimum 5%, jadi 101000 masih terlalu rendah (minimal 105000)
    app(PlaceBidAction::class)->execute($listing, $bidder, 101000);
})->throws(RuntimeException::class);

test('bid ditolak jika listing sudah berakhir', function () {
    $seller = User::factory()->create();
    $bidder = User::factory()->create();
    $listing = makeActiveListing($seller);
    $listing->update(['auction_end' => now()->subMinute(), 'status' => 'ended']);

    app(PlaceBidAction::class)->execute($listing, $bidder, 200000);
})->throws(RuntimeException::class);

test('pemilik listing tidak bisa bid listing miliknya sendiri', function () {
    $seller = User::factory()->create();
    $listing = makeActiveListing($seller);

    app(PlaceBidAction::class)->execute($listing, $seller, 200000);
})->throws(RuntimeException::class);

test('bid baru menggantikan current_winner_id sebelumnya', function () {
    $seller = User::factory()->create();
    $bidder1 = User::factory()->create();
    $bidder2 = User::factory()->create();
    $listing = makeActiveListing($seller, currentPrice: 100000);

    app(PlaceBidAction::class)->execute($listing, $bidder1, 110000);
    app(PlaceBidAction::class)->execute($listing, $bidder2, 120000);

    expect($listing->fresh()->current_winner_id)->toBe($bidder2->id)
        ->and($listing->fresh()->current_price)->toEqual(120000);
});