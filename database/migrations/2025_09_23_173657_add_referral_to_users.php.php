<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->string('referral_code')->unique()->nullable();   // kode milik user
            $t->unsignedBigInteger('referred_by')->nullable();   // siapa yang mengundang (untuk user baru)
            $t->foreign('referred_by')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('referred_by');
            $t->dropUnique(['referral_code']);
            $t->dropColumn(['referral_code','referred_by']);
        });
    }
};