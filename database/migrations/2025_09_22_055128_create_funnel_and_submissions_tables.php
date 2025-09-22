<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // (Opsional) profil user
        Schema::create('detail_user_profiles', function (Blueprint $t) {
            $t->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $t->string('phone')->nullable();
            $t->string('ig_handle')->nullable();
            $t->string('tiktok_handle')->nullable();
            $t->string('city')->nullable();
            $t->text('bio')->nullable();
            $t->timestamps();
        });

        // funnel events
        Schema::create('detail_funnel_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained('m_campaigns')->nullOnDelete();
            $t->enum('stage',['awareness','registration','selection','activation']);
            $t->dateTime('occurred_at')->useCurrent();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['user_id','stage','occurred_at']);
            $t->index(['campaign_id','stage','occurred_at']);
        });

        // submissions (UGC)
        Schema::create('detail_submissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('challenge_id')->constrained('m_challenges')->cascadeOnDelete();
            $t->enum('platform',['instagram','tiktok','other']);
            $t->string('content_url',500);
            $t->text('caption')->nullable();
            $t->enum('status',['submitted','approved','rejected'])->default('submitted');
            $t->json('metrics')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();

            $t->index(['challenge_id','status','created_at']);
            $t->index(['user_id','status']);
        });

        // metrics snapshot (opsional, kalau mau historis)
        Schema::create('detail_content_metrics', function (Blueprint $t) {
            $t->id();
            $t->foreignId('submission_id')->constrained('detail_submissions')->cascadeOnDelete();
            $t->integer('likes')->default(0);
            $t->integer('comments')->default(0);
            $t->integer('shares')->default(0);
            $t->integer('views')->default(0);
            $t->dateTime('collected_at')->useCurrent();
            $t->timestamps();

            $t->index(['submission_id','collected_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('detail_content_metrics');
        Schema::dropIfExists('detail_submissions');
        Schema::dropIfExists('detail_funnel_events');
        Schema::dropIfExists('detail_user_profiles');
    }
};
