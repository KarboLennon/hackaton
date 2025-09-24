<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_ledgers', function (Blueprint $table) {
            $table->id();
            // 'signup' / 'activation' / dst.
            $table->string('type', 32);
            $table->unsignedBigInteger('referrer_id'); // pengundang
            $table->unsignedBigInteger('referred_id'); // yang diundang
            $table->integer('points')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('referred_id')->references('id')->on('users')->cascadeOnDelete();

            // Cegah duplikasi kredit per pasangan (type, referrer, referred)
            $table->unique(['type', 'referrer_id', 'referred_id'], 'referral_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_ledgers');
    }
};
