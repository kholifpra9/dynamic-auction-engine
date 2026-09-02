<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'specs',
        'photo_path',
        'starting_price',
        'current_price',
        'current_winner_id',
        'auction_start',
        'auction_end',
        'status',
    ];

    protected $casts = [
        'specs' => 'array',
        'starting_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'auction_start' => 'datetime',
        'auction_end' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class)->latest();
    }

    public function currentWinner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_winner_id');
    }


    public function isActive(): bool
    {
        return $this->status === 'active' && $this->auction_end->isFuture();
    }

    public function highestBid(): ?Bid
    {
        return $this->bids()->orderByDesc('amount')->first();
    }

    public function isCurrentlyActive(): bool
    {
        return $this->status === 'active' && $this->auction_end->isFuture();
    }
}
