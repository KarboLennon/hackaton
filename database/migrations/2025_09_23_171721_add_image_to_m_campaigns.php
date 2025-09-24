<?php

// database/migrations/2025_09_24_000001_add_image_to_m_campaigns.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('m_campaigns', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('description');
        });
    }
    public function down(): void {
        Schema::table('m_campaigns', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
