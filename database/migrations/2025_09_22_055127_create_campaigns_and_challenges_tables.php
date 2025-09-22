<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('m_campaigns', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->text('description')->nullable();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->enum('status',['draft','active','ended'])->default('active');
            $t->timestamps();
        });

        Schema::create('m_challenges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campaign_id')->constrained('m_campaigns')->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->enum('type',['weekly','monthly','one_off'])->default('weekly');
            $t->dateTime('start_at')->nullable();
            $t->dateTime('end_at')->nullable();
            $t->integer('base_points')->default(10);
            $t->json('rules')->nullable();
            $t->enum('status',['draft','active','closed'])->default('active');
            $t->timestamps();

            $t->index(['campaign_id','status','start_at','end_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('m_challenges');
        Schema::dropIfExists('m_campaigns');
    }
};
