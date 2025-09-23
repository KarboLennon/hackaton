<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('points_cost');
            $table->unsignedInteger('stock')->nullable();  // null = unlimited
            $table->string('image_path')->nullable();      // path di storage
            $table->foreignId('category_id')->nullable()->constrained('reward_categories')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rewards');
    }
};
