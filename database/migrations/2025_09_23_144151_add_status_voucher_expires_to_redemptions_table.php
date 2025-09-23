<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            // kalau kolom belum ada, tambahkan
            if (!Schema::hasColumn('redemptions', 'status')) {
                $table->string('status', 32)->default('issued')->after('reward_id'); // issued|used|expired dll
            }
            if (!Schema::hasColumn('redemptions', 'points_spent')) {
                $table->integer('points_spent')->default(0)->after('status');
            }
            if (!Schema::hasColumn('redemptions', 'voucher_code')) {
                $table->string('voucher_code', 64)->nullable()->after('points_spent');
                $table->index('voucher_code');
            }
            if (!Schema::hasColumn('redemptions', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('voucher_code');
                $table->index('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            if (Schema::hasColumn('redemptions', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
            if (Schema::hasColumn('redemptions', 'voucher_code')) {
                $table->dropIndex(['voucher_code']);
                $table->dropColumn('voucher_code');
            }
            if (Schema::hasColumn('redemptions', 'points_spent')) {
                $table->dropColumn('points_spent');
            }
            if (Schema::hasColumn('redemptions', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
