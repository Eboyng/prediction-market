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
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code')->unique()->nullable();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(true);
            $table->boolean('in_app_notifications')->default(true);
            $table->boolean('market_updates')->default(true);
            $table->boolean('stake_confirmations')->default(true);
            $table->boolean('withdrawal_updates')->default(true);
            $table->boolean('referral_updates')->default(true);
            $table->boolean('promo_notifications')->default(true);
            
            $table->index('referral_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'referral_code',
                'email_notifications',
                'sms_notifications',
                'in_app_notifications',
                'market_updates',
                'stake_confirmations',
                'withdrawal_updates',
                'referral_updates',
                'promo_notifications',
            ]);
        });
    }
};
