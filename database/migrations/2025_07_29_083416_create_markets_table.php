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
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('description');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamp('closes_at');
            $table->enum('status', ['open', 'resolved', 'cancelled'])->default('open');
            $table->timestamps();
            
            $table->index(['category_id', 'status']);
            $table->index('closes_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
