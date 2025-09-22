<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // points ledger
        Schema::create('detail_points_ledger', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->enum('source_type',['challenge','referral','manual','redemption']);
            $t->unsignedBigInteger('source_id')->nullable();
            $t->integer('points'); // plus/minus
            $t->string('description')->nullable();
            $t->timestamps();

            $t->index(['user_id','created_at']);
            $t->index(['source_type','source_id']);
        });

        // rewards
        Schema::create('m_rewards', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->text('description')->nullable();
            $t->integer('points_cost');
            $t->integer('stock')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // redemptions
        Schema::create('detail_redemptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('reward_id')->constrained('m_rewards')->restrictOnDelete();
            $t->integer('points_spent');
            $t->enum('status',['requested','approved','shipped','canceled'])->default('requested');
            $t->json('shipping_info')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();

            $t->index(['user_id','status']);
            $t->index(['reward_id','status']);
        });

        // invites / referral
        Schema::create('detail_invites', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $t->char('invite_code',10)->unique();
            $t->foreignId('invitee_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained('m_campaigns')->nullOnDelete();
            $t->enum('status',['sent','registered','activated'])->default('sent');
            $t->timestamps();

            $t->index(['inviter_id','status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('detail_invites');
        Schema::dropIfExists('detail_redemptions');
        Schema::dropIfExists('m_rewards');
        Schema::dropIfExists('detail_points_ledger');
    }
};
