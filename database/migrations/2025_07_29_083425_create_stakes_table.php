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
        Schema::create('stakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('market_id')->constrained()->onDelete('cascade');
            $table->enum('side', ['yes', 'no']);
            $table->bigInteger('amount'); // Amount in kobo
            $table->decimal('odds_at_placement', 8, 4);
            $table->enum('status', ['active', 'won', 'lost', 'cancelled'])->default('active');
            $table->bigInteger('payout_amount')->default(0); // Payout amount in kobo
            $table->timestamps();
            
            $table->index(['user_id', 'market_id']);
            $table->index('market_id');
            $table->index('side');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stakes');
    }
};
