<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('specs');
            $table->string('photo_path')->nullable();
            $table->decimal('starting_price', 12, 2);
            $table->decimal('current_price', 12, 2);
            $table->foreignId('current_winner_id')->nullable()->constrained('users');
            $table->timestamp('auction_start');
            $table->timestamp('auction_end');
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
