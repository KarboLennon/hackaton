<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_platform_to_detail_content_metrics.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('detail_content_metrics', function (Blueprint $table) {
            $table->string('platform', 20)->nullable()->after('submission_id')->index();
        });
    }

    public function down(): void {
        Schema::table('detail_content_metrics', function (Blueprint $table) {
            $table->dropColumn('platform');
        });
    }
};
